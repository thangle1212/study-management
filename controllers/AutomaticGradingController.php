<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/AutomaticGrading.php';

final class AutomaticGradingController extends BaseController
{
    private $grading;

    /** Khởi tạo controller xử lý autosave, nộp bài và vi phạm. */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->grading = new AutomaticGrading($pdo);
    }

    /** Nhận đáp án tạm thời từ trang làm bài và trả kết quả JSON. */
    public function autosave()
    {
        $this->requireStudent();
        $this->requirePostWithCsrf();

        $attemptId = filter_var($_POST['attempt_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$attemptId || $attemptId < 1) {
            $this->sendJson(['error' => 'Lượt thi không hợp lệ.'], 400);
        }

        $result = $this->grading->autosave(
            $attemptId,
            (int) $_SESSION['user']['id'],
            $_POST['answers'] ?? []
        );
        if ($result === null) {
            $this->sendJson(['error' => 'Lượt thi không hợp lệ hoặc đã nộp.'], 403);
        }

        $this->sendJson($result);
    }

    /** Xác thực yêu cầu nộp thủ công và chuyển sang quy trình chấm điểm. */
    public function submit()
    {
        $this->requireStudent();
        $this->requirePostWithCsrf();

        $attemptId = filter_var($_POST['attempt_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$attemptId || $attemptId < 1) {
            http_response_code(400);
            exit('Lượt thi không hợp lệ.');
        }

        $answers = array_key_exists('answers', $_POST) ? $_POST['answers'] : null;
        $completed = $this->grading->finalize(
            $attemptId,
            (int) $_SESSION['user']['id'],
            $answers,
            'manual'
        );
        if (!$completed) {
            http_response_code(403);
            exit('Lượt thi không hợp lệ hoặc đã được nộp.');
        }

        header('Location: index.php?action=exam_result&attempt_id=' . $attemptId);
        exit;
    }

    /** Nhận sự kiện vi phạm từ trình duyệt và xử lý theo cấu hình đề thi. */
    public function logViolation()
    {
        $this->requireStudent();
        $this->requirePostWithCsrf();

        $attemptId = filter_var($_POST['attempt_id'] ?? null, FILTER_VALIDATE_INT);
        $violationType = is_string($_POST['violation_type'] ?? null)
            ? $_POST['violation_type']
            : '';
        $allowedTypes = ['tab_hidden', 'window_blur', 'context_menu', 'copy', 'cut', 'paste', 'shortcut'];
        if (!$attemptId || $attemptId < 1 || !in_array($violationType, $allowedTypes, true)) {
            $this->sendJson(['error' => 'Loại vi phạm không hợp lệ.'], 400);
        }

        $details = substr(trim((string) ($_POST['details'] ?? '')), 0, 255);
        $result = $this->grading->recordViolation(
            $attemptId,
            (int) $_SESSION['user']['id'],
            $violationType,
            $details
        );
        if ($result === null) {
            $this->sendJson(['error' => 'Lượt thi không còn hoạt động.'], 403);
        }

        $this->sendJson($result);
    }

    /** Bắt buộc request chấm/nộp bài phải do tài khoản học sinh thực hiện. */
    private function requireStudent()
    {
        $this->requireLogin();
        if (($_SESSION['user']['role'] ?? '') !== 'student') {
            http_response_code(403);
            exit('Chỉ học sinh mới được làm bài thi.');
        }
    }

    /** Gửi phản hồi JSON thống nhất cho các request bất đồng bộ. */
    private function sendJson($payload, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
