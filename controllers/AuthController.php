<?php
require_once __DIR__ . '/../models/User.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../libs/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/src/SMTP.php';

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

            if (!$user) {
                $error = 'Email này chưa được đăng ký!';
            } else {
                $token = bin2hex(random_bytes(32));

                if (!$this->userModel->setResetToken($user['email'], $token)) {
                    $error = 'Không thể tạo liên kết khôi phục, vui lòng thử lại sau.';
                } else {
                    $resetUrl = $this->getBaseUrl() . '/index.php?action=reset_password&token=' . urlencode($token);
                    $mailer = new PHPMailer(true);

                    try {
                        $mailer->isSMTP();
                        $mailer->Host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
                        $mailer->SMTPAuth = true;
                        $mailer->Username = getenv('SMTP_USERNAME');
                        $mailer->Password = getenv('SMTP_PASSWORD');
                        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mailer->Port = (int) (getenv('SMTP_PORT') ?: 587);
                        $mailer->CharSet = 'UTF-8';
                        $mailer->setFrom(
                            getenv('SMTP_FROM_EMAIL') ?: $mailer->Username,
                            getenv('SMTP_FROM_NAME') ?: 'EduTest'
                        );
                        $mailer->addAddress($user['email'], $user['full_name']);
                        $mailer->isHTML(true);
                        $mailer->Subject = 'Liên kết đặt lại mật khẩu EduTest';
                        $mailer->Body = 'Xin chào ' . htmlspecialchars($user['full_name']) . ',<br><br>'
                            . 'Nhấn vào liên kết sau để đặt lại mật khẩu (có hiệu lực trong 2 giờ):<br>'
                            . '<a href="' . htmlspecialchars($resetUrl) . '">' . htmlspecialchars($resetUrl) . '</a>';
                        $mailer->send();
                        $message = 'Liên kết khôi phục đã được gửi đến email của bạn.';
                    } catch (Exception $exception) {
                        $error = 'Không thể gửi email khôi phục. Vui lòng kiểm tra cấu hình SMTP.';
                    }
                }
            }
        }

        require __DIR__ . '/../views/auth/forgot-password.php';
    }

    private function getBaseUrl() {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    }

    // --- 4. ĐẶT LẠI MẬT KHẨU ---
    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        $error = '';
        $success = '';

        if (empty($token)) {
            $error = 'Thiếu mã xác thực khôi phục mật khẩu!';
            require __DIR__ . '/../views/auth/reset-password.php';
            return;
        }

        $user = $this->userModel->findByResetToken($token);

        if (!$user) {
            $error = 'Liên kết không hợp lệ hoặc đã hết hạn!';
            require __DIR__ . '/../views/auth/reset-password.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (strlen($newPassword) < 6) {
                $error = 'Mật khẩu phải từ 6 ký tự trở lên!';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Mật khẩu xác nhận không khớp!';
            } else {
                $userId = $user['user_id'] ?? $user['id']; // Hỗ trợ cả 2 tên cột ID

                $this->userModel->updatePasswordAndClearToken($userId, $newPassword);
                $success = 'Mật khẩu đã được đổi thành công! <a href="index.php?action=login">Đăng nhập ngay</a>';
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