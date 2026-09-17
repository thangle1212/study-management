<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.08); background: #fff; width: 100%; max-width: 420px; }
    </style>
</head>
<body>
<div class="auth-card p-4">
    <div class="text-center mb-4">
        <a href="index.php" class="text-decoration-none text-primary fw-bold fs-3"><i class="fa-solid fa-graduation-cap me-2"></i>EduTest</a>
        <h5 class="fw-bold mt-3 mb-1">Đăng nhập</h5>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="index.php?action=login" method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label class="form-label fw-semibold">Mật khẩu</label>
                <a href="index.php?action=forgot_password" class="small text-decoration-none">Quên mật khẩu?</a>
            </div>
            <div class="input-group">
                <input type="password" id="loginPassword" name="password" class="form-control" required>
                <button class="btn btn-outline-secondary" type="button" aria-label="Hiện mật khẩu" aria-pressed="false" onclick="togglePassword('loginPassword', this)">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3 mb-3">Đăng nhập</button>
    </form>
    <div class="text-center pt-2 border-top">
        <p class="small text-muted mb-0">Chưa có tài khoản? <a href="index.php?action=register" class="text-primary fw-semibold text-decoration-none">Đăng ký ngay</a></p>
    </div>
</div>
<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.setAttribute('aria-pressed', String(isHidden));
        btn.setAttribute('aria-label', isHidden ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        icon.classList.toggle('fa-eye', !isHidden);
        icon.classList.toggle('fa-eye-slash', isHidden);
    }
</script>
</body>
</html>