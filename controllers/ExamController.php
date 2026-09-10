<?php
require_once __DIR__ . '/../models/Exam.php';

class ExamController {
    private $examModel;

    public function __construct($pdo) {
        $this->examModel = new Exam($pdo);
    }

    public function take() {
        $this->requireStudent();
        $examId = filter_input(INPUT_GET, 'exam_id', FILTER_VALIDATE_INT);
        $exam = $examId ? $this->examModel->findPublishedExam($examId) : false;
        if (!$exam) {
            http_response_code(404);
            exit('Đề thi không tồn tại hoặc chưa mở.');
        }

        $studentId = $_SESSION['user']['id'];
        $attempt = $this->examModel->findActiveAttempt($examId, $studentId);
        if (!$attempt) {
            $attemptId = $this->examModel->createAttempt($examId, $studentId);
            $attempt = ['attempt_id' => $attemptId, 'start_time' => date('Y-m-d H:i:s')];
        }

        $questions = $this->examModel->getQuestions($examId);
        require __DIR__ . '/../views/take-exam.php';
    }

    public function submit() {
        $this->requireStudent();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Phương thức không hợp lệ.');
        }

        $attemptId = filter_input(INPUT_POST, 'attempt_id', FILTER_VALIDATE_INT);
        $examId = filter_input(INPUT_POST, 'exam_id', FILTER_VALIDATE_INT);
        $answers = $_POST['answers'] ?? [];
        $success = $attemptId && $examId && is_array($answers)
            && $this->examModel->completeAttempt($attemptId, $_SESSION['user']['id'], $answers);

        if (!$success) {
            http_response_code(400);
            exit('Không thể nộp bài hoặc bài thi đã được nộp.');
        }
        header('Location: index.php?action=dashboard&submitted=1');
        exit;
    }

    public function logViolation() {
        $this->requireStudent();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $attemptId = filter_input(INPUT_POST, 'attempt_id', FILTER_VALIDATE_INT);
        $type = preg_replace('/[^a-z_]/', '', $_POST['violation_type'] ?? 'unknown');
        $details = substr(trim($_POST['details'] ?? ''), 0, 255);
        if ($attemptId && $type) {
            $this->examModel->logViolation($attemptId, $_SESSION['user']['id'], $type, $details);
        }
        http_response_code(204);
    }

    private function requireStudent() {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?action=login');
            exit;
        }
        if (($_SESSION['user']['role'] ?? '') !== 'student') {
            http_response_code(403);
            exit('Chỉ học sinh được làm bài thi.');
        }
    }
}
?>
