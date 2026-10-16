<?php
$accountTotal = (int) ($stats['total_users'] ?? 0);
$adminMetrics = [
    ['label' => 'Tổng tài khoản', 'value' => $accountTotal, 'icon' => 'fa-users', 'note' => 'Toàn hệ thống'],
    ['label' => 'Học sinh', 'value' => (int) ($stats['students'] ?? 0), 'icon' => 'fa-user-graduate', 'note' => 'Tài khoản học sinh'],
    ['label' => 'Giáo viên', 'value' => (int) ($stats['teachers'] ?? 0), 'icon' => 'fa-chalkboard-user', 'note' => 'Tài khoản giáo viên'],
    ['label' => 'Quản trị viên', 'value' => (int) ($stats['admins'] ?? 0), 'icon' => 'fa-user-shield', 'note' => 'Tài khoản quản trị'],
    ['label' => 'Lớp học', 'value' => (int) ($stats['classes'] ?? 0), 'icon' => 'fa-school', 'note' => 'Không gian lớp học'],
];
$roleBreakdown = [
    ['label' => 'Học sinh', 'value' => (int) ($stats['students'] ?? 0)],
    ['label' => 'Giáo viên', 'value' => (int) ($stats['teachers'] ?? 0)],
    ['label' => 'Quản trị viên', 'value' => (int) ($stats['admins'] ?? 0)],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f7fc">
    <title>Tổng quan quản trị - EduTest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-app">
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a class="admin-brand" href="index.php?action=admin_dashboard">
            <span class="admin-brand-mark" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
            <span>EduTest</span>
        </a>
        <nav class="admin-nav" aria-label="Điều hướng quản trị">
            <a class="admin-nav-link active" href="index.php?action=admin_dashboard" aria-current="page"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i><span>Tổng quan</span></a>
            <a class="admin-nav-link" href="index.php?action=admin_users"><i class="fa-solid fa-users-gear" aria-hidden="true"></i><span>Tài khoản</span></a>
            <a class="admin-nav-link" href="index.php?action=forum"><i class="fa-regular fa-comments" aria-hidden="true"></i><span>Diễn đàn</span></a>
        </nav>
        <div class="admin-sidebar-bottom">
            <span class="admin-account-avatar" aria-hidden="true"><i class="fa-solid fa-user-shield"></i></span>
            <div class="admin-account-copy">
                <div class="admin-account-name"><?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="admin-account-role">Quản trị viên</div>
            </div>
            <a class="admin-logout" href="index.php?action=logout" aria-label="Đăng xuất" title="Đăng xuất"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i></a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <p class="admin-breadcrumb">EduTest <span aria-hidden="true">/</span> <strong>Tổng quan</strong></p>
            <a href="index.php?action=forum" class="admin-topbar-link"><i class="fa-regular fa-comments me-1" aria-hidden="true"></i> Diễn đàn</a>
        </header>
        <main class="admin-content">
            <div class="admin-page-heading">
                <div>
                    <p class="admin-eyebrow">Bảng điều khiển</p>
                    <h1>Tổng quan hệ thống</h1>
                    <p>Theo dõi tài khoản và hoạt động quản trị EduTest.</p>
                </div>
                <a href="index.php?action=admin_users" class="btn-admin-primary"><i class="fa-solid fa-users-gear" aria-hidden="true"></i> Quản lý tài khoản</a>
            </div>

            <section class="admin-metrics" aria-label="Chỉ số hệ thống">
                <?php foreach ($adminMetrics as $metric): ?>
                    <article class="admin-metric">
                        <div class="admin-metric-head">
                            <span class="admin-metric-label"><?= htmlspecialchars($metric['label'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="admin-metric-icon" aria-hidden="true"><i class="fa-solid <?= htmlspecialchars($metric['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                        </div>
                        <div class="admin-metric-value"><?= number_format($metric['value'], 0, ',', '.') ?></div>
                        <div class="admin-metric-note"><?= htmlspecialchars($metric['note'], ENT_QUOTES, 'UTF-8') ?></div>
                    </article>
                <?php endforeach; ?>
            </section>

            <div class="admin-dashboard-grid">
                <section class="admin-panel" aria-labelledby="role-breakdown-heading">
                    <div class="admin-panel-heading">
                        <div>
                            <h2 id="role-breakdown-heading">Cơ cấu tài khoản</h2>
                            <p>Phân bố theo vai trò hiện tại</p>
                        </div>
                        <span class="admin-metric-icon" aria-hidden="true"><i class="fa-solid fa-chart-simple"></i></span>
                    </div>
                    <div class="admin-panel-body role-breakdown">
                        <?php foreach ($roleBreakdown as $role): ?>
                            <?php $percentage = $accountTotal > 0 ? min(100, (int) round($role['value'] / $accountTotal * 100)) : 0; ?>
                            <div class="role-breakdown-row">
                                <span class="role-breakdown-label"><?= htmlspecialchars($role['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                <div class="role-track" role="meter" aria-label="<?= htmlspecialchars($role['label'], ENT_QUOTES, 'UTF-8') ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $percentage ?>">
                                    <div class="role-track-fill" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <span class="role-breakdown-value"><?= number_format($role['value'], 0, ',', '.') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="admin-panel" aria-labelledby="shortcuts-heading">
                    <div class="admin-panel-heading">
                        <div>
                            <h2 id="shortcuts-heading">Truy cập nhanh</h2>
                            <p>Các khu vực quản trị thường dùng</p>
                        </div>
                    </div>
                    <div class="admin-panel-body admin-shortcuts">
                        <a href="index.php?action=admin_users" class="admin-shortcut">
                            <span class="admin-shortcut-icon" aria-hidden="true"><i class="fa-solid fa-user-gear"></i></span>
                            <span class="admin-shortcut-copy"><span class="admin-shortcut-title d-block">Tài khoản</span><span class="admin-shortcut-note d-block">Tạo, tìm và cập nhật người dùng</span></span>
                            <i class="fa-solid fa-arrow-right text-muted" aria-hidden="true"></i>
                        </a>
                        <a href="index.php?action=forum" class="admin-shortcut">
                            <span class="admin-shortcut-icon" aria-hidden="true"><i class="fa-regular fa-comments"></i></span>
                            <span class="admin-shortcut-copy"><span class="admin-shortcut-title d-block">Diễn đàn</span><span class="admin-shortcut-note d-block">Xem thảo luận cộng đồng</span></span>
                            <i class="fa-solid fa-arrow-right text-muted" aria-hidden="true"></i>
                        </a>
                    </div>
                </section>
            </div>
        </main>
    </div>
</div>
</body>
</html>
