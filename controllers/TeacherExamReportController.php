<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/TeacherReport.php';

final class TeacherExamReportController extends BaseController
{
    private $report;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->report = new TeacherReport($pdo);
    }

    public function index()
    {
        $this->requireTeacher();
        $examId = filter_var($_GET['exam_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$examId || $examId < 1) {
            http_response_code(400);
            exit('Đề thi không hợp lệ.');
        }

        $distributionMode = is_string($_GET['distribution_mode'] ?? null)
            ? $_GET['distribution_mode']
            : 'highest';
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        $perPage = filter_var($_GET['per_page'] ?? 10, FILTER_VALIDATE_INT);
        $page = $page && $page > 0 ? min($page, 1000000) : 1;
        $perPage = $perPage && $perPage > 0 ? min($perPage, 50) : 10;
        $data = $this->report->getStatistics(
            $examId,
            (int) $_SESSION['user']['id'],
            $distributionMode,
            $page,
            $perPage
        );
        if ($data === null) {
            http_response_code(403);
            exit('Bạn không có quyền xem thống kê đề thi này.');
        }

        $csrfToken = $_SESSION['csrf_token'];
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../views/exams/statistics.php';
    }

    public function monitor()
    {
        $this->requireTeacher();
        $examId = filter_var($_GET['exam_id'] ?? null, FILTER_VALIDATE_INT);
        $data = $examId ? $this->report->getMonitoring($examId, (int) $_SESSION['user']['id']) : null;
        if ($data === null) {
            http_response_code(403);
            exit('Bạn không có quyền giám sát đề thi này.');
        }

        $exam = $data['exam'];
        $attempts = $data['attempts'];
        $csrfToken = $_SESSION['csrf_token'];
        require __DIR__ . '/../views/exams/monitoring.php';
    }

    public function monitorData()
    {
        $this->requireTeacher();
        $examId = filter_var($_GET['exam_id'] ?? null, FILTER_VALIDATE_INT);
        $data = $examId ? $this->report->getMonitoring($examId, (int) $_SESSION['user']['id']) : null;
        if ($data === null) {
            $this->sendJson(['error' => 'Không tìm thấy đề thi.'], 404);
        }
        $this->sendJson(['attempts' => $data['attempts']]);
    }

    public function updateSettings()
    {
        $this->requireTeacher();
        $this->requirePostWithCsrf();
        $examId = filter_var($_POST['exam_id'] ?? null, FILTER_VALIDATE_INT);
        $duration = filter_var($_POST['duration_minutes'] ?? null, FILTER_VALIDATE_INT);
        $limit = filter_var($_POST['violation_limit'] ?? null, FILTER_VALIDATE_INT);
        $action = is_string($_POST['violation_action'] ?? null) ? $_POST['violation_action'] : '';
        if (!$examId || !$duration || $duration < 1 || $duration > 360
            || !$limit || $limit < 1 || $limit > 20
            || !in_array($action, ['auto_submit', 'log_only'], true)) {
            http_response_code(400);
            exit('Thông số cấu hình không hợp lệ.');
        }

        $this->report->updateSettings($examId, (int) $_SESSION['user']['id'], [
            'duration_minutes' => $duration,
            'anti_cheat_enabled' => isset($_POST['anti_cheat_enabled']) ? 1 : 0,
            'violation_limit' => $limit,
            'violation_action' => $action,
            'shuffle_questions' => isset($_POST['shuffle_questions']) ? 1 : 0,
            'shuffle_answers' => isset($_POST['shuffle_answers']) ? 1 : 0
        ]);
        header('Location: index.php?action=exam_statistics&exam_id=' . $examId . '&settings=saved');
        exit;
    }

    public function reviewAttempt()
    {
        $this->requireTeacher();
        $this->requirePostWithCsrf();
        $attemptId = filter_var($_POST['attempt_id'] ?? null, FILTER_VALIDATE_INT);
        $decision = $_POST['decision'] ?? '';
        if (!$attemptId || !in_array($decision, ['void', 'allow_retry'], true)) {
            http_response_code(403);
            exit('Yêu cầu phúc khảo không hợp lệ.');
        }

        $note = substr(trim((string) ($_POST['review_note'] ?? '')), 0, 500);
        $updated = $this->report->reviewAttempt(
            $attemptId,
            (int) $_SESSION['user']['id'],
            $decision,
            $note
        );
        if (!$updated) {
            http_response_code(404);
            exit('Không tìm thấy lượt thi đang chờ phúc khảo.');
        }
        header('Location: index.php?action=attempt_detail&attempt_id=' . $attemptId . '&review=saved');
        exit;
    }

    private function sendJson($payload, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
