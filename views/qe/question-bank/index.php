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
</head>
<body class="bg-light">
<main class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Ngân hàng câu hỏi</h1>
            <p class="text-muted mb-0">Tạo và quản lý các bộ câu hỏi dùng khi tạo đề thi.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="index.php?action=dashboard">Bảng điều khiển</a>
            <a class="btn btn-primary" href="index.php?action=qe_bank_create">Tạo ngân hàng</a>
        </div>
    </div>
    <?php if (isset($successMessages[$success])): ?>
        <div class="alert alert-success" role="status"><?= $escape($successMessages[$success]) ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="alert alert-danger" role="alert"><?= $escape($error) ?></div>
    <?php endif; ?>
    <?php if (!$banks): ?>
        <div class="card card-body text-center py-5">
            <h2 class="h5">Bạn chưa có ngân hàng câu hỏi</h2>
            <p class="text-muted">Tạo ngân hàng trước, sau đó thêm câu hỏi và chọn ngân hàng này khi tạo đề thi.</p>
            <a class="btn btn-primary align-self-center" href="index.php?action=qe_bank_create">Tạo ngân hàng câu hỏi</a>
        </div>
    <?php else: ?>
        <div class="table-responsive card shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Tên ngân hàng</th><th>Môn học</th><th>Số câu</th><th>Ngày tạo</th><th class="text-end">Thao tác</th></tr>
                </thead>
                <tbody>
                <?php foreach ($banks as $bank): ?>
                    <tr>
                        <td class="fw-semibold"><?= $escape($bank['bank_name']) ?></td>
                        <td><?= $escape($bank['subject'] ?: '—') ?></td>
                        <td><?= (int) $bank['question_count'] ?></td>
                        <td><?= $escape($bank['created_at']) ?></td>
                        <td>
                            <div class="d-flex justify-content-end flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="index.php?action=qe_bank_show&id=<?= (int) $bank['bank_id'] ?>">Mở</a>
                                <a class="btn btn-sm btn-outline-secondary" href="index.php?action=qe_bank_edit&id=<?= (int) $bank['bank_id'] ?>">Sửa</a>
                                <form method="post" action="index.php?action=qe_bank_delete&id=<?= (int) $bank['bank_id'] ?>" onsubmit="return confirm('Xóa ngân hàng và toàn bộ câu hỏi chưa được dùng trong đề thi?')">
                                    <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Xóa</button>
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
</body>
</html>
