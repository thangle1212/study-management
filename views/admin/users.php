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
    <title>Quản lý người dùng - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --brand: #0d6efd; --ink: #17233c; }
        body { background: linear-gradient(135deg, #eaf4ff 0%, #f8fbff 48%, #fff 100%); color: var(--ink); min-height: 100vh; }
        .topbar { background: rgba(255,255,255,.9); border-bottom: 1px solid #e5edf8; backdrop-filter: blur(12px); }
        .brand { color: var(--brand); font-size: 1.35rem; }
        .page-card { border: 1px solid #e5edf8; border-radius: 18px; box-shadow: 0 8px 24px rgba(31,74,125,.06); }
        .user-avatar { width: 42px; height: 42px; display: inline-flex; flex: 0 0 42px; align-items: center; justify-content: center; border-radius: 13px; }
        .table > :not(caption) > * > * { padding: .9rem .85rem; }
        .table thead th { color: #64748b; font-size: .75rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; white-space: nowrap; }
        .table tbody tr:last-child > * { border-bottom: 0; }
        .account-meta { color: #64748b; font-size: .8rem; }
        .filter-control { min-width: 155px; }
        @media (max-width: 575.98px) {
            .table > :not(caption) > * > * { padding: .75rem .65rem; }
            .page-heading { align-items: flex-start !important; }
        }
    </style>
</head>
<body>
<nav class="navbar topbar sticky-top">
    <div class="container py-2">
        <a class="navbar-brand brand fw-bold" href="index.php?action=admin_dashboard">
            <i class="fa-solid fa-graduation-cap me-2"></i>EduTest
        </a>
        <div class="d-flex align-items-center gap-2 gap-md-3">
            <a href="index.php?action=admin_dashboard" class="btn btn-sm btn-outline-primary">
                <i class="fa-solid fa-gauge-high me-md-1"></i><span class="d-none d-md-inline">Tổng quan</span>
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
    <div class="d-flex justify-content-between page-heading gap-3 mb-4">
        <div>
            <div class="small text-primary text-uppercase fw-semibold mb-2">
                <i class="fa-solid fa-shield-halved me-1"></i> Quản trị hệ thống
            </div>
            <h1 class="h2 fw-bold mb-1">Quản lý người dùng</h1>
            <p class="text-muted mb-0">Quản lý vai trò và trạng thái tài khoản EduTest.</p>
        </div>
        <a href="index.php?action=admin_dashboard" class="btn btn-outline-primary align-self-start rounded-3 text-nowrap">
            <i class="fa-solid fa-arrow-left me-1"></i><span class="d-none d-sm-inline">Về tổng quan</span>
        </a>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger border-0 rounded-3 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
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
        <div class="alert alert-success border-0 rounded-3 shadow-sm" role="status">
            <i class="fa-solid fa-circle-check me-2"></i>
            <?= $_GET['success'] === 'created' ? 'Tạo tài khoản thành công.' : 'Cập nhật tài khoản thành công.' ?>
        </div>
    <?php endif; ?>

    <details class="card page-card mb-4">
        <summary class="card-body p-3 p-md-4 fw-semibold text-primary">
            <i class="fa-solid fa-user-plus me-2"></i>Tạo tài khoản mới
        </summary>
        <div class="card-body border-top p-3 p-md-4">
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
                    <button type="submit" class="btn btn-primary rounded-3">
                        <i class="fa-solid fa-user-plus me-1"></i>Tạo tài khoản
                    </button>
                </div>
            </form>
        </div>
    </details>

    <section class="card page-card mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="user-avatar bg-primary-subtle text-primary"><i class="fa-solid fa-users-gear"></i></span>
                    <div>
                        <h2 class="h5 fw-bold mb-1">Danh sách tài khoản</h2>
                        <p class="text-muted small mb-0"><?= count($users) ?> người dùng<?= $selectedRole && isset($roleLabels[$selectedRole]) ? ' · ' . htmlspecialchars($roleLabels[$selectedRole], ENT_QUOTES, 'UTF-8') : '' ?></p>
                    </div>
                </div>
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <label class="visually-hidden" for="user-search">Tìm theo tên hoặc email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="search" id="user-search" class="form-control" placeholder="Tìm tên hoặc email">
                    </div>
                    <form action="index.php" method="GET" class="d-flex">
                        <input type="hidden" name="action" value="admin_users">
                        <label class="visually-hidden" for="role-filter">Lọc theo vai trò</label>
                        <select id="role-filter" name="role" class="form-select filter-control" onchange="this.form.submit()">
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
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
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
                                    <span class="user-avatar bg-primary-subtle text-primary"><i class="fa-solid fa-user"></i></span>
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
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2"><i class="fa-solid fa-circle-check me-1"></i>Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2"><i class="fa-solid fa-lock me-1"></i>Đã khóa</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap text-muted small">
                                <?= !empty($user['created_at']) ? htmlspecialchars(date('d/m/Y', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8') : '—' ?>
                            </td>
                            <td class="text-end pe-3 pe-md-4">
                                <div class="d-flex flex-column align-items-end gap-2">
                                    <details class="text-start">
                                        <summary class="btn btn-sm btn-outline-primary text-nowrap">
                                            <i class="fa-solid fa-pen-to-square me-1"></i>Sửa email / mật khẩu
                                        </summary>
                                        <form action="index.php?action=admin_update_user" method="POST" class="mt-2 p-3 border rounded-3 bg-white" style="min-width: 250px">
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
                                            <button type="submit" class="btn btn-sm btn-primary">Lưu thay đổi</button>
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
                        <td colspan="5" class="text-center py-5">
                            <span class="user-avatar bg-primary-subtle text-primary mx-auto mb-3"><i class="fa-regular fa-folder-open"></i></span>
                            <p class="fw-semibold mb-1"><?= $users ? 'Không tìm thấy người dùng phù hợp.' : 'Chưa có tài khoản nào.' ?></p>
                            <p class="text-muted small mb-0"><?= $users ? 'Thử từ khóa khác hoặc đổi bộ lọc vai trò.' : 'Tài khoản sẽ xuất hiện ở đây sau khi được tạo.' ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
    const searchInput = document.getElementById('user-search');
    const userRows = Array.from(document.querySelectorAll('.user-row'));
    const noUsersRow = document.getElementById('no-users-row');

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim().toLocaleLowerCase('vi');
        let visibleCount = 0;

        userRows.forEach((row) => {
            const matches = row.dataset.search.includes(query);
            row.classList.toggle('d-none', !matches);
            if (matches) visibleCount++;
        });

        noUsersRow.classList.toggle('d-none', visibleCount > 0);
    });
</script>
</body>
</html>
