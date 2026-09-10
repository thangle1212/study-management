<?php
$startedAt = strtotime($attempt['start_time']);
$deadline = $startedAt + ((int) $exam['duration_minutes'] * 60);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($exam['title']); ?> - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-primary px-4">
    <span class="navbar-brand fw-bold">EduTest</span>
    <span class="badge bg-light text-primary fs-6" id="countdown">--:--</span>
</nav>
<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><?php echo htmlspecialchars($exam['title']); ?></h1>
            <p class="text-muted mb-0">Các sự kiện rời tab hoặc mất tập trung sẽ được ghi nhận.</p>
        </div>
    </div>
    <form id="exam-form" action="index.php?action=submit_exam" method="POST">
        <input type="hidden" name="attempt_id" value="<?php echo (int) $attempt['attempt_id']; ?>">
        <input type="hidden" name="exam_id" value="<?php echo (int) $exam['exam_id']; ?>">
        <?php foreach ($questions as $index => $question): ?>
            <section class="card shadow-sm mb-3">
                <div class="card-body">
                    <h2 class="h5">Câu <?php echo $index + 1; ?>. <?php echo htmlspecialchars($question['content']); ?></h2>
                    <?php if (!empty($question['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($question['image_url']); ?>" class="img-fluid mb-3" alt="Hình câu hỏi">
                    <?php endif; ?>
                    <?php foreach ($question['answers'] as $answer): ?>
                        <?php $inputType = $question['question_type'] === 'multiple_choice' ? 'checkbox' : 'radio'; ?>
                        <label class="d-block border rounded p-2 mb-2">
                            <input type="<?php echo $inputType; ?>" name="answers[<?php echo (int) $question['question_id']; ?>][]" value="<?php echo (int) $answer['answer_id']; ?>" class="form-check-input me-2">
                            <?php echo htmlspecialchars($answer['content']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-success btn-lg">Nộp bài</button>
    </form>
</main>
<script>
(function () {
    const deadline = <?php echo $deadline * 1000; ?>;
    const form = document.getElementById('exam-form');
    const countdown = document.getElementById('countdown');
    let submitted = false;

    function submitExam() {
        if (!submitted) {
            submitted = true;
            form.submit();
        }
    }

    function updateCountdown() {
        const remaining = Math.max(0, deadline - Date.now());
        const seconds = Math.floor(remaining / 1000);
        countdown.textContent = String(Math.floor(seconds / 60)).padStart(2, '0') + ':' + String(seconds % 60).padStart(2, '0');
        if (remaining <= 0) submitExam();
    }

    function report(type, details) {
        const data = new URLSearchParams({
            attempt_id: '<?php echo (int) $attempt['attempt_id']; ?>',
            violation_type: type,
            details: details || ''
        });
        navigator.sendBeacon('index.php?action=log_violation', data);
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) report('tab_switch', 'visibilitychange');
    });
    window.addEventListener('blur', function () {
        report('window_blur', 'window lost focus');
    });
    document.addEventListener('contextmenu', function (event) {
        event.preventDefault();
        report('context_menu', 'context menu blocked');
    });
    window.setInterval(updateCountdown, 1000);
    updateCountdown();
}());
</script>
</body>
</html>
