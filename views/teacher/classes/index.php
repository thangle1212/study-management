<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f7fc">
    <title>Lớp học của tôi - EduTest</title>
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
        <a class="admin-brand" href="index.php?action=dashboard">
            <span class="admin-brand-mark" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
            <span>EduTest</span>
        </a>
        <nav class="admin-nav" aria-label="Điều hướng chính">
            <a class="admin-nav-link" href="index.php?action=dashboard"><i class="fa-solid fa-house" aria-hidden="true"></i><span>Tổng quan</span></a>
            <a class="admin-nav-link active" href="index.php?action=teacher_classes" aria-current="page"><i class="fa-solid fa-school" aria-hidden="true"></i><span>Lớp học của tôi</span></a>
            <a class="admin-nav-link" href="index.php?action=profile"><i class="fa-regular fa-id-card" aria-hidden="true"></i><span>Hồ sơ của tôi</span></a>
            <a class="admin-nav-link" href="index.php?action=qe_banks"><i class="fa-solid fa-layer-group" aria-hidden="true"></i><span>Ngân hàng câu hỏi</span></a>
            <a class="admin-nav-link" href="index.php?action=qe_exam_create"><i class="fa-solid fa-file-circle-plus" aria-hidden="true"></i><span>Tạo đề thi</span></a>
            <a class="admin-nav-link" href="index.php?action=forum"><i class="fa-regular fa-comments" aria-hidden="true"></i><span>Diễn đàn</span></a>
        </nav>
        <div class="admin-sidebar-bottom">
            <span class="admin-account-avatar" aria-hidden="true"><i class="fa-solid fa-chalkboard-user"></i></span>
            <div class="admin-account-copy">
                <div class="admin-account-name"><?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'Giáo viên', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="admin-account-role">Giáo viên</div>
            </div>
            <a class="admin-logout" href="index.php?action=logout" aria-label="Đăng xuất" title="Đăng xuất"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i></a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <p class="admin-breadcrumb">EduTest <span aria-hidden="true">/</span> <strong>Lớp học của tôi</strong></p>
            <a href="index.php?action=forum" class="admin-topbar-link"><i class="fa-regular fa-comments me-1" aria-hidden="true"></i> Diễn đàn</a>
        </header>

        <main class="admin-content">
            <div class="admin-page-heading">
                <div>
                    <p class="admin-eyebrow">Không gian giảng dạy</p>
                    <h1>Lớp học của tôi</h1>
                    <p>Xem lớp được phân công, quản lý thành viên và giao đề thi.</p>
                </div>
                <span class="user-count-badge"><i class="fa-solid fa-school" aria-hidden="true"></i><?= count($classes) ?> lớp</span>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> class-alert" role="<?= $flash['type'] === 'success' ? 'status' : 'alert' ?>">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-2" aria-hidden="true"></i>
                    <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <section class="admin-panel p-0 overflow-hidden" aria-label="Danh sách lớp học">
                <?php if (!$classes): ?>
                    <div class="admin-empty-state p-5 text-center">
                        <span class="user-empty-icon" aria-hidden="true"><i class="fa-regular fa-folder-open"></i></span>
                        <h2>Bạn chưa được phân công lớp</h2>
                        <p class="mb-0">Liên hệ quản trị viên để được phân công lớp học.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4">Tên lớp</th>
                                    <th scope="col">Mã lớp</th>
                                    <th scope="col">Số thành viên</th>
                                    <th scope="col" class="text-end pe-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $class): ?>
                                    <?php $detailUrl = 'index.php?action=teacher_class_detail&class_id=' . (int) $class['class_id']; ?>
                                    <tr>
                                        <th scope="row" class="ps-4">
                                            <span class="d-inline-flex align-items-center gap-2">
                                                <span class="class-card-icon" aria-hidden="true"><i class="fa-solid fa-school"></i></span>
                                                <span><?= htmlspecialchars($class['class_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                            </span>
                                        </th>
                                        <td><span class="class-code"><strong><?= htmlspecialchars($class['class_code'], ENT_QUOTES, 'UTF-8') ?></strong></span></td>
                                        <td><span class="user-count-badge"><i class="fa-solid fa-user-graduate" aria-hidden="true"></i><?= (int) $class['student_count'] ?> học sinh</span></td>
                                        <td class="text-end pe-4">
                                            <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                                <a class="btn-admin-outline" href="<?= htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') ?>#members">
                                                    <i class="fa-solid fa-users" aria-hidden="true"></i> Quản lý thành viên
                                                </a>
                                                <a class="btn-admin-primary" href="<?= htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') ?>#assign-exam">
                                                    <i class="fa-solid fa-file-circle-check" aria-hidden="true"></i> Giao đề thi
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>
</body>
</html>
