<?php
$deadline = strtotime($exam['start_time']) + ((int) $exam['duration_minutes'] * 60);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Làm bài - <?php echo htmlspecialchars($exam['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --brand: #0d6efd; --ink: #17233c; --soft: #f4f8ff; }
        body { background: linear-gradient(135deg, #eaf4ff 0%, #f8fbff 45%, #fff 100%); color: var(--ink); }
        .topbar { background: rgba(255,255,255,.9); border-bottom: 1px solid #e5edf8; backdrop-filter: blur(12px); }
        .brand { color: var(--brand); font-size: 1.35rem; }
        .exam-shell { max-width: 980px; }
        .exam-header { border: 0; border-radius: 22px; background: linear-gradient(135deg, #0d6efd, #438df4); color: #fff; box-shadow: 0 16px 36px rgba(13,110,253,.18); }
        .question-card { border: 1px solid #e5edf8; border-radius: 16px; box-shadow: 0 8px 24px rgba(31,74,125,.06); transition: transform .2s ease, box-shadow .2s ease; }
        .question-card:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(31,74,125,.1); }
        .answer-option { cursor: pointer; background: #fff; border: 1px solid #e2eaf5 !important; transition: .2s ease; }
        .answer-option:hover { border-color: var(--brand) !important; background: #f5f9ff; }
        .answer-option input { accent-color: var(--brand); }
        .submit-bar { position: sticky; bottom: 18px; z-index: 5; }
    </style>
</head>
<body>
<nav class="navbar topbar sticky-top">
    <div class="container exam-shell d-flex flex-wrap align-items-center justify-content-between gap-2">
        <a class="navbar-brand brand fw-bold" href="index.php?action=dashboard"><i class="fa-solid fa-graduation-cap me-2"></i>EduTest</a>
        <span class="fw-semibold text-primary text-nowrap" aria-live="polite">
            <i class="fa-regular fa-clock me-1"></i>Còn lại <span id="exam-countdown">--:--</span>
        </span>
        <span id="autosave-status" class="small text-muted text-nowrap" aria-live="polite">Đang kết nối lưu bài</span>
        <span id="violation-count" class="badge bg-warning-subtle text-warning-emphasis text-nowrap">
            <?php echo (int) $exam['anti_cheat_enabled'] ? 'Vi phạm ' . $violationCount . '/' . (int) $exam['violation_limit'] : 'Giám sát tắt'; ?>
        </span>
        <a href="index.php?action=dashboard" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-left me-1"></i>Trang tổng quan</a>
    </div>
</nav>
<div class="container exam-shell py-4 py-md-5">
    <div class="exam-header p-4 p-md-5 mb-4">
        <div class="small opacity-75 mb-2"><i class="fa-solid fa-pen-to-square me-1"></i> BÀI KIỂM TRA TRỰC TUYẾN</div>
        <h1 class="h2 fw-bold mb-3"><?php echo htmlspecialchars($exam['title']); ?></h1>
        <div class="d-flex flex-wrap gap-3 small">
            <span><i class="fa-solid fa-list-ol me-1"></i><?php echo (int) $exam['question_count']; ?> câu hỏi</span>
            <span><i class="fa-regular fa-clock me-1"></i><?php echo (int) $exam['duration_minutes']; ?> phút</span>
        </div>
    </div>

    <div id="violation-alert" class="alert alert-warning d-none" role="alert" aria-live="polite"></div>
    <form id="exam-form" method="post" action="index.php?action=submit_exam">
        <input type="hidden" name="exam_id" value="<?php echo (int) $exam['exam_id']; ?>">
        <input type="hidden" name="attempt_id" value="<?php echo (int) $attemptId; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <?php $number = 1; foreach ($questions as $question): ?>
            <section class="card question-card mb-3">
                <div class="card-body">
                    <div class="d-flex gap-3 align-items-start">
                        <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">Câu <?php echo $number++; ?></span>
                        <h5 class="fw-bold mb-0 lh-base"><?php echo htmlspecialchars($question['content']); ?></h5>
                    </div>
                    <?php foreach ($question['answers'] as $answer): ?>
                        <label class="answer-option d-block rounded-3 p-3 mt-3">
                            <input type="<?php echo $question['question_type'] === 'multiple_choice' ? 'checkbox' : 'radio'; ?>" name="answers[<?php echo (int) $question['question_id']; ?>]<?php echo $question['question_type'] === 'multiple_choice' ? '[]' : ''; ?>" value="<?php echo (int) $answer['answer_id']; ?>" class="me-2" <?php echo in_array((int) $answer['answer_id'], $savedAnswers[$question['question_id']] ?? [], true) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($answer['content']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
        <div class="submit-bar text-end">
            <button type="submit" class="btn btn-primary btn-lg rounded-3 px-4 shadow"><i class="fa-solid fa-paper-plane me-2"></i>Nộp bài</button>
        </div>
    </form>
</div>
<script>
(function () {
    const deadline = <?php echo json_encode($deadline * 1000); ?>;
    const attemptId = <?php echo json_encode($attemptId); ?>;
    const csrfToken = <?php echo json_encode($csrfToken); ?>;
    const antiCheatEnabled = <?php echo json_encode((bool) $exam['anti_cheat_enabled']); ?>;
    const violationLimit = <?php echo json_encode((int) $exam['violation_limit']); ?>;
    let violationCount = <?php echo json_encode($violationCount); ?>;
    const form = document.getElementById('exam-form');
    const countdown = document.getElementById('exam-countdown');
    const saveStatus = document.getElementById('autosave-status');
    const violationAlert = document.getElementById('violation-alert');
    let isSubmitting = false;
    let lastViolationAt = 0;
    let autosaveTimeout = 0;

    async function saveAnswers(force) {
        if (isSubmitting && !force) return false;
        saveStatus.textContent = 'Đang lưu...';
        const data = new URLSearchParams(new FormData(form));
        try {
            const response = await fetch('index.php?action=autosave_answers', {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                keepalive: true
            });
            const result = await response.json();
            if (result.forced_submit) {
                window.location.href = 'index.php?action=exam_result&attempt_id=' + attemptId;
                return null;
            }
            if (!response.ok) throw new Error(result.error || 'Không thể lưu bài.');
            saveStatus.textContent = 'Đã lưu lúc ' + new Date().toLocaleTimeString('vi-VN');
            return true;
        } catch (error) {
            saveStatus.textContent = 'Chưa lưu được, đang giữ đáp án trên trang';
            return false;
        }
    }

    async function submitExam() {
        if (isSubmitting) return;
        isSubmitting = true;
        window.clearTimeout(autosaveTimeout);
        const saveResult = await saveAnswers(true);
        if (saveResult !== null) form.submit();
    }

    function queueAutosave() {
        window.clearTimeout(autosaveTimeout);
        autosaveTimeout = window.setTimeout(function () { saveAnswers(false); }, 500);
    }

    async function reportViolation(type, details) {
        if (!antiCheatEnabled) return;
        const now = Date.now();
        if (now - lastViolationAt < 1200) return;
        lastViolationAt = now;
        const data = new URLSearchParams({
            csrf_token: csrfToken,
            attempt_id: String(attemptId),
            violation_type: type,
            details: details
        });
        try {
            const response = await fetch('index.php?action=log_violation', {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                keepalive: true
            });
            const result = await response.json();
            if (!response.ok) return;
            violationCount = result.count || 0;
            document.getElementById('violation-count').textContent = 'Vi phạm ' + violationCount + '/' + (result.limit || violationLimit);
            violationAlert.classList.remove('d-none', 'alert-warning', 'alert-danger');
            const message = 'Cảnh báo ' + violationCount + '/' + (result.limit || violationLimit) + ': ' + details;
            if (result.forced_submit) {
                violationAlert.classList.add('alert-danger');
                violationAlert.textContent = message + ' Bài thi đã được tự động nộp.';
                isSubmitting = true;
                window.location.href = 'index.php?action=exam_result&attempt_id=' + attemptId;
                return;
            }
            violationAlert.classList.add('alert-warning');
            violationAlert.textContent = message;
        } catch (error) {
            violationAlert.classList.remove('d-none');
            violationAlert.classList.add('alert-warning');
            violationAlert.textContent = 'Không gửi được log vi phạm. Hãy kiểm tra kết nối mạng.';
        }
    }

    function updateCountdown() {
        const remaining = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
        const minutes = String(Math.floor(remaining / 60)).padStart(2, '0');
        const seconds = String(remaining % 60).padStart(2, '0');
        countdown.textContent = minutes + ':' + seconds;
        countdown.classList.toggle('text-danger', remaining <= 60);

        if (remaining === 0) {
            submitExam();
        }
    }

    form.addEventListener('change', queueAutosave);
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        submitExam();
    });
    window.setInterval(function () { saveAnswers(false); }, 5000);

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            reportViolation('tab_hidden', 'Rời tab hoặc thu nhỏ trình duyệt');
        }
    });
    window.addEventListener('blur', function () {
        reportViolation('window_blur', 'Cửa sổ trình duyệt mất tiêu điểm');
    });

    if (antiCheatEnabled) {
        document.addEventListener('contextmenu', function (event) {
            event.preventDefault();
            reportViolation('context_menu', 'Mở menu chuột phải');
        });
        ['copy', 'cut', 'paste'].forEach(function (eventName) {
            document.addEventListener(eventName, function (event) {
                event.preventDefault();
                reportViolation(eventName, 'Thao tác ' + eventName + ' bị chặn');
            });
        });
        document.addEventListener('keydown', function (event) {
            const key = event.key.toLowerCase();
            const modifier = event.ctrlKey || event.metaKey;
            const devToolsShortcut = event.key === 'F12' || (modifier && event.shiftKey && ['i', 'j', 'c'].includes(key));
            const clipboardShortcut = modifier && ['c', 'x', 'v'].includes(key);
            if (devToolsShortcut || clipboardShortcut) {
                event.preventDefault();
                reportViolation('shortcut', 'Sử dụng phím tắt bị chặn');
            }
        });
    }

    window.setInterval(updateCountdown, 1000);
    updateCountdown();
}());
</script>
</body>
</html>
