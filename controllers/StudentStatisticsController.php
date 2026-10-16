<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/StudentReport.php';

final class StudentStatisticsController extends BaseController
{
    private $statistics;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->statistics = new StudentReport($pdo);
    }

    public function index()
    {
        $this->requireLogin();
        $studentId = (int) $_SESSION['user']['id'];
        $selectedExamId = (int) ($_GET['exam_id'] ?? 0);
        $selectedExamType = is_string($_GET['exam_type'] ?? null) ? $_GET['exam_type'] : '';
        $data = $this->statistics->getDashboard($studentId, $selectedExamId, $selectedExamType);
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../views/exams/student-statistics.php';
    }
}
