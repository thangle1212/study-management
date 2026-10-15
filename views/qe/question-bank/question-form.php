git<?php
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$questionTypes = [
    'single_choice' => 'Một đáp án đúng',
    'multiple_choice' => 'Nhiều đáp án đúng'
];
$difficultyLabels = ['easy' => 'Dễ', 'medium' => 'Trung bình', 'hard' => 'Khó'];
$answers = $question['answers'] ?? [];
$postedCorrect = $_POST['correct_answers'] ?? [];
$postedCorrect = is_array($postedCorrect) ? array_map('strval', $postedCorrect) : [];
foreach ($answers as $index => &$answer) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $answer['is_correct'] = in_array((string) $index, $postedCorrect, true);
    }
}
unset($answer);
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $isEditing ? 'Sửa' : 'Tạo' ?> câu hỏi - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-4" style="max-width: 1000px">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1"><?= $isEditing ? 'Chỉnh sửa câu hỏi' : 'Tạo câu hỏi' ?></h1>
            <p class="text-muted mb-0">Chỉnh sửa nội dung, đáp án đúng, loại câu hỏi và độ khó.</p>
        </div>
        <a class="btn btn-outline-secondary" href="<?= !empty($question['bank_id']) ? 'index.php?action=qe_bank_show&id=' . (int) $question['bank_id'] : 'index.php?action=qe_banks' ?>">Quay lại</a>
    </div>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger" role="alert"><?= $escape($error) ?></div>
    <?php endforeach; ?>
    <?php if (!$banks): ?>
        <div class="alert alert-warning">
            Bạn cần tạo ngân hàng câu hỏi trước.
            <a class="alert-link" href="index.php?action=qe_bank_create">Tạo ngân hàng câu hỏi</a>
        </div>
    <?php else: ?>
        <form method="post" action="index.php?action=<?= $isEditing ? 'qe_question_edit&id=' . (int) $questionId : 'qe_question_create' ?>" class="card card-body shadow-sm" id="question-form">
            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="bank-id" class="form-label">Ngân hàng câu hỏi <span class="text-danger">*</span></label>
                    <select id="bank-id" name="bank_id" class="form-select" required>
                        <option value="">Chọn ngân hàng</option>
                        <?php foreach ($banks as $bank): ?>
                            <option value="<?= (int) $bank['bank_id'] ?>" <?= (string) ($question['bank_id'] ?? '') === (string) $bank['bank_id'] ? 'selected' : '' ?>>
                                <?= $escape($bank['bank_name']) ?><?= $bank['subject'] ? ' — ' . $escape($bank['subject']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="question-type" class="form-label">Loại câu hỏi</label>
                    <select id="question-type" name="question_type" class="form-select">
                        <?php foreach ($questionTypes as $value => $label): ?>
                            <option value="<?= $escape($value) ?>" <?= ($question['question_type'] ?? 'single_choice') === $value ? 'selected' : '' ?>><?= $escape($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="difficulty" class="form-label">Độ khó</label>
                    <select id="difficulty" name="difficulty_level" class="form-select">
                        <?php foreach ($difficultyLabels as $value => $label): ?>
                            <option value="<?= $escape($value) ?>" <?= ($question['difficulty_level'] ?? 'medium') === $value ? 'selected' : '' ?>><?= $escape($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label for="content" class="form-label">Nội dung câu hỏi <span class="text-danger">*</span></label>
                    <textarea id="content" name="content" class="form-control" rows="4" maxlength="5000" required><?= $escape($question['content'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label for="image-url" class="form-label">URL hình ảnh (không bắt buộc)</label>
                    <input id="image-url" name="image_url" type="url" class="form-control" maxlength="255" value="<?= $escape($question['image_url'] ?? '') ?>" placeholder="https://...">
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h2 class="h5 mb-1">Đáp án</h2>
                    <p class="text-muted small mb-0">Chọn đáp án đúng; câu nhiều đáp án cho phép chọn nhiều.</p>
                </div>
                <button class="btn btn-sm btn-outline-primary" type="button" id="add-answer">Thêm đáp án</button>
            </div>
            <div id="answers">
                <?php foreach ($answers as $index => $answer): ?>
                    <div class="input-group mb-2 answer-row">
                        <span class="input-group-text answer-label"><?= chr(65 + $index) ?></span>
                        <input type="checkbox" class="form-check-input mt-0 align-self-center mx-3 correct-choice" name="correct_answers[]" value="<?= (int) $index ?>" aria-label="Đánh dấu đáp án đúng" <?= !empty($answer['is_correct']) ? 'checked' : '' ?>>
                        <textarea class="form-control" name="answers[]" rows="2" maxlength="2000" required aria-label="Nội dung đáp án"><?= $escape($answer['content'] ?? '') ?></textarea>
                        <button class="btn btn-outline-danger remove-answer" type="button" aria-label="Xóa đáp án">Xóa</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <a class="btn btn-outline-secondary" href="<?= !empty($question['bank_id']) ? 'index.php?action=qe_bank_show&id=' . (int) $question['bank_id'] : 'index.php?action=qe_banks' ?>">Hủy</a>
                <button class="btn btn-primary" type="submit"><?= $isEditing ? 'Lưu câu hỏi' : 'Tạo câu hỏi' ?></button>
            </div>
        </form>
    <?php endif; ?>
</main>
<script>
    const answerList = document.getElementById('answers');
    const questionType = document.getElementById('question-type');

    function updateAnswerControls() {
        if (!answerList || !questionType) return;
        const multiple = questionType.value === 'multiple_choice';
        answerList.querySelectorAll('.correct-choice').forEach((choice) => {
            choice.type = multiple ? 'checkbox' : 'radio';
            choice.name = 'correct_answers[]';
        });
        if (!multiple) {
            const selected = answerList.querySelectorAll('.correct-choice:checked');
            selected.forEach((choice, index) => {
                if (index > 0) choice.checked = false;
            });
        }
        answerList.querySelectorAll('.answer-row').forEach((row, index) => {
            row.querySelector('.answer-label').textContent = String.fromCharCode(65 + index);
            row.querySelector('.correct-choice').value = index;
        });
        document.getElementById('add-answer').disabled = answerList.querySelectorAll('.answer-row').length >= 10;
    }

    document.getElementById('add-answer')?.addEventListener('click', () => {
        const index = answerList.querySelectorAll('.answer-row').length;
        if (index >= 10) return;
        const row = document.createElement('div');
        row.className = 'input-group mb-2 answer-row';
        row.innerHTML = `<span class="input-group-text answer-label"></span>
            <input type="checkbox" class="form-check-input mt-0 align-self-center mx-3 correct-choice" name="correct_answers[]" aria-label="Đánh dấu đáp án đúng">
            <textarea class="form-control" name="answers[]" rows="2" maxlength="2000" required aria-label="Nội dung đáp án"></textarea>
            <button class="btn btn-outline-danger remove-answer" type="button" aria-label="Xóa đáp án">Xóa</button>`;
        answerList.appendChild(row);
        row.querySelector('.correct-choice').value = index;
        updateAnswerControls();
    });
    answerList?.addEventListener('click', (event) => {
        if (event.target.closest('.remove-answer') && answerList.querySelectorAll('.answer-row').length > 2) {
            event.target.closest('.answer-row').remove();
            updateAnswerControls();
        }
    });
    questionType?.addEventListener('change', updateAnswerControls);
    updateAnswerControls();
</script>
</body>
</html>
