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

        $rawUserId = $_POST['user_id'] ?? null;
        $actionType = $_POST['action_type'] ?? null;
        $value = $_POST['value'] ?? null;

        if (!is_string($rawUserId) || !is_string($actionType)) {
            header('Location: index.php?action=admin_users&error=invalid_user');
            exit;
        }
        $userId = filter_var($rawUserId, FILTER_VALIDATE_INT);
        if ($userId === false || $userId < 1) {
            header('Location: index.php?action=admin_users&error=invalid_user');
            exit;
        }

        if (!$this->userModel->existsById($userId)) {
            header('Location: index.php?action=admin_users&error=invalid_user');
            exit;
        }

        if (in_array($actionType, ['status', 'role'], true)
            && $userId === (int) $_SESSION['user']['id']) {
            header('Location: index.php?action=admin_users&error=self_action_forbidden');
            exit;
        }

        if ($actionType === 'status' && in_array($value, ['active', 'locked'], true)) {
            $this->userModel->updateStatus($userId, $value);
        } elseif ($actionType === 'role' && in_array($value, ['student', 'teacher', 'admin'], true)) {
            $this->userModel->updateRole($userId, $value);
        } elseif ($actionType === 'profile') {
            $email = $_POST['email'] ?? null;
            $newPassword = $_POST['new_password'] ?? '';
            if (!is_string($email) || !is_string($newPassword)) {
                header('Location: index.php?action=admin_users&error=invalid_profile');
                exit;
            }
            $email = trim($email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)
                || strlen($email) > 100
                || !is_string($newPassword)
                || ($newPassword !== '' && !preg_match('/^.{6,}$/us', $newPassword))) {
                header('Location: index.php?action=admin_users&error=invalid_profile');
                exit;
            }
            if ($this->userModel->emailExists($email, $userId)) {
                header('Location: index.php?action=admin_users&error=email_exists');
                exit;
            }

            try {
                $this->userModel->updateAccountDetails(
                    $userId,
                    $email,
                    $newPassword === '' ? null : $newPassword
                );
            } catch (PDOException $exception) {
                if (($exception->errorInfo[1] ?? null) === 1062) {
                    header('Location: index.php?action=admin_users&error=email_exists');
                    exit;
                }
                throw $exception;
            }
        } else {
            header('Location: index.php?action=admin_users&error=invalid_action');
            exit;
        }

        header('Location: index.php?action=admin_users&success=updated');
        exit;
    }

    public function createUserAction() {
        $this->requireAdmin();
        $this->requirePostWithCsrf();

        $fullName = $_POST['full_name'] ?? null;
        $email = $_POST['email'] ?? null;
        $schoolName = $_POST['school_name'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        if (!is_string($fullName) || !is_string($email)
            || !is_string($schoolName)
            || !is_string($password) || !preg_match('/^.{6,}$/us', $password)
            || !is_string($role) || !in_array($role, ['student', 'teacher', 'admin'], true)) {
            header('Location: index.php?action=admin_users&error=invalid_create');
            exit;
        }
        $fullName = trim($fullName);
        $email = trim($email);
        $schoolName = trim($schoolName);
        if ($fullName === '' || strlen($fullName) > 100
            || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100
            || strlen($schoolName) > 150) {
            header('Location: index.php?action=admin_users&error=invalid_create');
            exit;
        }
        if ($this->userModel->emailExists($email)) {
            header('Location: index.php?action=admin_users&error=email_exists');
            exit;
        }

        try {
            $this->userModel->create($role, $fullName, $email, $schoolName, $password);
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                header('Location: index.php?action=admin_users&error=email_exists');
                exit;
            }
            throw $exception;
        }

        header('Location: index.php?action=admin_users&success=created');
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
    // Hiển thị form đăng nhập riêng cho Admin
    public function login() {
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
            header('Location: index.php?action=dashboard');
            exit;
        }
        $error = '';
        require __DIR__ . '/../views/admin/login.php';
    }

    // Xử lý xác thực đăng nhập Admin (Bắt buộc kiểm tra role === 'admin')
    public function handleLogin() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->findByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $error = 'Tài khoản quản trị của bạn đã bị khóa!';
                } elseif ($user['role'] !== 'admin') {
                    // CHẶN NGAY: Học sinh hoặc giáo viên không được đăng nhập qua cổng này
                    $error = 'Vui lòng đăng nhập qua cổng quản trị Admin!';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => $user['user_id'],
                        'full_name' => $user['full_name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'avatar' => $user['avatar'] ?? 'assets/images/default-avatar.png'
                    ];
                    header('Location: index.php?action=dashboard');
                    exit;
                }
            } else {
                $error = 'Email hoặc mật khẩu quản trị không chính xác!';
            }
        }
        require __DIR__ . '/../views/admin/login.php';
    }
}