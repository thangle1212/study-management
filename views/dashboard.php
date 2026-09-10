<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang chủ - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand fw-bold" href="#">EduTest</a>
    <div class="ms-auto d-flex align-items-center gap-3 text-white">
        <span>Xin chào, <strong><?php echo htmlspecialchars($_SESSION['user']['full_name']); ?></strong> (<?php echo $_SESSION['user']['role']; ?>)</span>
        <a href="index.php?action=logout" class="btn btn-sm btn-light text-primary fw-bold">Đăng xuất</a>
    </div>
</nav>
<div class="container mt-5">
    <div class="card p-4 shadow-sm">
        <h3>Chào mừng bạn đến với hệ thống thi trắc nghiệm EduTest!</h3>
        <p class="text-muted">Bạn đã đăng nhập thành công với vai trò: <strong><?php echo $_SESSION['user']['role']; ?></strong>.</p>
    </div>
</div>
</body>
</html>