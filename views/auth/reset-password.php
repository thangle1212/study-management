<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - EduTest</title>
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
        <h5 class="fw-bold mt-3 mb-1">Tạo mật khẩu mới</h5>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success py-2 small"><?php echo $success; ?></div>
    <?php else: ?>

    <form action="index.php?action=reset_password&token=<?php echo htmlspecialchars($token); ?>" method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Mật khẩu mới</label>
            <div class="input-group">
                <input type="password" id="resetPassword" name="password" class="form-control" required minlength="6">
                <button class="btn btn-outline-secondary" type="button" aria-label="Hiện mật khẩu" aria-pressed="false" onclick="togglePassword('resetPassword', this)">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
            <div class="input-group">
                <input type="password" id="resetConfirmPassword" name="confirm_password" class="form-control" required minlength="6">
                <button class="btn btn-outline-secondary" type="button" aria-label="Hiện mật khẩu" aria-pressed="false" onclick="togglePassword('resetConfirmPassword', this)">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3 mb-3">Cập nhật mật khẩu</button>
    </form>
    <?php endif; ?>
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