<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f7fc">
    <title>Đăng nhập - EduTest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-login-page">
<main class="admin-login-shell" aria-label="Đăng nhập EduTest">
    <section class="admin-login-brand" aria-labelledby="auth-brand-heading">
        <a href="index.php?action=home" class="admin-brand">
            <span class="admin-brand-mark" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
            <span>EduTest</span>
        </a>
        <div class="admin-login-copy">
            <p class="admin-eyebrow">Không gian học tập</p>
            <h1 id="auth-brand-heading">Tiếp tục hành trình học của bạn.</h1>
            <p>Đăng nhập để trở lại đúng nơi bạn đã dừng và tiếp tục chinh phục mục tiêu.</p>
        </div>
        <div class="admin-login-stamp">EduTest · Học tập trực tuyến</div>
    </section>

    <section class="admin-login-content" aria-labelledby="login-heading">
        <div class="admin-login-form auth-form">
            <h2 id="login-heading">Chào mừng trở lại</h2>
            <p class="admin-login-intro">Nhập thông tin tài khoản để tiếp tục.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 mb-4" id="loginError" role="alert" tabindex="-1">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" action="index.php?action=login" method="POST">
                <div class="mb-3">
                    <label for="loginEmail" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true"><i class="fa-regular fa-envelope"></i></span>
                        <input type="email" id="loginEmail" name="email" class="form-control" autocomplete="email" inputmode="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <label for="loginPassword" class="form-label mb-0">Mật khẩu</label>
                        <a href="index.php?action=forgot_password" class="auth-inline-link">Quên mật khẩu?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" id="loginPassword" name="password" class="form-control" autocomplete="current-password" required>
                        <button class="btn btn-outline-secondary" type="button" data-auth-password-toggle aria-label="Hiện mật khẩu" aria-controls="loginPassword" aria-pressed="false" title="Hiện mật khẩu">
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-admin-primary auth-submit" id="loginSubmit">
                    <i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i>
                    <span data-submit-label>Đăng nhập</span>
                </button>
            </form>

            <p class="auth-footer">Chưa có tài khoản? <a href="index.php?action=register" class="auth-inline-link">Đăng ký ngay</a></p>
        </div>
    </section>
</main>
<script>
    const passwordToggle = document.querySelector('[data-auth-password-toggle]');
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

    const loginError = document.getElementById('loginError');
    if (loginError) loginError.focus();

    document.getElementById('loginForm').addEventListener('submit', () => {
        const submitButton = document.getElementById('loginSubmit');
        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');
        submitButton.querySelector('[data-submit-label]').textContent = 'Đang đăng nhập...';
    });
</script>
</body>
</html>
