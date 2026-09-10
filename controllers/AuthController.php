<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
    }

    // --- 1. ĐĂNG NHẬP ---
    public function login() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->findByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $error = 'Tài khoản của bạn đã bị khóa!';
                } else {
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
                $error = 'Email hoặc mật khẩu không chính xác!';
            }
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    // --- 2. ĐĂNG KÝ ---
    public function register() {
        $error = '';
        $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $role = $_POST['role'] ?? 'student';
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $schoolName = trim($_POST['school_name'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($this->userModel->findByEmail($email)) {
                $error = 'Email này đã được sử dụng!';
            } else {
                if ($this->userModel->create($role, $fullName, $email, $schoolName, $password)) {
                    $success = 'Đăng ký thành công! <a href="index.php?action=login">Đăng nhập ngay</a>';
                } else {
                    $error = 'Có lỗi xảy ra, vui lòng thử lại sau.';
                }
            }
        }
        require __DIR__ . '/../views/auth/register.php';
    }

    // --- 3. QUÊN MẬT KHẨU ---
    public function forgotPassword() {
        $error = '';
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $user = $this->userModel->findByEmail($email);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $this->userModel->setResetToken($email, $token, $expires);

                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER["REQUEST_URI"], '?') . "?action=reset_password&token=" . $token;
                
                // Môi trường Local: Hiển thị trực tiếp Link để test thay vì gửi Mail
                $message = "Yêu cầu đã được tạo! Link đặt lại mật khẩu (Giả lập): <br><a href='$resetLink' class='fw-bold'>$resetLink</a>";
            } else {
                $error = 'Email không tồn tại trong hệ thống!';
            }
        }
        require __DIR__ . '/../views/auth/forgot-password.php';
    }

    // --- 4. ĐẶT LẠI MẬT KHẨU ---
    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        $user = $this->userModel->findByResetToken($token);

        if (!$user) {
            die('Liên kết không hợp lệ hoặc đã hết hạn!');
        }

        $error = '';
        $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (strlen($newPassword) < 6) {
                $error = 'Mật khẩu phải từ 6 ký tự trở lên!';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Mật khẩu xác nhận không khớp!';
            } else {
                $this->userModel->updatePasswordAndClearToken($user['user_id'], $newPassword);
                $success = 'Mặt khẩu đã được đổi thành công! <a href="index.php?action=login">Đăng nhập ngay</a>';
            }
        }
        require __DIR__ . '/../views/auth/reset-password.php';
    }

    // --- 5. ĐĂNG XUẤT ---
    public function logout() {
        unset($_SESSION['user']);
        session_destroy();
        header('Location: index.php?action=login');
        exit;
    }
}
?>