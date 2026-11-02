<?php
$appShellRole = $_SESSION['user']['role'] ?? 'student';
$appShellName = $_SESSION['user']['full_name'] ?? 'Người dùng';
$appShellRoleLabels = ['student' => 'Học sinh', 'teacher' => 'Giáo viên', 'admin' => 'Quản trị viên'];
$appShellRoleIcons = ['student' => 'fa-user-graduate', 'teacher' => 'fa-chalkboard-user', 'admin' => 'fa-user-shield'];
$appShellNavigation = [
    'student' => [
        ['key' => 'dashboard', 'label' => 'Tổng quan', 'icon' => 'fa-house', 'href' => 'index.php?action=dashboard'],
        ['key' => 'profile', 'label' => 'Hồ sơ của tôi', 'icon' => 'fa-id-card', 'href' => 'index.php?action=profile'],
        ['key' => 'student_statistics', 'label' => 'Thống kê của tôi', 'icon' => 'fa-chart-line', 'href' => 'index.php?action=student_statistics'],
        ['key' => 'forum', 'label' => 'Diễn đàn', 'icon' => 'fa-comments', 'icon_style' => 'fa-regular', 'href' => 'index.php?action=forum'],
    ],
    'teacher' => [
        ['key' => 'dashboard', 'label' => 'Tổng quan', 'icon' => 'fa-house', 'href' => 'index.php?action=dashboard'],
        ['key' => 'teacher_classes', 'label' => 'Lớp học của tôi', 'icon' => 'fa-school', 'href' => 'index.php?action=teacher_classes'],
        ['key' => 'profile', 'label' => 'Hồ sơ của tôi', 'icon' => 'fa-id-card', 'href' => 'index.php?action=profile'],
        ['key' => 'qe_banks', 'label' => 'Ngân hàng câu hỏi', 'icon' => 'fa-layer-group', 'href' => 'index.php?action=qe_banks'],
        ['key' => 'qe_exam_create', 'label' => 'Tạo đề thi', 'icon' => 'fa-file-circle-plus', 'href' => 'index.php?action=qe_exam_create'],
        ['key' => 'forum', 'label' => 'Diễn đàn', 'icon' => 'fa-comments', 'icon_style' => 'fa-regular', 'href' => 'index.php?action=forum'],
    ],
    'admin' => [
        ['key' => 'dashboard', 'label' => 'Tổng quan', 'icon' => 'fa-chart-pie', 'href' => 'index.php?action=admin_dashboard'],
        ['key' => 'admin_users', 'label' => 'Tài khoản', 'icon' => 'fa-users-gear', 'href' => 'index.php?action=admin_users'],
        ['key' => 'admin_classes', 'label' => 'Lớp học', 'icon' => 'fa-school', 'href' => 'index.php?action=admin_classes'],
        ['key' => 'forum', 'label' => 'Diễn đàn', 'icon' => 'fa-comments', 'icon_style' => 'fa-regular', 'href' => 'index.php?action=forum'],
    ],
];
$appShellMenu = $appShellNavigation[$appShellRole] ?? $appShellNavigation['student'];
$appShellEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a class="admin-brand" href="index.php?action=<?= $appShellRole === 'admin' ? 'admin_dashboard' : 'dashboard' ?>">
            <span class="admin-brand-mark" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
            <span>EduTest</span>
        </a>
        <nav class="admin-nav" aria-label="Điều hướng chính">
            <?php foreach ($appShellMenu as $item): ?>
                <?php $isActive = ($appActiveAction ?? '') === $item['key']; ?>
                <a class="admin-nav-link<?= $isActive ? ' active' : '' ?>" href="<?= $appShellEscape($item['href']) ?>"<?= $isActive ? ' aria-current="page"' : '' ?>>
                    <i class="<?= $appShellEscape($item['icon_style'] ?? 'fa-solid') ?> <?= $appShellEscape($item['icon']) ?>" aria-hidden="true"></i><span><?= $appShellEscape($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-sidebar-bottom">
            <span class="admin-account-avatar" aria-hidden="true"><i class="fa-solid <?= $appShellEscape($appShellRoleIcons[$appShellRole] ?? $appShellRoleIcons['student']) ?>"></i></span>
            <div class="admin-account-copy">
                <div class="admin-account-name"><?= $appShellEscape($appShellName) ?></div>
                <div class="admin-account-role"><?= $appShellEscape($appShellRoleLabels[$appShellRole] ?? 'Thành viên') ?></div>
            </div>
            <a class="admin-logout" href="index.php?action=logout" aria-label="Đăng xuất" title="Đăng xuất"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i></a>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar<?= !empty($appTopbarHtml) ? ' admin-exam-topbar' : '' ?>">
            <p class="admin-breadcrumb">EduTest <span aria-hidden="true">/</span> <strong><?= $appShellEscape($appPageTitle ?? 'Không gian học tập') ?></strong></p>
            <div class="admin-topbar-actions">
                <?php if (!empty($appTopbarHtml)): ?>
                    <?= $appTopbarHtml ?>
                <?php else: ?>
                    <a href="index.php?action=profile" class="admin-topbar-link"><i class="fa-regular fa-id-card me-1" aria-hidden="true"></i> Hồ sơ của tôi</a>
                <?php endif; ?>
            </div>
        </header>
        <div class="admin-content">
