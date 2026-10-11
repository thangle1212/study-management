<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tổng quan quản trị - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --brand: #0d6efd; --ink: #17233c; }
        body { background: linear-gradient(135deg, #eaf4ff 0%, #f8fbff 48%, #fff 100%); color: var(--ink); min-height: 100vh; }
        .topbar { background: rgba(255,255,255,.9); border-bottom: 1px solid #e5edf8; backdrop-filter: blur(12px); }
        .brand { color: var(--brand); font-size: 1.35rem; }
        .welcome { border: 0; border-radius: 22px; background: linear-gradient(135deg, #0d6efd, #438df4); color: #fff; box-shadow: 0 16px 36px rgba(13,110,253,.18); }
        .stat-card, .action-card { border: 1px solid #e5edf8; border-radius: 16px; box-shadow: 0 8px 24px rgba(31,74,125,.06); }
        .action-card { transition: transform .2s ease, box-shadow .2s ease; }
        .action-card:hover { transform: translateY(-3px); box-shadow: 0 13px 30px rgba(31,74,125,.11); }
        .stat-icon, .action-icon { width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; border-radius: 13px; }
        .action-icon { width: 52px; height: 52px; font-size: 1.25rem; }
        .action-card-disabled { background: rgba(255,255,255,.78); }
        .action-card-disabled:hover { transform: none; box-shadow: 0 8px 24px rgba(31,74,125,.06); }
    </style>
</head>
<body>
<nav class="navbar topbar sticky-top">
    <div class="container py-2">
        <a class="navbar-brand brand fw-bold" href="index.php?action=admin_dashboard">
            <i class="fa-solid fa-graduation-cap me-2"></i>EduTest
        </a>
        <div class="d-flex align-items-center gap-2 gap-md-3">
            <a href="index.php?action=forum" class="btn btn-sm btn-outline-primary">
                <i class="fa-solid fa-comments me-md-1"></i><span class="d-none d-md-inline">Diễn đàn</span>
            </a>
            <span class="d-none d-md-inline text-muted small">
                Xin chào, <strong><?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></strong>
            </span>
            <a href="index.php?action=logout" class="btn btn-sm btn-outline-danger">
                <i class="fa-solid fa-right-from-bracket me-md-1"></i><span class="d-none d-md-inline">Đăng xuất</span>
            </a>
        </div>
    </div>
</nav>

<main class="container py-4 py-md-5">
    <section class="welcome p-4 p-md-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="small opacity-75 text-uppercase mb-2">
                    <i class="fa-solid fa-shield-halved me-1"></i> Không gian quản trị
                </div>
                <h1 class="h2 fw-bold mb-2">
                    Chào mừng, <?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?>!
                </h1>
                <p class="mb-0 opacity-75">Theo dõi hoạt động và quản lý tài khoản trên hệ thống EduTest.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <i class="fa-solid fa-chart-line display-3 opacity-25"></i>
            </div>
        </div>
    </section>

    <section class="mb-5" aria-labelledby="overview-title">
        <div class="d-flex justify-content-between align-items-end mb-3 gap-3">
            <div>
                <h2 id="overview-title" class="h4 fw-bold mb-1">Tổng quan hệ thống</h2>
                <p class="text-muted small mb-0">Số liệu tài khoản và lớp học hiện tại</p>
            </div>
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                <i class="fa-solid fa-chart-simple me-1"></i> Thống kê
            </span>
        </div>

        <div class="row g-3">
            <div class="col-6 col-xl">
                <article class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-users"></i></span>
                        <div><p class="text-muted small mb-1">Người dùng</p><p class="h3 fw-bold mb-0"><?= (int) $stats['total_users'] ?></p></div>
                    </div>
                </article>
            </div>
            <div class="col-6 col-xl">
                <article class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-user-graduate"></i></span>
                        <div><p class="text-muted small mb-1">Học sinh</p><p class="h3 fw-bold mb-0"><?= (int) $stats['students'] ?></p></div>
                    </div>
                </article>
            </div>
            <div class="col-6 col-xl">
                <article class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="stat-icon bg-info-subtle text-info"><i class="fa-solid fa-chalkboard-user"></i></span>
                        <div><p class="text-muted small mb-1">Giáo viên</p><p class="h3 fw-bold mb-0"><?= (int) $stats['teachers'] ?></p></div>
                    </div>
                </article>
            </div>
            <div class="col-6 col-xl">
                <article class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="stat-icon bg-warning-subtle text-warning"><i class="fa-solid fa-user-shield"></i></span>
                        <div><p class="text-muted small mb-1">Quản trị viên</p><p class="h3 fw-bold mb-0"><?= (int) $stats['admins'] ?></p></div>
                    </div>
                </article>
            </div>
            <div class="col-6 col-xl">
                <article class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="stat-icon bg-danger-subtle text-danger"><i class="fa-solid fa-school"></i></span>
                        <div><p class="text-muted small mb-1">Lớp học</p><p class="h3 fw-bold mb-0"><?= (int) $stats['classes'] ?></p></div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section aria-labelledby="management-title">
        <div class="mb-3">
            <h2 id="management-title" class="h4 fw-bold mb-1">Quản lý hệ thống</h2>
            <p class="text-muted small mb-0">Truy cập nhanh các khu vực quản trị</p>
        </div>
        <div class="row g-3">
            <div class="col-md-6 col-xl-4">
                <article class="card action-card h-100">
                    <div class="card-body p-4 d-flex flex-column align-items-start">
                        <span class="action-icon bg-primary-subtle text-primary mb-3"><i class="fa-solid fa-users-gear"></i></span>
                        <h3 class="h5 fw-bold">Quản lý người dùng</h3>
                        <p class="text-muted small flex-grow-1">Xem tài khoản, cập nhật vai trò và khóa hoặc mở khóa người dùng.</p>
                        <a href="index.php?action=admin_users" class="btn btn-primary rounded-3">
                            Quản lý tài khoản <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </article>
            </div>
            <div class="col-md-6 col-xl-4">
                <article class="card action-card action-card-disabled h-100">
                    <div class="card-body p-4 d-flex flex-column align-items-start">
                        <span class="action-icon bg-success-subtle text-success mb-3"><i class="fa-solid fa-school"></i></span>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <h3 class="h5 fw-bold mb-0">Quản lý lớp học</h3>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill">Sắp ra mắt</span>
                        </div>
                        <p class="text-muted small mb-0">Khu vực tạo lớp, phân công giáo viên và quản lý thành viên sẽ được bổ sung.</p>
                    </div>
                </article>
            </div>
            <div class="col-md-6 col-xl-4">
                <article class="card action-card h-100">
                    <div class="card-body p-4 d-flex flex-column align-items-start">
                        <span class="action-icon bg-info-subtle text-info mb-3"><i class="fa-solid fa-comments"></i></span>
                        <h3 class="h5 fw-bold">Diễn đàn thảo luận</h3>
                        <p class="text-muted small flex-grow-1">Xem các bài viết và bình luận trong cộng đồng EduTest.</p>
                        <a href="index.php?action=forum" class="btn btn-outline-primary rounded-3">
                            Mở diễn đàn <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </article>
            </div>
        </div>
    </section>
</main>
</body>
</html>
