<?php
$currentUserId = (int) ($_SESSION['user']['id'] ?? 0);
$selectedRole = $_GET['role'] ?? '';
$roleLabels = [
    'student' => 'Học sinh',
    'teacher' => 'Giáo viên',
    'admin' => 'Quản trị viên'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f7fc">
    <title>Quản lý người dùng - EduTest</title>
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
            <a class="admin-nav-link" href="index.php?action=admin_dashboard"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i><span>Tổng quan</span></a>
            <a class="admin-nav-link active" href="index.php?action=admin_users" aria-current="page"><i class="fa-solid fa-users-gear" aria-hidden="true"></i><span>Tài khoản</span></a>
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
        <p class="admin-breadcrumb">EduTest <span aria-hidden="true">/</span> <strong>Tài khoản</strong></p>
        <a href="index.php?action=forum" class="admin-topbar-link"><i class="fa-regular fa-comments me-1" aria-hidden="true"></i> Diễn đàn</a>
    </header>

    <main class="admin-content">
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow">Quản trị hệ thống</p>
            <h1>Quản lý tài khoản</h1>
            <p>Tìm tài khoản, cập nhật quyền truy cập và trạng thái.</p>
        </div>
        <a href="index.php?action=admin_dashboard" class="btn-admin-outline">
            <i class="fa-solid fa-chart-pie" aria-hidden="true"></i><span>Tổng quan</span>
        </a>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger admin-alert" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2" aria-hidden="true"></i>
            <?php if ($_GET['error'] === 'self_action_forbidden'): ?>
                Bạn không thể thay đổi trạng thái hoặc vai trò của chính tài khoản đang đăng nhập.
            <?php elseif ($_GET['error'] === 'email_exists'): ?>
                Email này đã được sử dụng bởi tài khoản khác.
            <?php elseif ($_GET['error'] === 'invalid_profile'): ?>
                Email không hợp lệ hoặc mật khẩu mới có ít hơn 6 ký tự.
            <?php elseif ($_GET['error'] === 'invalid_create'): ?>
                Thông tin tạo tài khoản không hợp lệ. Hãy kiểm tra email, vai trò và mật khẩu tối thiểu 6 ký tự.
            <?php else: ?>
                Không thể thực hiện thao tác. Vui lòng kiểm tra lại thông tin và thử lại.
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success admin-alert" role="status">
            <i class="fa-solid fa-circle-check me-2" aria-hidden="true"></i>
            <?= $_GET['success'] === 'created' ? 'Tạo tài khoản thành công.' : 'Cập nhật tài khoản thành công.' ?>
        </div>
    <?php endif; ?>

    <details class="admin-panel admin-create-details">
        <summary class="admin-create-summary">
            <span class="admin-create-icon" aria-hidden="true"><i class="fa-solid fa-user-plus"></i></span>
            <span>Tạo tài khoản mới</span>
        </summary>
        <div class="admin-create-form">
            <form action="index.php?action=admin_create_user" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="col-md-6">
                    <label for="new-full-name" class="form-label">Họ và tên</label>
                    <input id="new-full-name" name="full_name" type="text" class="form-control" maxlength="100" required>
                </div>
                <div class="col-md-6">
                    <label for="new-email" class="form-label">Email</label>
                    <input id="new-email" name="email" type="email" class="form-control" maxlength="100" required>
                </div>
                <div class="col-md-6">
                    <label for="new-password" class="form-label">Mật khẩu khởi tạo</label>
                    <input id="new-password" name="password" type="password" class="form-control" minlength="6" autocomplete="new-password" required>
                    <div class="form-text">Tối thiểu 6 ký tự.</div>
                </div>
                <div class="col-md-6">
                    <label for="new-role" class="form-label">Phân quyền</label>
                    <select id="new-role" name="role" class="form-select" required>
                        <?php foreach ($roleLabels as $role => $label): ?>
                            <option value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="new-school" class="form-label">Trường học <span class="text-muted">(không bắt buộc)</span></label>
                    <input id="new-school" name="school_name" type="text" class="form-control" maxlength="150">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn-admin-primary">
                        <i class="fa-solid fa-user-plus me-1"></i>Tạo tài khoản
                    </button>
                </div>
            </form>
        </div>
    </details>

    <section class="admin-panel admin-list-panel" aria-labelledby="accounts-heading">
        <div class="admin-list-toolbar">
            <div class="admin-list-title">
                <span class="admin-create-icon" aria-hidden="true"><i class="fa-solid fa-users-gear"></i></span>
                <div>
                    <h2 id="accounts-heading">Danh sách tài khoản</h2>
                    <p class="admin-list-count" id="users-count" aria-live="polite"><?= count($users) ?> tài khoản<?= $selectedRole && isset($roleLabels[$selectedRole]) ? ' · ' . htmlspecialchars($roleLabels[$selectedRole], ENT_QUOTES, 'UTF-8') : '' ?></p>
                </div>
            </div>
            <div class="admin-list-controls">
                <label class="visually-hidden" for="user-search">Tìm theo tên hoặc email</label>
                <div class="input-group admin-search">
                    <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
                    <input type="search" id="user-search" class="form-control" placeholder="Tìm tên hoặc email" autocomplete="off">
                </div>
                <form action="index.php" method="GET" class="m-0">
                    <input type="hidden" name="action" value="admin_users">
                    <label class="visually-hidden" for="role-filter">Lọc theo vai trò</label>
                    <select id="role-filter" name="role" class="form-select admin-filter" onchange="this.form.submit()">
                        <option value="">Tất cả vai trò</option>
                        <?php foreach ($roleLabels as $role => $label): ?>
                            <option value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedRole === $role ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <div class="admin-table-wrap" role="region" aria-label="Danh sách tài khoản, cuộn ngang để xem đầy đủ cột" tabindex="0">
            <table class="table admin-table align-middle">
                <caption class="visually-hidden">Tài khoản EduTest, vai trò, trạng thái và thao tác quản lý</caption>
                <thead class="bg-light">
                    <tr>
                        <th scope="col" class="ps-3 ps-md-4">Người dùng</th>
                        <th scope="col">Vai trò</th>
                        <th scope="col">Trạng thái</th>
                        <th scope="col">Ngày tạo</th>
                        <th scope="col" class="text-end pe-3 pe-md-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="user-table-body">
                    <?php foreach ($users as $user): ?>
                        <?php
                        $userId = (int) $user['user_id'];
                        $userName = htmlspecialchars($user['full_name'] ?? '', ENT_QUOTES, 'UTF-8');
                        $userEmail = htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8');
                        $userRole = $user['role'] ?? 'student';
                        $userStatus = $user['status'] ?? 'active';
                        $isCurrentUser = $userId === $currentUserId;
                        ?>
                        <tr class="user-row" data-search="<?= htmlspecialchars(($user['full_name'] ?? '') . ' ' . ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <td class="ps-3 ps-md-4">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="account-avatar" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
                                    <div>
                                        <div class="fw-semibold"><?= $userName ?></div>
                                        <div class="account-meta"><?= $userEmail ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <form action="index.php?action=admin_update_user" method="POST" class="m-0">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="user_id" value="<?= $userId ?>">
                                    <input type="hidden" name="action_type" value="role">
                                    <label class="visually-hidden" for="role-user-<?= $userId ?>">Vai trò của <?= $userName ?></label>
                                    <select id="role-user-<?= $userId ?>" name="value" class="form-select form-select-sm" aria-label="Vai trò của <?= $userName ?>" onchange="this.form.submit()" <?= $isCurrentUser ? 'disabled' : '' ?>>
                                        <?php foreach ($roleLabels as $role => $label): ?>
                                            <option value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>" <?= $userRole === $role ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <?php if ($userStatus === 'active'): ?>
                                    <span class="admin-status active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Hoạt động</span>
                                <?php else: ?>
                                    <span class="admin-status locked"><i class="fa-solid fa-lock" aria-hidden="true"></i>Đã khóa</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap text-muted small">
                                <?= !empty($user['created_at']) ? htmlspecialchars(date('d/m/Y', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8') : '—' ?>
                            </td>
                            <td class="text-end pe-3 pe-md-4 table-actions">
                                <div class="d-flex flex-column align-items-end gap-2">
                                    <details class="admin-edit-details text-start">
                                        <summary class="btn btn-sm btn-admin-outline text-nowrap">
                                            <i class="fa-solid fa-pen-to-square me-1"></i>Sửa email / mật khẩu
                                        </summary>
                                        <form action="index.php?action=admin_update_user" method="POST" class="admin-edit-form mt-2">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="user_id" value="<?= $userId ?>">
                                            <input type="hidden" name="action_type" value="profile">
                                            <div class="mb-2">
                                                <label for="email-user-<?= $userId ?>" class="form-label small">Email</label>
                                                <input id="email-user-<?= $userId ?>" name="email" type="email" class="form-control form-control-sm" value="<?= $userEmail ?>" maxlength="100" required>
                                            </div>
                                            <div class="mb-2">
                                                <label for="password-user-<?= $userId ?>" class="form-label small">Đặt mật khẩu mới</label>
                                                <input id="password-user-<?= $userId ?>" name="new_password" type="password" class="form-control form-control-sm" minlength="6" autocomplete="new-password" placeholder="Để trống nếu không đổi">
                                                <div class="form-text">Mật khẩu cũ không thể xem; để trống để giữ nguyên.</div>
                                            </div>
                                            <button type="submit" class="btn-admin-primary">Lưu thay đổi</button>
                                        </form>
                                    </details>
                                    <?php if (!$isCurrentUser): ?>
                                        <form action="index.php?action=admin_update_user" method="POST" class="m-0">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="user_id" value="<?= $userId ?>">
                                        <input type="hidden" name="action_type" value="status">
                                        <?php if ($userStatus === 'active'): ?>
                                            <input type="hidden" name="value" value="locked">
                                            <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap" onclick="return confirm('Bạn có chắc muốn khóa tài khoản này?')">
                                                <i class="fa-solid fa-lock me-1"></i>Khóa
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="value" value="active">
                                            <button type="submit" class="btn btn-sm btn-outline-success text-nowrap">
                                                <i class="fa-solid fa-lock-open me-1"></i>Mở khóa
                                            </button>
                                        <?php endif; ?>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small"><i class="fa-solid fa-user-shield me-1"></i>Bạn</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="no-users-row" <?= $users ? 'class="d-none"' : '' ?>>
                        <td colspan="5" class="admin-empty-state">
                            <span class="account-avatar"><i class="fa-regular fa-folder-open" aria-hidden="true"></i></span>
                            <p class="fw-semibold mb-1"><?= $users ? 'Không tìm thấy người dùng phù hợp.' : 'Chưa có tài khoản nào.' ?></p>
                            <p class="text-muted small mb-0"><?= $users ? 'Thử từ khóa khác hoặc đổi bộ lọc vai trò.' : 'Tài khoản sẽ xuất hiện ở đây sau khi được tạo.' ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</main>
    </div>
</div>

<script>
    const searchInput = document.getElementById('user-search');
    const userRows = Array.from(document.querySelectorAll('.user-row'));
    const noUsersRow = document.getElementById('no-users-row');
    const usersCount = document.getElementById('users-count');
    const totalUsers = userRows.length;

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim().toLocaleLowerCase('vi');
        let visibleCount = 0;

        userRows.forEach((row) => {
            const matches = row.dataset.search.includes(query);
            row.classList.toggle('d-none', !matches);
            if (matches) visibleCount++;
        });

        noUsersRow.classList.toggle('d-none', visibleCount > 0);
        usersCount.textContent = `${visibleCount} / ${totalUsers} tài khoản`;
    });
</script>
</body>
</html>
