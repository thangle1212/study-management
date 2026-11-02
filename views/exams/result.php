<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kết quả bài thi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: #f2f7fc; }
        .result-card { max-width: 720px; border: 1px solid #d9e5f0; border-radius: 6px; box-shadow: none; overflow: hidden; }
        .result-head { background: #0b3d73; color: #fff; position: relative; overflow: hidden; }
        .result-head-content { position: relative; z-index: 1; }
        .result-kicker { letter-spacing: .04em; }
        .score { width: 158px; height: 158px; border: 9px solid #e6f2fc; background: #fff; color: #1769aa; box-shadow: none; }
        .result-actions { display: flex; gap: .75rem; justify-content: center; flex-wrap: wrap; }
        .result-actions .btn { min-width: 205px; }
        .detail-link { color: #1769aa; border-color: #1769aa; }
        .detail-link:hover, .detail-link:focus-visible { color: #fff; background: #1769aa; border-color: #1769aa; }
        @media (max-width: 575.98px) {
            .result-card { border-radius: 6px; }
            .result-actions { flex-direction: column; }
            .result-actions .btn { width: 100%; }
        }
    </style>
    <link rel="stylesheet" href="assets/css/edutest.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-app">
<div class="container py-5 d-flex justify-content-center">
    <div class="card result-card text-center w-100">
        <div class="result-head p-4 p-md-5">
            <div class="result-head-content">
            <i class="fa-solid fa-circle-check fs-1 mb-3"></i>
            <div class="result-kicker text-uppercase small opacity-75">Bài thi đã hoàn thành</div>
            <h1 class="h3 fw-bold mt-2 mb-0"><?php echo htmlspecialchars($attempt['title']); ?></h1>
            </div>
        </div>
        <div class="p-4 p-md-5">
            <?php
            $submissionMessages = [
                'manual' => 'Bạn đã nộp bài thành công.',
                'time_expired' => 'Hệ thống đã tự nộp bài vì hết giờ.',
                'violation_limit' => 'Bài thi được tự động nộp do chạm giới hạn vi phạm.'
            ];
            ?>
            <p class="mb-3"><?php echo htmlspecialchars($submissionMessages[$attempt['submit_reason']] ?? 'Bài thi đã được hoàn tất.'); ?></p>
            <?php if ($attempt['review_status'] === 'pending'): ?>
                <div class="alert alert-warning text-start">Lượt thi đang chờ giáo viên xem xét.</div>
            <?php elseif ($attempt['review_status'] === 'voided'): ?>
                <div class="alert alert-danger text-start">Giáo viên đã hủy kết quả lượt thi.</div>
            <?php elseif ($attempt['review_status'] === 'retake_allowed'): ?>
                <div class="alert alert-info text-start">Giáo viên đã cho phép làm lại bài thi.</div>
            <?php endif; ?>
            <div class="score rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                <span class="display-5 fw-bold"><?php echo number_format((float) $attempt['total_score'], 2); ?><small class="fs-6">/10</small></span>
            </div>
            <p class="mb-1">Kết quả của <strong><?php echo htmlspecialchars($attempt['full_name']); ?></strong></p>
            <p class="text-muted small mb-4"><i class="fa-regular fa-clock me-1"></i><?php echo htmlspecialchars($attempt['end_time']); ?></p>
            <div class="result-actions">
                <a href="index.php?action=attempt_detail&amp;attempt_id=<?php echo (int) $attempt['attempt_id']; ?>&amp;from=result" class="btn detail-link rounded-3 px-4">
                    <i class="fa-solid fa-list-check me-2"></i>Xem chi tiết bài làm
                </a>
                <a href="index.php?action=dashboard" class="btn btn-primary rounded-3 px-4">
                    <i class="fa-solid fa-house me-2"></i>Về trang tổng quan
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
