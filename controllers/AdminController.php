<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';

class AdminController extends BaseController {
    private $userModel;

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->userModel = new User($pdo);
    }

    // Hiển thị trang quản lý người dùng
    public function manageUsers() {
        $this->requireAdmin(); // Bắt buộc phải là Admin mới được vào

        $roleFilter = $_GET['role'] ?? null;
        if ($roleFilter !== null && !is_string($roleFilter)) {
            http_response_code(400);
            exit('Bộ lọc vai trò không hợp lệ.');
        }
        if ($roleFilter === null || $roleFilter === '') {
            $roleFilter = null;
        } elseif (!in_array($roleFilter, ['student', 'teacher', 'admin'], true)) {
            http_response_code(400);
            exit('Bộ lọc vai trò không hợp lệ.');
        }

        $users = $this->userModel->getAllUsers($roleFilter);
        $csrfToken = $_SESSION['csrf_token'];

        require __DIR__ . '/../views/admin/users.php';
    }

    // Xử lý đổi trạng thái tài khoản (Khóa / Mở khóa) hoặc đổi Role qua POST
    public function updateUserAction() {
        $this->requireAdmin();
        $this->requirePostWithCsrf();

        $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
        $actionType = $_POST['action_type'] ?? null;
        $value = $_POST['value'] ?? null;

        if ($userId === false || $userId < 1 || !is_string($actionType) || !is_string($value)) {
            header('Location: index.php?action=admin_users&error=invalid_user');
            exit;
        }

        if ($userId === (int) $_SESSION['user']['id']) {
            header('Location: index.php?action=admin_users&error=self_action_forbidden');
            exit;
        }

        if ($actionType === 'status' && in_array($value, ['active', 'locked'], true)) {
            $this->userModel->updateStatus($userId, $value);
        } elseif ($actionType === 'role' && in_array($value, ['student', 'teacher', 'admin'], true)) {
            $this->userModel->updateRole($userId, $value);
        } else {
            header('Location: index.php?action=admin_users&error=invalid_action');
            exit;
        }

        header('Location: index.php?action=admin_users&success=updated');
        exit;
    }
    // Hiển thị Dashboard riêng cho Admin
    public function dashboard() {
        $this->requireAdmin(); // Đảm bảo chỉ Admin mới được truy cập

        // Lấy thống kê tổng quan từ CSDL
        $stats = [
            'total_users' => $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'students'    => $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),
            'teachers'    => $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn(),
            'admins'      => $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(),
            'classes'     => $this->pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn(),
        ];

        require __DIR__ . '/../views/admin/dashboard.php';
    }
}