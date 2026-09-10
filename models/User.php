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

    public function create($role, $fullName, $email, $schoolName, $password) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO users (role, full_name, email, school_name, password) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$role, $fullName, $email, $schoolName, $hashedPassword]);
    }

    public function setResetToken($email, $token, $expires) {
        $stmt = $this->pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
        return $stmt->execute([$token, $expires, $email]);
    }

    public function findByResetToken($token) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function updatePasswordAndClearToken($userId, $password) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE user_id = ?");
        return $stmt->execute([$hashedPassword, $userId]);
    }
}
?>