<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    // Đăng ký tài khoản
    public function create($role, $fullName, $email, $schoolName, $password) {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $createdAt = date('Y-m-d H:i:s');
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare("INSERT INTO users (role, full_name, email, school_name, password, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$role, $fullName, $email, $schoolName, $hashedPassword, $createdAt]);
    }

    // 1. Lưu Token và Thời gian hết hạn (Kiểu DATETIME)
    public function setResetToken($email, $token) {
        $stmt = $this->pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = DATE_ADD(NOW(), INTERVAL 2 HOUR) WHERE email = ?");
        return $stmt->execute([$token, $email]);
    }

// Kiểm tra Token còn hạn theo thời gian Unix Timestamp của PHP
    public function findByResetToken($token) {
    if (empty($token)) return false;

    $sql = "SELECT * FROM users WHERE reset_token = :token AND reset_token_expires > NOW()";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['token' => $token]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    // 3. Đổi mật khẩu mới và xóa Token
    public function updatePasswordAndClearToken($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE user_id = ?");
        return $stmt->execute([$hashedPassword, $userId]);
    }
}
?>