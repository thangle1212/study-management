<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ClassModel.php';

class ClassController extends BaseController {
    private $classModel;

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->classModel = new ClassModel($pdo);
    }

    public function index() {
        $this->requireTeacherRole();

        $teacherId = (int) $_SESSION['user']['id'];
        $classes = $this->classModel->getClassesByTeacher($teacherId);
        $flash = $this->consumeFlash();

        require __DIR__ . '/../views/teacher/classes/index.php';
    }

    public function detail($classId) {
        $this->requireTeacherRole();
        $classId = $this->validateId($classId);
        if ($classId === false) {
            http_response_code(400);
            exit('Mã lớp không hợp lệ.');
        }

        $teacherId = (int) $_SESSION['user']['id'];
        $class = $this->classModel->getClassDetail($classId, $teacherId);
        if (!$class) {
            http_response_code(403);
            exit('Bạn không có quyền truy cập lớp học này.');
        }

        $students = $this->classModel->getStudentsInClass($classId, $teacherId);
        $availableStudents = $this->classModel->getAvailableStudents($classId, $teacherId);
        $exams = $this->classModel->getExamsByTeacher($teacherId);
        $flash = $this->consumeFlash();
        $csrfToken = $_SESSION['csrf_token'];

        require __DIR__ . '/../views/teacher/classes/detail.php';
    }

    public function addStudent() {
        $this->requireTeacherRole();
        $this->requirePostWithCsrf();

        $classId = $this->validateId($_POST['class_id'] ?? null);
        $studentId = $this->validateId($_POST['student_id'] ?? null);
        if ($classId === false || $studentId === false) {
            http_response_code(400);
            exit('Mã lớp hoặc mã học sinh không hợp lệ.');
        }

        $teacherId = (int) $_SESSION['user']['id'];
        $class = $this->classModel->getClassDetail($classId, $teacherId);

        if (!$class) {
            http_response_code(403);
            exit('Bạn không có quyền gán học sinh vào lớp này.');
        }
        if (!$this->classModel->isActiveStudent($studentId)) {
            $this->redirectWithFlash($classId, 'error', 'Chỉ có thể gán tài khoản học sinh đang hoạt động.');
        }
        if ($this->classModel->studentIsInClass($classId, $studentId, $teacherId)) {
            $this->redirectWithFlash($classId, 'error', 'Học sinh này đã thuộc lớp.');
        }

        try {
            $added = $this->classModel->addStudentToClass($classId, $studentId, $teacherId);
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                $this->redirectWithFlash($classId, 'error', 'Học sinh này vừa được gán vào lớp.');
            }
            throw $exception;
        }

        if (!$added) {
            if ($this->classModel->studentIsInClass($classId, $studentId, $teacherId)) {
                $this->redirectWithFlash($classId, 'error', 'Học sinh này đã thuộc lớp.');
            }
            $this->redirectWithFlash($classId, 'error', 'Không thể gán học sinh. Hãy tải lại trang và thử lại.');
        }

        $this->redirectWithFlash($classId, 'success', 'Đã gán học sinh vào lớp.');
    }

    public function removeStudent() {
        $this->requireTeacherRole();
        $this->requirePostWithCsrf();

        $classId = $this->validateId($_POST['class_id'] ?? null);
        $studentId = $this->validateId($_POST['student_id'] ?? null);
        if ($classId === false || $studentId === false) {
            http_response_code(400);
            exit('Mã lớp hoặc mã học sinh không hợp lệ.');
        }

        $teacherId = (int) $_SESSION['user']['id'];
        $class = $this->classModel->getClassDetail($classId, $teacherId);

        if (!$class) {
            http_response_code(403);
            exit('Bạn không có quyền bỏ gán học sinh khỏi lớp này.');
        }
        if (!$this->classModel->studentIsInClass($classId, $studentId, $teacherId)) {
            $this->redirectWithFlash($classId, 'error', 'Không tìm thấy học sinh trong lớp này.');
        }
        if (!$this->classModel->removeStudentFromClass($classId, $studentId, $teacherId)) {
            $this->redirectWithFlash($classId, 'error', 'Không thể bỏ gán học sinh. Hãy tải lại trang và thử lại.');
        }

        $this->redirectWithFlash(
            $classId,
            'success',
            'Đã bỏ học sinh khỏi lớp. Lịch sử làm bài và điểm số vẫn được giữ nguyên.'
        );
    }

    public function assignExam() {
        $this->requireTeacherRole();
        $this->requirePostWithCsrf();

        $classId = $this->validateId($_POST['class_id'] ?? null);
        $examId = $this->validateId($_POST['exam_id'] ?? null);
        if ($classId === false || $examId === false) {
            http_response_code(400);
            exit('Mã lớp hoặc mã đề thi không hợp lệ.');
        }

        $teacherId = (int) $_SESSION['user']['id'];
        $class = $this->classModel->getClassDetail($classId, $teacherId);

        if (!$class) {
            http_response_code(403);
            exit('Bạn không có quyền gán đề thi cho lớp này.');
        }

        $exam = $this->classModel->getExamForTeacher($examId, $teacherId);
        if (!$exam) {
            http_response_code(403);
            exit('Đề thi không tồn tại hoặc không thuộc quyền quản lý của bạn.');
        }
        if ((int) $exam['question_count'] < 1) {
            $this->redirectWithFlash($classId, 'error', 'Chỉ có thể gán đề thi có ít nhất một câu hỏi.');
        }
        if ($exam['class_id'] !== null && (int) $exam['class_id'] !== $classId) {
            $this->redirectWithFlash($classId, 'error', 'Đề thi đã được gán cho một lớp khác. Mỗi đề chỉ gán được cho một lớp.');
        }
        if ((int) $exam['class_id'] === $classId) {
            $this->redirectWithFlash($classId, 'success', 'Đề thi này đã được gán cho lớp.');
        }

        if (!$this->classModel->assignExamToClass($examId, $classId, $teacherId)) {
            $this->redirectWithFlash($classId, 'error', 'Không thể gán đề thi. Có thể đề không còn câu hỏi hoặc dữ liệu vừa thay đổi.');
        }

        $this->redirectWithFlash($classId, 'success', 'Đã gán đề thi cho lớp.');
    }

    private function requireTeacherRole() {
        $this->requireTeacher();
        if (($_SESSION['user']['role'] ?? '') !== 'teacher') {
            http_response_code(403);
            exit('Chức năng này chỉ dành cho giáo viên.');
        }
    }

    private function validateId($value) {
        if (!is_string($value) && !is_int($value)) {
            return false;
        }
        $id = filter_var($value, FILTER_VALIDATE_INT);
        return $id !== false && $id > 0 ? $id : false;
    }

    private function consumeFlash() {
        $flash = $_SESSION['teacher_class_flash'] ?? null;
        unset($_SESSION['teacher_class_flash']);
        return $flash;
    }

    private function redirectWithFlash($classId, $type, $message) {
        $_SESSION['teacher_class_flash'] = [
            'type' => $type,
            'message' => $message
        ];
        header('Location: index.php?action=teacher_class_detail&class_id=' . (int) $classId);
        exit;
    }
}
