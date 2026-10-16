<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f4f8f7">
    <title>Đăng nhập - EduTest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --ink: #173b38;
            --muted: #536967;
            --teal: #0f766e;
            --teal-deep: #0b514c;
            --teal-soft: #d9efeb;
            --amber: #b45309;
            --line: #d7e4e1;
            --paper: #ffffff;
            --page: #f4f8f7;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            min-height: 100svh;
            display: grid;
            place-items: center;
            margin: 0;
            padding: 32px 20px;
            color: var(--ink);
            background-color: var(--page);
            background-image: linear-gradient(rgba(15, 118, 110, .035) 1px, transparent 1px), linear-gradient(90deg, rgba(15, 118, 110, .035) 1px, transparent 1px);
            background-size: 32px 32px;
            font-family: 'Open Sans', sans-serif;
        }

        .auth-shell {
            display: grid;
            grid-template-columns: minmax(0, .92fr) minmax(360px, 1.08fr);
            width: min(100%, 980px);
            min-height: 580px;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--paper);
            animation: arrive 420ms ease-out both;
        }

        .brand-panel {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 44px 42px 38px;
            color: #f4fffd;
            background: var(--teal-deep);
        }

        .brand-link {
            display: inline-flex;
            align-items: center;
            gap: 11px;
            width: fit-content;
            color: inherit;
            font-family: 'Poppins', sans-serif;
            font-size: 21px;
            font-weight: 600;
            text-decoration: none;
        }

        .brand-mark {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .34);
            border-radius: 8px;
            color: #d9efeb;
            font-size: 17px;
        }

        .brand-copy { max-width: 360px; padding: 36px 0; }

        .eyebrow {
            margin: 0 0 16px;
            color: #a8e0d8;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .11em;
            text-transform: uppercase;
        }

        .brand-copy h1 {
            max-width: 340px;
            margin: 0 0 18px;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: clamp(30px, 3.4vw, 40px);
            font-weight: 600;
            line-height: 1.2;
        }

        .brand-copy p {
            max-width: 330px;
            margin: 0;
            color: #d3e9e5;
            font-size: 15px;
            line-height: 1.75;
        }

        .brand-rule {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #c8e5e0;
            font-size: 13px;
        }

        .brand-rule::before {
            width: 26px;
            height: 2px;
            background: #e5a34e;
            content: '';
        }

        .login-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px clamp(32px, 6vw, 72px);
        }

        .login-content { width: 100%; max-width: 380px; }

        .login-content h2 {
            margin: 0 0 8px;
            color: var(--ink);
            font-family: 'Poppins', sans-serif;
            font-size: 29px;
            font-weight: 600;
            line-height: 1.3;
        }

        .login-intro { margin: 0 0 30px; color: var(--muted); font-size: 15px; line-height: 1.6; }
        .form-label { margin-bottom: 8px; color: var(--ink); font-size: 14px; font-weight: 700; }
        .field-block { margin-bottom: 21px; }

        .form-control {
            min-height: 50px;
            border-color: #cbd9d6;
            border-radius: 5px;
            color: var(--ink);
            font-size: 15px;
        }

        .form-control:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(15, 118, 110, .17);
        }

        .input-group > .form-control { border-radius: 5px 0 0 5px; }

        .password-toggle {
            min-width: 50px;
            border-color: #cbd9d6;
            border-radius: 0 5px 5px 0;
            color: var(--muted);
        }

        .password-toggle:hover { border-color: var(--teal); color: var(--teal-deep); background: #f3faf8; }

        .forgot-link, .register-link {
            color: var(--teal-deep);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        .forgot-link:hover, .register-link:hover { color: var(--amber); text-decoration: underline; }

        .submit-button {
            min-height: 50px;
            margin-top: 3px;
            border: 0;
            border-radius: 5px;
            color: #fff;
            background: var(--teal);
            font-size: 15px;
            font-weight: 700;
            transition: background-color 160ms ease, transform 160ms ease;
        }

        .submit-button:hover { color: #fff; background: var(--teal-deep); transform: translateY(-1px); }
        .submit-button:disabled { color: #fff; background: #557e79; }

        a:focus-visible, button:focus-visible {
            outline: 3px solid #e5a34e;
            outline-offset: 3px;
        }

        .login-footer {
            margin: 27px 0 0;
            padding-top: 20px;
            border-top: 1px solid #e4ecea;
            color: var(--muted);
            font-size: 14px;
            text-align: center;
        }

        .alert { border-radius: 5px; font-size: 14px; }

        @keyframes arrive {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 720px) {
            body { padding: 16px; }
            .auth-shell { grid-template-columns: 1fr; min-height: 0; }
            .brand-panel { min-height: 205px; padding: 24px 26px; }
            .brand-copy { padding: 24px 0 14px; }
            .brand-copy h1 { margin-bottom: 8px; font-size: 27px; }
            .brand-copy p { font-size: 14px; }
            .brand-rule { display: none; }
            .login-panel { padding: 32px 26px 30px; }
            .login-content h2 { font-size: 25px; }
            .login-intro { margin-bottom: 24px; }
        }

        @media (max-width: 380px) {
            body { padding: 10px; }
            .brand-panel { padding: 20px; }
            .login-panel { padding: 27px 20px; }
            .login-heading-row { align-items: flex-start !important; flex-direction: column; gap: 3px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; transition-duration: .01ms !important; }
        }
    </style>
    <link rel="stylesheet" href="assets/css/edutest.css">
</head>
<body>
<main class="auth-shell" aria-label="Đăng nhập EduTest">
    <section class="brand-panel" aria-labelledby="brand-heading">
        <a href="index.php" class="brand-link">
            <span class="brand-mark" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
            <span>EduTest</span>
        </a>
        <div class="brand-copy">
            <p class="eyebrow">Không gian học tập</p>
            <h1 id="brand-heading">Tiếp tục hành trình học của bạn.</h1>
            <p>Đăng nhập để trở lại đúng nơi bạn đã dừng.</p>
        </div>
        <div class="brand-rule" aria-hidden="true">EduTest · Học tập trực tuyến</div>
    </section>

    <section class="login-panel" aria-labelledby="login-heading">
        <div class="login-content">
            <h2 id="login-heading">Chào mừng trở lại</h2>
            <p class="login-intro">Nhập thông tin tài khoản để tiếp tục.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 mb-4" id="loginError" role="alert" tabindex="-1">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" action="index.php?action=login" method="POST">
                <div class="field-block">
                    <label for="loginEmail" class="form-label">Email</label>
                    <input type="email" id="loginEmail" name="email" class="form-control" autocomplete="email" inputmode="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="field-block">
                    <div class="login-heading-row d-flex justify-content-between align-items-center mb-2">
                        <label for="loginPassword" class="form-label mb-0">Mật khẩu</label>
                        <a href="index.php?action=forgot_password" class="forgot-link">Quên mật khẩu?</a>
                    </div>
                    <div class="input-group">
                        <input type="password" id="loginPassword" name="password" class="form-control" autocomplete="current-password" required>
                        <button class="btn password-toggle" type="button" data-password-toggle aria-label="Hiện mật khẩu" aria-controls="loginPassword" aria-pressed="false" title="Hiện mật khẩu">
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn submit-button w-100" id="loginSubmit">
                    <span data-submit-label>Đăng nhập</span>
                </button>
            </form>

            <p class="login-footer">Chưa có tài khoản? <a href="index.php?action=register" class="register-link">Đăng ký ngay</a></p>
        </div>
    </section>
</main>
<script>
    const passwordToggle = document.querySelector('[data-password-toggle]');
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