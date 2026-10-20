<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f7fc">
    <title>Quên mật khẩu - EduTest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-login-page">
<main class="admin-login-shell" aria-label="Khôi phục mật khẩu EduTest">
    <section class="admin-login-brand" aria-labelledby="auth-brand-heading">
        <a href="index.php?action=home" class="admin-brand">
            <span class="admin-brand-mark" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
            <span>EduTest</span>
        </a>
        <div class="admin-login-copy">
            <p class="admin-eyebrow">Hỗ trợ tài khoản</p>
            <h1 id="auth-brand-heading">Lấy lại quyền truy cập an toàn.</h1>
            <p>Chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu đến email đăng ký của bạn.</p>
        </div>
        <div class="admin-login-stamp">EduTest · Học tập trực tuyến</div>
    </section>

    <section class="admin-login-content" aria-labelledby="forgot-heading">
        <div class="admin-login-form auth-form">
            <h2 id="forgot-heading">Quên mật khẩu?</h2>
            <p class="admin-login-intro">Nhập email đã dùng để đăng ký tài khoản.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2" role="alert" tabindex="-1">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($message)): ?>
                <div class="alert alert-info py-2" role="status">
                    <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="index.php?action=forgot_password" method="POST">
                <div class="mb-4">
                    <label for="forgotEmail" class="form-label">Email đăng ký</label>
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true"><i class="fa-regular fa-envelope"></i></span>
                        <input type="email" id="forgotEmail" name="email" class="form-control" autocomplete="email" inputmode="email" placeholder="name@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-admin-primary auth-submit">
                    <i class="fa-regular fa-paper-plane" aria-hidden="true"></i>
                    Gửi liên kết khôi phục
                </button>
            </form>
            <a href="index.php?action=login" class="admin-login-back"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Quay lại đăng nhập</a>
        </div>
    </section>
</main>
</body>
</html>
