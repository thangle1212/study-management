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
        $stmt = $this->pdo->prepare("SELECT status, role FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['status'] !== 'active') {
            unset($_SESSION['user']);
            session_destroy();
            header('Location: index.php?action=login&error=account_locked');
            exit;
        }

        $_SESSION['user']['role'] = $user['role'];
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    protected function requirePostWithCsrf() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Phương thức yêu cầu không được hỗ trợ.');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ. Hãy tải lại trang và thử lại.');
        }
    }

    // 2. Chỉ cho phép Admin truy cập
    protected function requireAdmin() {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?action=admin_login');
            exit;
        }
        // Kiểm tra trạng thái active từ DB
        $stmt = $this->pdo->prepare("SELECT role,status FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['status'] !== 'active' || $user['role'] !== 'admin') {
            unset($_SESSION['user']);
            session_destroy();
            header('Location: index.php?action=admin_login&error=unauthorized');
            exit;
        }

        if ($_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            die('<h3>403 Forbidden</h3><p>Bạn không có quyền truy cập trang quản trị hệ thống!</p>');
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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