<?php
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    header('Location: ../../../index.php?action=qe_banks');
    exit;
}
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$successMessages = [
    'bank_created' => 'Đã tạo ngân hàng câu hỏi.',
    'bank_updated' => 'Đã cập nhật ngân hàng câu hỏi.',
    'question_created' => 'Đã thêm câu hỏi vào ngân hàng.',
    'question_saved' => 'Đã lưu câu hỏi.',
    'question_deleted' => 'Đã xóa câu hỏi.',
];
$typeLabels = ['single_choice' => 'Một đáp án đúng', 'multiple_choice' => 'Nhiều đáp án đúng'];
$difficultyLabels = ['easy' => 'Dễ', 'medium' => 'Trung bình', 'hard' => 'Khó'];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($bank['bank_name']) ?> - Ngân hàng câu hỏi</title>
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
            <p class="admin-eyebrow"><a href="index.php?action=qe_banks"><i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Ngân hàng câu hỏi</a></p>
            <h1><i class="fa-solid fa-folder-open text-primary me-2" aria-hidden="true"></i><?= $escape($bank['bank_name']) ?></h1>
            <p><?= $escape($bank['subject'] ?: 'Chưa chọn môn học') ?> · <?= count($questions) ?> câu hỏi</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn-admin-outline" href="index.php?action=qe_bank_edit&id=<?= (int) $bank['bank_id'] ?>"><i class="fa-solid fa-pen" aria-hidden="true"></i>Sửa ngân hàng</a>
            <a class="btn-admin-outline" href="index.php?action=qe_exam_create&bank_id=<?= (int) $bank['bank_id'] ?>"><i class="fa-solid fa-file-circle-plus" aria-hidden="true"></i>Tạo đề thi</a>
            <a class="btn-admin-primary" href="index.php?action=qe_question_create&bank_id=<?= (int) $bank['bank_id'] ?>"><i class="fa-solid fa-plus" aria-hidden="true"></i>Thêm câu hỏi</a>
        </div>
    </div>
    <?php if (isset($successMessages[$success])): ?>
        <div class="alert alert-success" role="status"><?= $escape($successMessages[$success]) ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="alert alert-danger" role="alert"><?= $escape($error) ?></div>
    <?php endif; ?>
    <?php if (!$questions): ?>
        <div class="card card-body text-center py-5">
            <span class="admin-empty-icon" aria-hidden="true"><i class="fa-regular fa-circle-question"></i></span>
            <h2 class="h5">Ngân hàng chưa có câu hỏi</h2>
            <p class="text-muted">Thêm câu hỏi trước để bắt đầu xây dựng ngân hàng.</p>
            <a class="btn btn-primary align-self-center" href="index.php?action=qe_question_create&bank_id=<?= (int) $bank['bank_id'] ?>">Thêm câu hỏi đầu tiên</a>
        </div>
    <?php else: ?>
        <div class="vstack gap-3">
            <?php foreach ($questions as $index => $question): ?>
                <article class="card content-card question-content-card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between gap-3">
                            <div class="flex-grow-1">
                                <div class="question-meta mb-2">
                                    <span class="question-number-badge">Câu <?= $index + 1 ?></span>
                                    <span><i class="fa-solid fa-list-check me-1" aria-hidden="true"></i><?= $escape($typeLabels[$question['question_type']] ?? $question['question_type']) ?></span>
                                    <span><i class="fa-solid fa-signal me-1" aria-hidden="true"></i><?= $escape($difficultyLabels[$question['difficulty_level']] ?? $question['difficulty_level']) ?></span>
                                </div>
                                <h2 class="question-content"><?= $escape($question['content']) ?></h2>
                                <?php if ($question['image_url']): ?>
                                    <a class="question-image-link" href="<?= $escape($question['image_url']) ?>" target="_blank" rel="noopener noreferrer"><i class="fa-regular fa-image me-1" aria-hidden="true"></i>Xem hình ảnh đính kèm</a>
                                <?php endif; ?>
                                <ol type="A" class="question-answer-list mt-3">
                                    <?php foreach ($question['answers'] as $answer): ?>
                                        <li class="<?= $answer['is_correct'] ? 'fw-semibold text-success' : '' ?>"><?= $escape($answer['content']) ?><?= $answer['is_correct'] ? ' — Đáp án đúng' : '' ?></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <a class="btn-admin-outline btn-admin-compact" href="index.php?action=qe_question_edit&id=<?= (int) $question['question_id'] ?>"><i class="fa-solid fa-pen" aria-hidden="true"></i>Sửa</a>
                                <form method="post" action="index.php?action=qe_question_delete&id=<?= (int) $question['question_id'] ?>&amp;bank_id=<?= (int) $bank['bank_id'] ?>" onsubmit="return confirm('Bạn có chắc muốn xóa câu hỏi này?')">
                                    <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                                    <button class="btn-admin-danger btn-admin-compact" type="submit"><i class="fa-solid fa-trash-can" aria-hidden="true"></i>Xóa</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../../partials/app-shell-end.php'; ?>
</body>
</html>
