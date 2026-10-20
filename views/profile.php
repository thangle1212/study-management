<?php
$userRole = $_SESSION['user']['role'] ?? '';
$roleLabels = [
    'student' => 'Học sinh',
    'teacher' => 'Giáo viên',
];
$roleLabel = $roleLabels[$userRole] ?? 'Thành viên';
$userIcon = $userRole === 'teacher' ? 'fa-chalkboard-user' : 'fa-user-graduate';
$profileErrors = [
    'full_name' => 'Họ và tên: ' . ($errors['full_name'] ?? ''),
    'school_name' => 'Trường học: ' . ($errors['school_name'] ?? ''),
    'current_password' => 'Mật khẩu hiện tại: ' . ($errors['current_password'] ?? ''),
    'new_password' => 'Mật khẩu mới: ' . ($errors['new_password'] ?? ''),
    'confirm_password' => 'Xác nhận mật khẩu: ' . ($errors['confirm_password'] ?? ''),
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f7fc">
    <title>Hồ sơ của tôi - EduTest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-app">
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a class="admin-brand" href="index.php?action=dashboard">
            <span class="admin-brand-mark" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
            <span>EduTest</span>
        </a>
        <nav class="admin-nav" aria-label="Điều hướng chính">
            <a class="admin-nav-link" href="index.php?action=dashboard">
                <i class="fa-solid fa-house" aria-hidden="true"></i><span>Tổng quan</span>
            </a>
            <a class="admin-nav-link active" href="index.php?action=profile" aria-current="page">
                <i class="fa-regular fa-id-card" aria-hidden="true"></i><span>Hồ sơ của tôi</span>
            </a>
            <?php if ($userRole === 'student'): ?>
                <a class="admin-nav-link" href="index.php?action=student_statistics">
                    <i class="fa-solid fa-chart-line" aria-hidden="true"></i><span>Thống kê của tôi</span>
                </a>
            <?php elseif ($userRole === 'teacher'): ?>
                <a class="admin-nav-link" href="index.php?action=qe_banks">
                    <i class="fa-solid fa-layer-group" aria-hidden="true"></i><span>Ngân hàng câu hỏi</span>
                </a>
                <a class="admin-nav-link" href="index.php?action=qe_exam_create">
                    <i class="fa-solid fa-file-circle-plus" aria-hidden="true"></i><span>Tạo đề thi</span>
                </a>
            <?php endif; ?>
            <a class="admin-nav-link" href="index.php?action=forum">
                <i class="fa-regular fa-comments" aria-hidden="true"></i><span>Diễn đàn</span>
            </a>
        </nav>
        <div class="admin-sidebar-bottom">
            <span class="admin-account-avatar" aria-hidden="true"><i class="fa-solid <?= htmlspecialchars($userIcon, ENT_QUOTES, 'UTF-8') ?>"></i></span>
            <div class="admin-account-copy">
                <div class="admin-account-name"><?= htmlspecialchars($profile['full_name'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="admin-account-role"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <a class="admin-logout" href="index.php?action=logout" aria-label="Đăng xuất" title="Đăng xuất">
                <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
            </a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <p class="admin-breadcrumb">EduTest <span aria-hidden="true">/</span> <strong>Hồ sơ của tôi</strong></p>
            <a href="index.php?action=dashboard" class="admin-topbar-link"><i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i> Không gian học tập</a>
        </header>

        <main class="admin-content">
            <div class="admin-page-heading">
                <div>
                    <p class="admin-eyebrow">Tài khoản cá nhân</p>
                    <h1>Hồ sơ của tôi</h1>
                    <p>Cập nhật thông tin cá nhân và giữ tài khoản của bạn an toàn.</p>
                </div>
                <span class="user-role-badge"><i class="fa-solid <?= htmlspecialchars($userIcon, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success profile-alert" role="status">
                    <i class="fa-solid fa-circle-check me-2" aria-hidden="true"></i><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-danger profile-alert" id="profileErrorSummary" role="alert" tabindex="-1" aria-labelledby="profileErrorHeading">
                    <h2 id="profileErrorHeading" class="h6 fw-bold mb-2">Vui lòng kiểm tra lại thông tin</h2>
                    <ul class="mb-0">
                        <?php if (isset($errors['form'])): ?>
                            <li><?= htmlspecialchars($errors['form'], ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endif; ?>
                        <?php foreach ($profileErrors as $field => $label): ?>
                            <?php if (isset($errors[$field])): ?>
                                <li><a href="#<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="profile-layout">
                <section class="admin-panel profile-summary" aria-labelledby="profile-summary-heading">
                    <div class="profile-summary-avatar" aria-hidden="true"><i class="fa-solid <?= htmlspecialchars($userIcon, ENT_QUOTES, 'UTF-8') ?>"></i></div>
                    <h2 id="profile-summary-heading"><?= htmlspecialchars($formData['full_name'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <span class="user-role-badge"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></span>
                    <div class="profile-summary-divider"></div>
                    <dl class="profile-summary-details">
                        <div>
                            <dt><i class="fa-regular fa-envelope" aria-hidden="true"></i>Email</dt>
                            <dd><?= htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                        <div>
                            <dt><i class="fa-solid fa-school" aria-hidden="true"></i>Trường học</dt>
                            <dd><?= htmlspecialchars($formData['school_name'] !== '' ? $formData['school_name'] : 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                    </dl>
                    <p class="profile-privacy-note"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i>Email, vai trò và dữ liệu học tập được bảo vệ.</p>
                </section>

                <form action="index.php?action=profile" method="POST" class="profile-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <section class="admin-panel profile-panel" aria-labelledby="profile-info-heading">
                        <div class="admin-panel-heading">
                            <div>
                                <h2 id="profile-info-heading">Thông tin cá nhân</h2>
                                <p>Các thay đổi sẽ được dùng trong hồ sơ và hoạt động của bạn.</p>
                            </div>
                            <span class="admin-metric-icon" aria-hidden="true"><i class="fa-regular fa-id-card"></i></span>
                        </div>
                        <div class="profile-panel-body">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Họ và tên</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" autocomplete="name" maxlength="100" required value="<?= htmlspecialchars($formData['full_name'], ENT_QUOTES, 'UTF-8') ?>" aria-invalid="<?= isset($errors['full_name']) ? 'true' : 'false' ?>" <?= isset($errors['full_name']) ? 'aria-describedby="full-name-error"' : '' ?>>
                                <?php if (isset($errors['full_name'])): ?>
                                    <div class="invalid-feedback d-block" id="full-name-error"><?= htmlspecialchars($errors['full_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="profileEmail" class="form-label">Email</label>
                                <input type="email" id="profileEmail" class="form-control profile-readonly" value="<?= htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') ?>" readonly aria-describedby="email-help">
                                <div class="form-text" id="email-help">Email không thể thay đổi tại đây.</div>
                            </div>
                            <div class="mb-0">
                                <label for="school_name" class="form-label">Trường học <span class="text-muted fw-normal">(không bắt buộc)</span></label>
                                <input type="text" id="school_name" name="school_name" class="form-control" autocomplete="organization" maxlength="150" value="<?= htmlspecialchars($formData['school_name'], ENT_QUOTES, 'UTF-8') ?>" aria-invalid="<?= isset($errors['school_name']) ? 'true' : 'false' ?>" <?= isset($errors['school_name']) ? 'aria-describedby="school-name-error"' : '' ?>>
                                <?php if (isset($errors['school_name'])): ?>
                                    <div class="invalid-feedback d-block" id="school-name-error"><?= htmlspecialchars($errors['school_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                    <section class="admin-panel profile-panel" aria-labelledby="profile-password-heading">
                        <div class="admin-panel-heading">
                            <div>
                                <h2 id="profile-password-heading">Đổi mật khẩu</h2>
                                <p>Để trống cả ba ô nếu bạn không muốn đổi mật khẩu.</p>
                            </div>
                            <span class="admin-metric-icon" aria-hidden="true"><i class="fa-solid fa-shield-halved"></i></span>
                        </div>
                        <div class="profile-panel-body">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password" aria-invalid="<?= isset($errors['current_password']) ? 'true' : 'false' ?>" <?= isset($errors['current_password']) ? 'aria-describedby="current-password-error"' : '' ?>>
                                <?php if (isset($errors['current_password'])): ?>
                                    <div class="invalid-feedback d-block" id="current-password-error"><?= htmlspecialchars($errors['current_password'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="new_password" class="form-label">Mật khẩu mới</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" autocomplete="new-password" minlength="6" aria-invalid="<?= isset($errors['new_password']) ? 'true' : 'false' ?>" <?= isset($errors['new_password']) ? 'aria-describedby="new-password-help new-password-error"' : 'aria-describedby="new-password-help"' ?>>
                                    <div class="form-text" id="new-password-help">Tối thiểu 6 ký tự.</div>
                                    <?php if (isset($errors['new_password'])): ?>
                                        <div class="invalid-feedback d-block" id="new-password-error"><?= htmlspecialchars($errors['new_password'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" autocomplete="new-password" minlength="6" aria-invalid="<?= isset($errors['confirm_password']) ? 'true' : 'false' ?>" <?= isset($errors['confirm_password']) ? 'aria-describedby="confirm-password-error"' : '' ?>>
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback d-block" id="confirm-password-error"><?= htmlspecialchars($errors['confirm_password'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="profile-form-actions">
                        <a href="index.php?action=dashboard" class="btn-admin-outline">Hủy</a>
                        <button type="submit" class="btn-admin-primary" id="profileSubmit">
                            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i><span data-submit-label>Lưu thay đổi</span>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
<script>
    const errorSummary = document.getElementById('profileErrorSummary');
    if (errorSummary) errorSummary.focus();

    document.querySelector('.profile-form').addEventListener('submit', () => {
        const button = document.getElementById('profileSubmit');
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.querySelector('[data-submit-label]').textContent = 'Đang lưu...';
    });
</script>
</body>
</html>
