<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f7fc">
    <title>Đăng nhập quản trị - EduTest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-login-page">
<main class="admin-login-shell" aria-label="Cổng quản trị EduTest">
    <section class="admin-login-brand" aria-labelledby="admin-brand-heading">
        <a class="admin-brand" href="index.php?action=home">
            <span class="admin-brand-mark" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
            <span>EduTest</span>
        </a>
        <div class="admin-login-copy">
            <p class="admin-eyebrow">Cổng quản trị</p>
            <h1 id="admin-brand-heading">Quản trị hệ thống, rõ ràng và an toàn.</h1>
            <p>Khu vực dành riêng cho đội ngũ quản trị EduTest.</p>
        </div>
        <div class="admin-login-stamp">EduTest · Administration</div>
    </section>

    <section class="admin-login-content" aria-labelledby="admin-login-heading">
        <div class="admin-login-form">
            <h2 id="admin-login-heading">Đăng nhập quản trị</h2>
            <p class="admin-login-intro">Sử dụng tài khoản quản trị để tiếp tục.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2" id="adminLoginError" role="alert" tabindex="-1">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form id="adminLoginForm" action="index.php?action=admin_login_process" method="POST">
                <div class="mb-3">
                    <label for="adminEmail" class="form-label">Email quản trị</label>
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true"><i class="fa-regular fa-envelope"></i></span>
                        <input type="email" id="adminEmail" name="email" class="form-control" autocomplete="username" inputmode="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="adminPassword" class="form-label">Mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" id="adminPassword" name="password" class="form-control" autocomplete="current-password" required>
                        <button type="button" class="btn btn-outline-secondary" data-admin-password-toggle aria-label="Hiện mật khẩu" aria-controls="adminPassword" aria-pressed="false" title="Hiện mật khẩu">
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-admin-primary admin-submit" id="adminLoginSubmit">
                    <i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i>
                    <span data-submit-label>Vào trang quản trị</span>
                </button>
            </form>
            <a href="index.php?action=home" class="admin-login-back"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Về trang học tập</a>
        </div>
    </section>
</main>
<script>
    const passwordToggle = document.querySelector('[data-admin-password-toggle]');
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

    const loginError = document.getElementById('adminLoginError');
    if (loginError) loginError.focus();

    document.getElementById('adminLoginForm').addEventListener('submit', () => {
        const button = document.getElementById('adminLoginSubmit');
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.querySelector('[data-submit-label]').textContent = 'Đang đăng nhập...';
    });
</script>
</body>
</html>