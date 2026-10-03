<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chi tiết lượt nộp - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --brand: #0d6efd; --ink: #17233c; }
        body { background: linear-gradient(135deg, #eaf4ff 0%, #f8fbff 48%, #fff 100%); color: var(--ink); }
        .topbar { background: rgba(255,255,255,.9); border-bottom: 1px solid #e5edf8; backdrop-filter: blur(12px); }
        .brand { color: var(--brand); font-size: 1.35rem; }
        .page-heading { border: 0; border-radius: 22px; background: linear-gradient(135deg, #0d6efd, #438df4); color: #fff; box-shadow: 0 16px 36px rgba(13,110,253,.18); }
        .question-card { border: 1px solid #e5edf8; border-radius: 18px; box-shadow: 0 10px 28px rgba(31,74,125,.07); }
        .answer-row { border: 1px solid #e5edf8; border-radius: 10px; }
        .answer-selected { background: #eef6ff; border-color: #86b7fe; }
        .answer-correct { background: #eefaf3; border-color: #8bd3a8; }
        .answer-wrong { background: #fff2f2; border-color: #f1aeb5; }
    </style>
</head>
<body>
<nav class="navbar topbar sticky-top">
    <div class="container">
        <a class="navbar-brand brand fw-bold" href="index.php?action=dashboard"><i class="fa-solid fa-graduation-cap me-2"></i>EduTest</a>
        <?php $backAction = $_SESSION['user']['role'] === 'student' ? 'student_statistics' : 'exam_statistics&exam_id=' . (int) $attempt['exam_id']; ?>
        <a href="index.php?action=<?php echo $backAction; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-left me-1"></i><?php echo $_SESSION['user']['role'] === 'student' ? 'Quay lại thống kê cá nhân' : 'Quay lại bảng điểm'; ?></a>
    </div>
</nav>

<main class="container py-4 py-md-5">
    <?php
    $start = new DateTime($attempt['start_time']);
    $end = new DateTime($attempt['end_time']);
    $durationSeconds = max(0, $end->getTimestamp() - $start->getTimestamp());
    $durationText = floor($durationSeconds / 60) . ' phút ' . ($durationSeconds % 60) . ' giây';
    ?>
    <section class="page-heading p-4 p-md-5 mb-4">
        <div class="small opacity-75 text-uppercase mb-2"><i class="fa-solid fa-file-circle-check me-1"></i> CHI TIẾT LƯỢT NỘP</div>
        <h1 class="h2 fw-bold mb-2"><?php echo htmlspecialchars($attempt['title']); ?></h1>
        <p class="mb-0 opacity-75">Sinh viên: <?php echo htmlspecialchars($attempt['full_name']); ?></p>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card question-card p-3 h-100"><div class="text-muted small">Điểm đạt được</div><div class="h3 fw-bold text-primary mb-0"><?php echo number_format((float) $attempt['total_score'], 2); ?></div></div></div>
        <div class="col-md-4"><div class="card question-card p-3 h-100"><div class="text-muted small">Bắt đầu làm</div><div class="fw-semibold mt-1"><?php echo htmlspecialchars($attempt['start_time']); ?></div></div></div>
        <div class="col-md-4"><div class="card question-card p-3 h-100"><div class="text-muted small">Nộp bài / thời gian làm</div><div class="fw-semibold mt-1"><?php echo htmlspecialchars($attempt['end_time']); ?></div><div class="text-muted small mt-1"><i class="fa-regular fa-clock me-1"></i><?php echo $durationText; ?></div></div></div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h2 class="h4 fw-bold mb-1">Chi tiết câu trả lời</h2><p class="text-muted small mb-0">Đối chiếu lựa chọn của sinh viên với đáp án đúng.</p></div>
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2"><?php echo count($questions); ?> câu</span>
    </div>

    <?php $number = 1; foreach ($questions as $question): ?>
        <section class="card question-card mb-3">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between gap-3 align-items-start mb-3">
                    <h3 class="h6 fw-bold mb-0"><span class="badge rounded-pill bg-primary-subtle text-primary me-2">Câu <?php echo $number++; ?></span><?php echo htmlspecialchars($question['content']); ?></h3>
                    <?php if ($question['correct']): ?>
                        <span class="badge rounded-pill bg-success-subtle text-success flex-shrink-0"><i class="fa-solid fa-check me-1"></i>Đúng</span>
                    <?php else: ?>
                        <span class="badge rounded-pill bg-danger-subtle text-danger flex-shrink-0"><i class="fa-solid fa-xmark me-1"></i>Sai</span>
                    <?php endif; ?>
                </div>
                <div class="row g-2">
                    <?php foreach ($question['answers'] as $answer): ?>
                        <?php $selected = in_array($answer['answer_id'], $question['selected_ids'], true); ?>
                        <div class="col-12">
                            <div class="answer-row p-3 <?php echo $selected ? ($answer['is_correct'] ? 'answer-correct' : 'answer-wrong') : ($answer['is_correct'] ? 'answer-correct' : ''); ?>">
                                <i class="fa-solid <?php echo $selected ? ($answer['is_correct'] ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger') : ($answer['is_correct'] ? 'fa-check text-success' : 'fa-circle text-muted'); ?> me-2"></i>
                                <?php echo htmlspecialchars($answer['content']); ?>
                                <?php if ($selected): ?><span class="badge bg-primary-subtle text-primary ms-2">Sinh viên chọn</span><?php endif; ?>
                                <?php if ($answer['is_correct']): ?><span class="badge bg-success-subtle text-success ms-2">Đáp án đúng</span><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (!$question['selected_ids']): ?>
                    <div class="text-muted small mt-3"><i class="fa-regular fa-circle-xmark me-1"></i>Sinh viên bỏ trống câu này.</div>
                <?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>
</main>
</body>
</html>
