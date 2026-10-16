<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { border-radius: 20px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08); background-color: #ffffff; width: 100%; max-width: 450px; }
    </style>
</head>
<body>
<div class="auth-card p-4">
    <div class="text-center mb-4">
        <a href="index.php?action=home" class="text-decoration-none text-primary fw-bold fs-3">
            <i class="fa-solid fa-graduation-cap me-2"></i>EduTest
        </a>
        <h5 class="fw-bold mt-3 mb-1">Đặt lại mật khẩu</h5>
        <p class="small text-muted">Nhập email đăng ký của bạn để nhận liên kết khôi phục.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info py-2 small"><?php echo $message; ?></div>
    <?php endif; ?>

    <form action="index.php?action=forgot_password" method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Email đăng ký</label>
            <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3 mb-3">
            Gửi liên kết khôi phục <i class="fa-solid fa-paper-plane ms-1"></i>
        </button>
    </form>

    <div class="text-center pt-2 border-top">
        <a href="index.php?action=login" class="small text-decoration-none text-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Đăng nhập
        </a>
    </div>
</div>
</body>
</html>