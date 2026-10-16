<?php
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $isEditing ? 'Sửa' : 'Tạo' ?> ngân hàng câu hỏi - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="views/qe/qe.css">
    <link rel="stylesheet" href="assets/css/edutest.css">
</head>
<body>
<main class="container py-4" style="max-width: 900px">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><?= $isEditing ? 'Chỉnh sửa ngân hàng' : 'Tạo ngân hàng câu hỏi' ?></h1>
            <p class="text-muted mb-0">Ngân hàng là nơi lưu và quản lý các câu hỏi dùng cho đề thi.</p>
        </div>
        <a class="btn btn-outline-secondary" href="index.php?action=qe_banks">Quay lại</a>
    </div>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger" role="alert"><?= $escape($error) ?></div>
    <?php endforeach; ?>
    <form method="post" action="index.php?action=<?= $isEditing ? 'qe_bank_edit&id=' . (int) $bankId : 'qe_bank_create' ?>" class="card card-body shadow-sm">
        <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
        <div class="mb-3">
            <label for="bank-name" class="form-label">Tên ngân hàng <span class="text-danger">*</span></label>
            <input id="bank-name" name="bank_name" class="form-control" maxlength="150" required value="<?= $escape($bank['bank_name'] ?? '') ?>" placeholder="Ví dụ: Toán học - Đại số">
        </div>
        <div class="mb-4">
            <label for="subject" class="form-label">Môn học</label>
            <input id="subject" name="subject" class="form-control" maxlength="100" value="<?= $escape($bank['subject'] ?? '') ?>" placeholder="Ví dụ: Toán">
            <div class="form-text">Các trường này khớp với cấu trúc database hiện tại.</div>
        </div>
        <div class="d-flex justify-content-end gap-2">
            <a class="btn btn-outline-secondary" href="index.php?action=qe_banks">Hủy</a>
            <button class="btn btn-primary" type="submit"><?= $isEditing ? 'Lưu thay đổi' : 'Tạo ngân hàng' ?></button>
        </div>
    </form>
</main>
</body>
</html>
