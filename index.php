<?php
session_start();

// Kết nối CSDL và Controller
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/AuthController.php';

$action = $_GET['action'] ?? 'home';
$authController = new AuthController($pdo);

switch ($action) {
    case 'home':
        require_once __DIR__ . '/views/startpage.php';
        break;

    case 'login':
        $authController->login();
        break;

    case 'register':
        $authController->register();
        break;

    case 'forgot_password':
        $authController->forgotPassword();
        break;

    case 'reset_password':
        $authController->resetPassword();
        break;

    case 'logout':
        $authController->logout();
        break;

    case 'dashboard':
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?action=login');
            exit;
        }
        require_once __DIR__ . '/views/dashboard.php';
        break;

    default:
        require_once __DIR__ . '/views/startpage.php';
        break;
}
?>