<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ClassModel.php';

class StudentClassController extends BaseController {
    private $classModel;

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->classModel = new ClassModel($pdo);
    }

    public function index() {
        $this->requireLogin();
        if (($_SESSION['user']['role'] ?? '') !== 'student') {
            http_response_code(403);
            exit('Chức năng này chỉ dành cho học sinh.');
        }

        $studentId = (int) $_SESSION['user']['id'];
        $classes = $this->classModel->getClassesByStudent($studentId);
        $assignedExams = $this->classModel->getPublishedExamsByStudent($studentId);

        require __DIR__ . '/../views/student/classes/index.php';
    }
}
