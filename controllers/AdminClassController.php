<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/AdminClass.php';

class AdminClassController extends BaseController {
    private $classModel;

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->classModel = new AdminClass($pdo);
    }

    public function index() {
        $this->requireAdmin();

        $errors = [];
        $formData = [
            'class_name' => '',
            'class_code' => '',
            'teacher_id' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostWithCsrf();
            $operation = $_POST['operation'] ?? null;
            if (!is_string($operation)) {
                http_response_code(400);
                exit('Thao tác quản lý lớp không hợp lệ.');
            }

            if ($operation === 'create') {
                $errors = $this->createClass();
                $formData = [
                    'class_name' => is_string($_POST['class_name'] ?? null) ? trim($_POST['class_name']) : '',
                    'class_code' => is_string($_POST['class_code'] ?? null) ? strtoupper(trim($_POST['class_code'])) : '',
                    'teacher_id' => is_string($_POST['teacher_id'] ?? null) ? $_POST['teacher_id'] : '',
                ];
            } elseif ($operation === 'update') {
                $errors = $this->updateClass();
            } elseif ($operation === 'assign_student') {
                $this->assignStudent();
            } elseif ($operation === 'remove_student') {
                $this->removeStudent();
            } else {
                http_response_code(400);
                exit('Thao tác quản lý lớp không được hỗ trợ.');
            }
        }

        $classes = $this->classModel->getClasses();
        $teachers = $this->classModel->getTeachers();
        $students = $this->classModel->getActiveStudents();
        $memberships = $this->classModel->getClassMemberships();
        $studentsByClass = [];
        foreach ($memberships as $membership) {
            $studentsByClass[$membership['class_id']][] = $membership;
        }

        $flash = $_SESSION['admin_class_flash'] ?? null;
        unset($_SESSION['admin_class_flash']);
        $csrfToken = $_SESSION['csrf_token'];

        require __DIR__ . '/../views/admin/classes.php';
    }

    private function createClass() {
        $className = $_POST['class_name'] ?? null;
        $classCode = $_POST['class_code'] ?? null;
        $rawTeacherId = $_POST['teacher_id'] ?? null;
        $errors = [];

        if (!is_string($className)) {
            $errors['class_name'] = 'Tên lớp không hợp lệ.';
        } else {
            $className = trim($className);
            if ($className === '' || strlen($className) > 100) {
                $errors['class_name'] = 'Tên lớp phải có từ 1 đến 100 ký tự.';
            }
        }

        if (!is_string($classCode)) {
            $errors['class_code'] = 'Mã lớp không hợp lệ.';
        } else {
            $classCode = strtoupper(trim($classCode));
            if (!preg_match('/^[A-Z0-9]{4,10}$/', $classCode)) {
                $errors['class_code'] = 'Mã lớp cần có từ 4 đến 10 chữ cái hoặc chữ số.';
            } elseif ($this->classModel->classCodeExists($classCode)) {
                $errors['class_code'] = 'Mã lớp này đã được sử dụng.';
            }
        }

        $teacherId = $this->validateId($rawTeacherId);
        if ($teacherId === false || !$this->classModel->activeTeacherExists($teacherId)) {
            $errors['teacher_id'] = 'Hãy chọn một giáo viên đang hoạt động.';
        }

        if (!$errors) {
            try {
                $this->classModel->createClass($className, $classCode, $teacherId);
            } catch (PDOException $exception) {
                if (($exception->errorInfo[1] ?? null) === 1062) {
                    $errors['class_code'] = 'Mã lớp này vừa được sử dụng. Hãy chọn mã khác.';
                    return $errors;
                }
                throw $exception;
            }
            $this->redirectWithFlash('success', 'Đã tạo lớp mới.');
        }

        return $errors;
    }

    private function updateClass() {
        $classId = $this->validateId($_POST['class_id'] ?? null);
        $className = $_POST['class_name'] ?? null;
        $teacherId = $this->validateId($_POST['teacher_id'] ?? null);
        if ($classId === false || !$this->classModel->classExists($classId)) {
            $this->redirectWithFlash('error', 'Không tìm thấy lớp cần cập nhật.');
        }

        $errors = [];
        if (!is_string($className)) {
            $errors['class_name_' . $classId] = 'Tên lớp không hợp lệ.';
        } else {
            $className = trim($className);
            if ($className === '' || strlen($className) > 100) {
                $errors['class_name_' . $classId] = 'Tên lớp phải có từ 1 đến 100 ký tự.';
            }
        }
        if ($teacherId === false || !$this->classModel->activeTeacherExists($teacherId)) {
            $errors['teacher_id_' . $classId] = 'Hãy chọn một giáo viên đang hoạt động.';
        }

        if (!$errors) {
            $this->classModel->updateClass($classId, $className, $teacherId);
            $this->redirectWithFlash('success', 'Đã cập nhật thông tin lớp.');
        }

        return $errors;
    }

    private function assignStudent() {
        $classId = $this->validateId($_POST['class_id'] ?? null);
        $studentId = $this->validateId($_POST['student_id'] ?? null);
        if ($classId === false || !$this->classModel->classExists($classId)) {
            $this->redirectWithFlash('error', 'Không tìm thấy lớp cần gán học sinh.');
        }
        if ($studentId === false || !$this->classModel->activeStudentExists($studentId)) {
            $this->redirectWithFlash('error', 'Chỉ có thể gán học sinh đang hoạt động.');
        }
        if ($this->classModel->studentBelongsToClass($classId, $studentId)) {
            $this->redirectWithFlash('error', 'Học sinh này đã thuộc lớp.');
        }

        try {
            $assigned = $this->classModel->assignStudent($classId, $studentId);
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                $this->redirectWithFlash('error', 'Học sinh này đã được gán vào lớp.');
            }
            throw $exception;
        }
        if (!$assigned) {
            $this->redirectWithFlash('error', 'Không thể gán học sinh. Hãy kiểm tra trạng thái tài khoản và thử lại.');
        }
        $this->redirectWithFlash('success', 'Đã gán học sinh vào lớp.');
    }

    private function removeStudent() {
        $classId = $this->validateId($_POST['class_id'] ?? null);
        $studentId = $this->validateId($_POST['student_id'] ?? null);
        if ($classId === false || $studentId === false
            || !$this->classModel->studentBelongsToClass($classId, $studentId)) {
            $this->redirectWithFlash('error', 'Không tìm thấy học sinh trong lớp này.');
        }

        $this->classModel->removeStudent($classId, $studentId);
        $this->redirectWithFlash('success', 'Đã bỏ học sinh khỏi lớp. Lịch sử làm bài vẫn được giữ nguyên.');
    }

    private function validateId($value) {
        if (!is_string($value)) {
            return false;
        }
        $id = filter_var($value, FILTER_VALIDATE_INT);
        return $id !== false && $id > 0 ? $id : false;
    }

    private function redirectWithFlash($type, $message) {
        $_SESSION['admin_class_flash'] = ['type' => $type, 'message' => $message];
        header('Location: index.php?action=admin_classes');
        exit;
    }
}
