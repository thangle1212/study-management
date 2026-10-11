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
        $users = $this->userModel->getAllUsers($roleFilter);

        require __DIR__ . '/../views/admin/users.php';
    }

    // Xử lý đổi trạng thái tài khoản (Khóa / Mở khóa) hoặc đổi Role qua POST
    public function updateUserAction() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? null;
            $actionType = $_POST['action_type'] ?? null; // 'status' hoặc 'role'
            $value = $_POST['value'] ?? null;

            if (!$userId) {
                header('Location: index.php?action=admin_users&error=invalid_user');
                exit;
            }

            // Không cho phép Admin tự khóa chính tài khoản của mình
            if ($userId == $_SESSION['user']['id']) {
                header('Location: index.php?action=admin_users&error=self_action_forbidden');
                exit;
            }

            if ($actionType === 'status' && in_array($value, ['active', 'locked'])) {
                $this->userModel->updateStatus($userId, $value);
            } elseif ($actionType === 'role' && in_array($value, ['student', 'teacher', 'admin'])) {
                $this->userModel->updateRole($userId, $value);
            }

            header('Location: index.php?action=admin_users&success=updated');
            exit;
        }
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