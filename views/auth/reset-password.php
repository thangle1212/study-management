<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f7fc">
    <title>Đặt lại mật khẩu - EduTest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-login-page">
<main class="admin-login-shell" aria-label="Đặt lại mật khẩu EduTest">
    <section class="admin-login-brand" aria-labelledby="auth-brand-heading">
        <a href="index.php?action=home" class="admin-brand">
            <span class="admin-brand-mark" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
            <span>EduTest</span>
        </a>
        <div class="admin-login-copy">
            <p class="admin-eyebrow">Bảo mật tài khoản</p>
            <h1 id="auth-brand-heading">Chọn mật khẩu mới cho tài khoản.</h1>
            <p>Hãy dùng mật khẩu riêng và dễ nhớ để bảo vệ hành trình học tập của bạn.</p>
        </div>
        <div class="admin-login-stamp">EduTest · Học tập trực tuyến</div>
    </section>

    <section class="admin-login-content" aria-labelledby="reset-heading">
        <div class="admin-login-form auth-form">
            <h2 id="reset-heading">Tạo mật khẩu mới</h2>
            <p class="admin-login-intro">Mật khẩu cần có ít nhất 6 ký tự.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2" role="alert" tabindex="-1">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success py-2" role="status">
                    <?= htmlspecialchars(strip_tags($success), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <a href="index.php?action=login" class="btn btn-admin-primary auth-submit">
                    <i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i>
                    Đăng nhập
                </a>
            <?php else: ?>
                <form action="index.php?action=reset_password&amp;token=<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>" method="POST">
                    <div class="mb-3">
                        <label for="resetPassword" class="form-label">Mật khẩu mới</label>
                        <div class="input-group">
                            <span class="input-group-text" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" id="resetPassword" name="password" class="form-control" autocomplete="new-password" minlength="6" required>
                            <button class="btn btn-outline-secondary" type="button" data-auth-password-toggle aria-label="Hiện mật khẩu" aria-controls="resetPassword" aria-pressed="false" title="Hiện mật khẩu">
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="resetConfirmPassword" class="form-label">Xác nhận mật khẩu mới</label>
                        <div class="input-group">
                            <span class="input-group-text" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" id="resetConfirmPassword" name="confirm_password" class="form-control" autocomplete="new-password" minlength="6" required>
                            <button class="btn btn-outline-secondary" type="button" data-auth-password-toggle aria-label="Hiện mật khẩu" aria-controls="resetConfirmPassword" aria-pressed="false" title="Hiện mật khẩu">
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-admin-primary auth-submit">
                        <i class="fa-solid fa-key" aria-hidden="true"></i>
                        Cập nhật mật khẩu
                    </button>
                </form>
            <?php endif; ?>
            <a href="index.php?action=login" class="admin-login-back"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Quay lại đăng nhập</a>
        </div>
    </section>
</main>
<script>
    document.querySelectorAll('[data-auth-password-toggle]').forEach((passwordToggle) => {
        passwordToggle.addEventListener('click', () => {
            const input = document.getElementById(passwordToggle.getAttribute('aria-controls'));
            const icon = passwordToggle.querySelector('i');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            passwordToggle.setAttribute('aria-pressed', String(isHidden));
            passwordToggle.setAttribute('aria-label', isHidden ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
            passwordToggle.title = isHidden ? 'Ẩn mật khẩu' : 'Hiện mật khẩu';
            icon.classList.toggle('fa-eye', !isHidden);
            icon.classList.toggle('fa-eye-slash', isHidden);
        });
    });
</script>
</body>
</html>
