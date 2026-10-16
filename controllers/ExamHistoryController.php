<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ExamHistory.php';

final class ExamHistoryController extends BaseController
{
    private $history;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->history = new ExamHistory($pdo);
    }

    public function resultPage()
    {
        $this->requireLogin();
        $attemptId = filter_var($_GET['attempt_id'] ?? null, FILTER_VALIDATE_INT);
        $attempt = $attemptId
            ? $this->history->findResult($attemptId, (int) $_SESSION['user']['id'])
            : null;
        if (!$attempt) {
            http_response_code(404);
            exit('Không tìm thấy kết quả bài làm.');
        }
        require __DIR__ . '/../views/exams/result.php';
    }

    public function detail()
    {
        $this->requireLogin();
        $attemptId = filter_var($_GET['attempt_id'] ?? null, FILTER_VALIDATE_INT);
        $data = $attemptId
            ? $this->history->findDetail($attemptId, (int) $_SESSION['user']['id'])
            : null;
        if ($data === null) {
            http_response_code(404);
            exit('Không tìm thấy lượt nộp hoặc bạn không có quyền xem lượt nộp này.');
        }

        $attempt = $data['attempt'];
        $violationLogs = $data['violationLogs'];
        $questions = $data['questions'];
        $csrfToken = $_SESSION['csrf_token'];
        require __DIR__ . '/../views/exams/attempt-detail.php';
    }
}
