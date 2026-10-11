<?php
class BaseController {
    protected $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // 1. Kiểm tra xem người dùng đã đăng nhập chưa và tài khoản có còn active không
    protected function requireLogin() {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?action=login');
            exit;
        }

        // KIỂM TRA THỜI GIAN THỰC TỪ DATABASE (Chặn ngay nếu tài khoản bị Admin khóa giữa chừng)
        $stmt = $this->pdo->prepare("SELECT status FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        $userStatus = $stmt->fetchColumn();

        if (!$userStatus || $userStatus !== 'active') {
            unset($_SESSION['user']);
            session_destroy();
            header('Location: index.php?action=login&error=account_locked');
            exit;
        }
    }

    // 2. Chỉ cho phép Admin truy cập
    protected function requireAdmin() {
        $this->requireLogin();
        if ($_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            die('<h3>403 Forbidden</h3><p>Bạn không có quyền truy cập trang quản trị hệ thống!</p>');
        }
    }

    // 3. Cho phép Giáo viên hoặc Admin truy cập
    protected function requireTeacher() {
        $this->requireLogin();
        if (!in_array($_SESSION['user']['role'], ['admin', 'teacher'])) {
            http_response_code(403);
            die('<h3>403 Forbidden</h3><p>Bạn không có quyền thực hiện thao tác này!</p>');
        }
    }
}