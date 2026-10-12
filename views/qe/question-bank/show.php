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
</head>
<body class="bg-light">
<main class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="index.php?action=qe_banks" class="text-decoration-none">&larr; Ngân hàng câu hỏi</a>
            <h1 class="h3 mt-2 mb-1"><?= $escape($bank['bank_name']) ?></h1>
            <p class="text-muted mb-0"><?= $escape($bank['subject'] ?: 'Chưa chọn môn học') ?> · <?= count($questions) ?> câu hỏi</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary" href="index.php?action=qe_bank_edit&id=<?= (int) $bank['bank_id'] ?>">Sửa ngân hàng</a>
            <a class="btn btn-outline-primary" href="index.php?action=qe_exam_create">Tạo đề thi</a>
            <a class="btn btn-primary" href="index.php?action=qe_question_create&bank_id=<?= (int) $bank['bank_id'] ?>">Thêm câu hỏi</a>
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
            <h2 class="h5">Ngân hàng chưa có câu hỏi</h2>
            <p class="text-muted">Thêm câu hỏi trước để bắt đầu xây dựng ngân hàng.</p>
            <a class="btn btn-primary align-self-center" href="index.php?action=qe_question_create&bank_id=<?= (int) $bank['bank_id'] ?>">Thêm câu hỏi đầu tiên</a>
        </div>
    <?php else: ?>
        <div class="vstack gap-3">
            <?php foreach ($questions as $index => $question): ?>
                <article class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between gap-3">
                            <div class="flex-grow-1">
                                <div class="small text-muted mb-2">
                                    Câu <?= $index + 1 ?> · <?= $escape($typeLabels[$question['question_type']] ?? $question['question_type']) ?>
                                    · Độ khó: <?= $escape($difficultyLabels[$question['difficulty_level']] ?? $question['difficulty_level']) ?>
                                </div>
                                <h2 class="h6" style="white-space: pre-wrap"><?= $escape($question['content']) ?></h2>
                                <?php if ($question['image_url']): ?>
                                    <a href="<?= $escape($question['image_url']) ?>" target="_blank" rel="noopener noreferrer">Xem hình ảnh đính kèm</a>
                                <?php endif; ?>
                                <ol type="A" class="mb-0 mt-3">
                                    <?php foreach ($question['answers'] as $answer): ?>
                                        <li class="<?= $answer['is_correct'] ? 'fw-semibold text-success' : '' ?>"><?= $escape($answer['content']) ?><?= $answer['is_correct'] ? ' — Đáp án đúng' : '' ?></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <a class="btn btn-sm btn-outline-secondary" href="index.php?action=qe_question_edit&id=<?= (int) $question['question_id'] ?>">Sửa</a>
                                <form method="post" action="index.php?action=qe_question_delete&id=<?= (int) $question['question_id'] ?>&amp;bank_id=<?= (int) $bank['bank_id'] ?>" onsubmit="return confirm('Bạn có chắc muốn xóa câu hỏi này?')">
                                    <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Xóa</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
