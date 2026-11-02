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
<?php $appPageTitle = 'Lớp học của tôi'; $appActiveAction = 'teacher_classes'; require __DIR__ . '/../../partials/app-shell-start.php'; ?>
<main>
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
                        <span class="user-empty-icon" aria-hidden="true"><i class="fa-solid fa-folder-open"></i></span>
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
<?php require __DIR__ . '/../../partials/app-shell-end.php'; ?>
</body>
</html>
