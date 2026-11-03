<?php
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    header('Location: ../../../index.php?action=qe_banks');
    exit;
}
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$successMessages = [
    'bank_deleted' => 'Đã xóa ngân hàng câu hỏi.',
];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ngân hàng câu hỏi - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="views/qe/qe.css">
    <link rel="stylesheet" href="assets/css/edutest.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-app">
<?php $appPageTitle = 'Ngân hàng câu hỏi'; $appActiveAction = 'qe_banks'; require __DIR__ . '/../../partials/app-shell-start.php'; ?>
<main class="container py-4">
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow"><i class="fa-solid fa-layer-group me-1" aria-hidden="true"></i>Kho nội dung</p>
            <h1>Ngân hàng câu hỏi</h1>
            <p class="text-muted mb-0">Tạo và quản lý các bộ câu hỏi dùng khi tạo đề thi.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn-admin-outline" href="index.php?action=qe_exam_create"><i class="fa-solid fa-file-circle-plus" aria-hidden="true"></i>Tạo đề thi</a>
            <a class="btn-admin-primary" href="index.php?action=qe_bank_create"><i class="fa-solid fa-plus" aria-hidden="true"></i>Tạo ngân hàng</a>
        </div>
    </div>
    <?php if (isset($successMessages[$success])): ?>
        <div class="alert alert-success" role="status"><?= $escape($successMessages[$success]) ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="alert alert-danger" role="alert"><?= $escape($error) ?></div>
    <?php endif; ?>
    <?php if (!$banks): ?>
        <div class="content-card text-center py-5">
            <span class="admin-empty-icon" aria-hidden="true"><i class="fa-regular fa-folder-open"></i></span>
            <h2 class="h5">Bạn chưa có ngân hàng câu hỏi</h2>
            <p class="text-muted">Tạo ngân hàng trước, sau đó thêm câu hỏi và chọn ngân hàng này khi tạo đề thi.</p>
            <a class="btn btn-primary align-self-center" href="index.php?action=qe_bank_create">Tạo ngân hàng câu hỏi</a>
        </div>
    <?php else: ?>
        <div class="table-responsive content-card p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th scope="col">Tên ngân hàng</th><th scope="col">Môn học</th><th scope="col">Số câu</th><th scope="col">Ngày tạo</th><th scope="col" class="text-end">Thao tác</th></tr>
                </thead>
                <tbody>
                <?php foreach ($banks as $bank): ?>
                    <tr>
                        <th scope="row" class="fw-semibold"><i class="fa-solid fa-folder-open text-primary me-2" aria-hidden="true"></i><?= $escape($bank['bank_name']) ?></th>
                        <td><?= $escape($bank['subject'] ?: 'Chưa phân loại') ?></td>
                        <td><span class="question-count-badge"><i class="fa-regular fa-circle-question" aria-hidden="true"></i><?= (int) $bank['question_count'] ?> câu</span></td>
                        <td class="text-nowrap"><?= $escape($bank['created_at']) ?></td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end flex-wrap gap-2">
                                <a class="btn-admin-outline btn-admin-compact" href="index.php?action=qe_bank_show&id=<?= (int) $bank['bank_id'] ?>"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>Mở</a>
                                <a class="btn-admin-outline btn-admin-compact" href="index.php?action=qe_bank_edit&id=<?= (int) $bank['bank_id'] ?>"><i class="fa-solid fa-pen" aria-hidden="true"></i>Sửa</a>
                                <form method="post" action="index.php?action=qe_bank_delete&id=<?= (int) $bank['bank_id'] ?>" onsubmit="return confirm('Xóa ngân hàng và toàn bộ câu hỏi chưa được dùng trong đề thi?')">
                                    <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                                    <button class="btn-admin-danger btn-admin-compact" type="submit"><i class="fa-solid fa-trash-can" aria-hidden="true"></i>Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../../partials/app-shell-end.php'; ?>
</body>
</html>
