<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';

class ProfileController extends BaseController {
    private $userModel;

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->userModel = new User($pdo);
    }

    public function index() {
        $this->requireLogin();
        if (!in_array($_SESSION['user']['role'], ['student', 'teacher'], true)) {
            http_response_code(403);
            exit('Bạn không có quyền truy cập hồ sơ người dùng.');
        }

        $userId = (int) $_SESSION['user']['id'];
        $profile = $this->userModel->getProfileById($userId);
        if (!$profile) {
            unset($_SESSION['user'], $_SESSION['csrf_token']);
            session_destroy();
            header('Location: index.php?action=login');
            exit;
        }

        $errors = [];
        $formData = [
            'full_name' => $profile['full_name'],
            'school_name' => $profile['school_name'] ?? '',
        ];
        $success = $_SESSION['profile_success'] ?? '';
        unset($_SESSION['profile_success']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostWithCsrf();

            $rawFullName = $_POST['full_name'] ?? null;
            $rawSchoolName = $_POST['school_name'] ?? '';
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (!is_string($rawFullName)) {
                $errors['full_name'] = 'Họ và tên không hợp lệ.';
            } else {
                $formData['full_name'] = trim($rawFullName);
                if ($formData['full_name'] === '' || strlen($formData['full_name']) > 100) {
                    $errors['full_name'] = 'Họ tên phải có từ 1 đến 100 ký tự.';
                }
            }

            if (!is_string($rawSchoolName)) {
                $errors['school_name'] = 'Tên trường học không hợp lệ.';
            } else {
                $formData['school_name'] = trim($rawSchoolName);
                if (strlen($formData['school_name']) > 150) {
                    $errors['school_name'] = 'Tên trường học không được vượt quá 150 ký tự.';
                }
            }

            if (!is_string($currentPassword)) {
                $errors['current_password'] = 'Mật khẩu hiện tại không hợp lệ.';
                $currentPassword = '';
            }
            if (!is_string($newPassword)) {
                $errors['new_password'] = 'Mật khẩu mới không hợp lệ.';
                $newPassword = '';
            }
            if (!is_string($confirmPassword)) {
                $errors['confirm_password'] = 'Xác nhận mật khẩu không hợp lệ.';
                $confirmPassword = '';
            }

            $passwordChangeRequested = $currentPassword !== ''
                || $newPassword !== ''
                || $confirmPassword !== '';
            $newPasswordToSave = null;

            if ($passwordChangeRequested) {
                $storedPasswordHash = $this->userModel->getPasswordHashById($userId);
                if ($currentPassword === '') {
                    $errors['current_password'] = 'Nhập mật khẩu hiện tại để xác nhận thay đổi.';
                } elseif (!is_string($storedPasswordHash)
                    || !password_verify($currentPassword, $storedPasswordHash)) {
                    $errors['current_password'] = 'Mật khẩu hiện tại chưa chính xác.';
                }

                if ($newPassword === '') {
                    $errors['new_password'] = 'Nhập mật khẩu mới.';
                } elseif (!preg_match('/^.{6,}$/us', $newPassword)) {
                    $errors['new_password'] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
                }

                if ($confirmPassword === '') {
                    $errors['confirm_password'] = 'Nhập lại mật khẩu mới để xác nhận.';
                } elseif ($newPassword !== $confirmPassword) {
                    $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp.';
                }

                if (!isset($errors['current_password']) && !isset($errors['new_password'])
                    && !isset($errors['confirm_password'])) {
                    $newPasswordToSave = $newPassword;
                }
            }

            if (!$errors) {
                if (!$this->userModel->updateProfile(
                    $userId,
                    $formData['full_name'],
                    $formData['school_name'] === '' ? null : $formData['school_name'],
                    $newPasswordToSave
                )) {
                    $errors['form'] = 'Không thể lưu thay đổi. Vui lòng thử lại.';
                } else {
                    $_SESSION['user']['full_name'] = $formData['full_name'];
                    if ($newPasswordToSave !== null) {
                        session_regenerate_id(true);
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }
                    $_SESSION['profile_success'] = 'Thông tin hồ sơ đã được cập nhật.';
                    header('Location: index.php?action=profile');
                    exit;
                }
            }
        }

        $csrfToken = $_SESSION['csrf_token'];
        require __DIR__ . '/../views/profile.php';
    }
}
