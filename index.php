<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
session_start();

// Kết nối CSDL và Controller
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ExamController.php';
<<<<<<< HEAD
require_once __DIR__ . '/controllers/ForumController.php';
=======
require_once __DIR__ . '/controllers/AdminController.php';
>>>>>>> 4f05e28 (fix(core):update code to latest version after resolving conflict)

$action = $_GET['action'] ?? 'home';
$authController = new AuthController($pdo);
$examController = new ExamController($pdo);
<<<<<<< HEAD
$forumController = new ForumController($pdo);
=======
$adminController = new AdminController($pdo);
>>>>>>> 4f05e28 (fix(core):update code to latest version after resolving conflict)

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
        if (($_SESSION['user']['role'] ?? '') === 'admin') {
            $adminController->dashboard();
        } else {
            require_once __DIR__ . '/views/dashboard.php';
        }
        break;

    case 'admin_dashboard':
        $adminController->dashboard();
        break;

    case 'admin_users':
        $adminController->manageUsers();
        break;

    case 'admin_update_user':
        $adminController->updateUserAction();
        break;

    case 'prepare_exam':
        $examController->prepare();
        break;

    case 'start_exam':
        $examController->start();
        break;

    case 'take_exam':
        $examController->take();
        break;

    case 'autosave_answers':
        $examController->autosave();
        break;

    case 'update_exam_settings':
        $examController->updateSettings();
        break;

    case 'submit_exam':
        $examController->submit();
        break;

    case 'log_violation':
        $examController->logViolation();
        break;

    case 'exam_result':
        $examController->result();
        break;

    case 'exam_statistics':
        $examController->statistics();
        break;

    case 'exam_monitoring':
        $examController->monitoring();
        break;

    case 'exam_monitoring_data':
        $examController->monitoringData();
        break;

    case 'review_attempt':
        $examController->reviewAttempt();
        break;

    case 'attempt_detail':
        $examController->attemptDetail();
        break;

    case 'student_statistics':
        $examController->studentStatistics();
        break;

    case 'forum':
        $forumController->index();
        break;

    case 'forum_detail':
        $forumController->detail();
        break;

    case 'forum_create':
        $forumController->create();
        break;

    case 'forum_edit':
        $forumController->edit();
        break;

    case 'forum_delete':
        $forumController->delete();
        break;

    case 'forum_comment_create':
        $forumController->commentCreate();
        break;

    case 'forum_comment_delete':
        $forumController->commentDelete();
        break;

    default:
        require_once __DIR__ . '/views/startpage.php';
        break;
}
?>