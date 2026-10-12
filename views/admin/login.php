<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập Quản trị - EduTest Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow-lg border-0 p-4" style="width: 100%; max-width: 400px; border-radius: 12px;">
        <div class="text-center mb-4">
            <div class="text-danger mb-2">
                <i class="bi bi-shield-lock-fill display-4"></i>
            </div>
            <h3 class="fw-bold text-dark">EduTest Admin</h3>
            <p class="text-muted small">Khu vực đăng nhập dành riêng cho Quản trị viên</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 text-center small"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="index.php?action=admin_login_process" method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold small">Email Quản trị</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="admin@edutest.com" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-dark w-100 py-2 fw-bold shadow-sm">
                <i class="bi bi-box-arrow-in-right me-2"></i> Truy cập Hệ thống
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="index.php?action=home" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left me-1"></i> Quay về trang chủ học tập
            </a>
        </div>
    </div>
</body>
</html>