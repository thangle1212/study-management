<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/StudentReport.php';

final class StudentStatisticsController extends BaseController
{
    private $statistics;

    /** Khởi tạo controller tổng hợp tiến độ học tập cá nhân. */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->statistics = new StudentReport($pdo);
    }

    /** Lấy bộ lọc của học sinh, gọi model và render trang thống kê cá nhân. */
    public function index()
    {
        $this->requireLogin();
        $studentId = (int) $_SESSION['user']['id'];
        $selectedExamId = (int) ($_GET['exam_id'] ?? 0);
        $data = $this->statistics->getDashboard($studentId, $selectedExamId);
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../views/exams/student-statistics.php';
    }
}
