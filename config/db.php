<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
$host = 'localhost';
$db   = 'db'; // Tên CSDL của bạn
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->exec("SET time_zone = '+07:00';");
} catch (\PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}
?>