<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f7fc">
    <title>Đăng ký - EduTest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-login-page">
<main class="admin-login-shell" aria-label="Tạo tài khoản EduTest">
    <section class="admin-login-brand" aria-labelledby="auth-brand-heading">
        <a href="index.php?action=home" class="admin-brand">
            <span class="admin-brand-mark" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
            <span>EduTest</span>
        </a>
        <div class="admin-login-copy">
            <p class="admin-eyebrow">Bắt đầu học tập</p>
            <h1 id="auth-brand-heading">Một tài khoản, nhiều cơ hội tiến bộ.</h1>
            <p>Tạo hồ sơ cá nhân để tham gia kỳ thi, theo dõi kết quả và trao đổi cùng cộng đồng.</p>
        </div>
        <div class="admin-login-stamp">EduTest · Học tập trực tuyến</div>
    </section>

    <section class="admin-login-content" aria-labelledby="register-heading">
        <div class="admin-login-form auth-form">
            <h2 id="register-heading">Tạo tài khoản</h2>
            <p class="admin-login-intro">Điền thông tin để bắt đầu sử dụng EduTest.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2" role="alert" tabindex="-1">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success py-2" role="status">
                    <?= htmlspecialchars(strip_tags($success), ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="index.php?action=register" method="POST">
                <div class="alert alert-primary py-2 small" role="note">
                    <i class="fa-solid fa-circle-info me-1" aria-hidden="true"></i>
                    Tài khoản mới mặc định là học sinh. Quản trị viên có thể cấp quyền giáo viên sau khi đăng ký.
                </div>
                <div class="mb-3">
                    <label for="registerName" class="form-label">Họ và tên</label>
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true"><i class="fa-regular fa-user"></i></span>
                        <input type="text" id="registerName" name="full_name" class="form-control" autocomplete="name" maxlength="100" placeholder="Nguyễn Văn A" value="<?= htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="registerEmail" class="form-label">Địa chỉ email</label>
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true"><i class="fa-regular fa-envelope"></i></span>
                        <input type="email" id="registerEmail" name="email" class="form-control" autocomplete="email" inputmode="email" maxlength="100" placeholder="name@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="registerSchool" class="form-label">Trường học <span class="text-muted fw-normal">(không bắt buộc)</span></label>
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true"><i class="fa-solid fa-school"></i></span>
                        <input type="text" id="registerSchool" name="school_name" class="form-control" autocomplete="organization" maxlength="150" placeholder="Trường học của bạn" value="<?= htmlspecialchars($_POST['school_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="regPassword" class="form-label">Mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" id="regPassword" name="password" class="form-control" autocomplete="new-password" placeholder="Ít nhất 6 ký tự" minlength="6" required>
                        <button class="btn btn-outline-secondary" type="button" data-auth-password-toggle aria-label="Hiện mật khẩu" aria-controls="regPassword" aria-pressed="false" title="Hiện mật khẩu">
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="form-text">Sử dụng ít nhất 6 ký tự.</div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label small text-muted" for="terms">
                        Tôi đồng ý với <a href="#" class="auth-inline-link">Điều khoản dịch vụ</a> và <a href="#" class="auth-inline-link">Chính sách bảo mật</a>.
                    </label>
                </div>
                <button type="submit" class="btn btn-admin-primary auth-submit">
                    <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                    Tạo tài khoản
                </button>
            </form>
            <p class="auth-footer">Đã có tài khoản? <a href="index.php?action=login" class="auth-inline-link">Đăng nhập</a></p>
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
