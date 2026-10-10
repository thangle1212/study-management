<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chuẩn bị thi - <?php echo htmlspecialchars($exam['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #eaf4ff, #f8fbff 52%, #fff); color: #17233c; }
        .prepare-shell { max-width: 760px; }
        .prepare-header { border: 0; border-radius: 18px; background: linear-gradient(135deg, #0d6efd, #438df4); color: #fff; }
        .policy-row { border-bottom: 1px solid #e5edf8; }
        .policy-row:last-child { border-bottom: 0; }
        .history-card { border: 1px solid #e5edf8; border-radius: 14px; }
        .history-score { min-width: 86px; }
    </style>
</head>
<body>
<nav class="navbar bg-white border-bottom">
    <div class="container prepare-shell">
        <a class="navbar-brand fw-bold text-primary" href="index.php?action=dashboard"><i class="fa-solid fa-graduation-cap me-2"></i>EduTest</a>
        <a href="index.php?action=dashboard" class="btn btn-sm btn-outline-primary">Quay lại</a>
    </div>
</nav>
<main class="container prepare-shell py-4 py-md-5">
    <section class="prepare-header p-4 p-md-5 mb-4">
        <div class="small opacity-75 mb-2">CHUẨN BỊ BÀI KIỂM TRA</div>
        <h1 class="h2 fw-bold mb-0"><?php echo htmlspecialchars($exam['title']); ?></h1>
    </section>
    <section class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-3">Thông tin bài thi</h2>
            <div class="policy-row d-flex justify-content-between py-3 gap-3"><span>Số câu hỏi</span><strong><?php echo (int) $exam['question_count']; ?></strong></div>
            <div class="policy-row d-flex justify-content-between py-3 gap-3"><span>Thời gian làm bài</span><strong><?php echo (int) $exam['duration_minutes']; ?> phút</strong></div>
            <div class="policy-row d-flex justify-content-between py-3 gap-3"><span>Giám sát</span><strong><?php echo (int) $exam['anti_cheat_enabled'] ? 'Đang bật' : 'Đang tắt'; ?></strong></div>
            <?php if ((int) $exam['anti_cheat_enabled']): ?>
                <div class="policy-row d-flex justify-content-between py-3 gap-3"><span>Giới hạn vi phạm</span><strong><?php echo (int) $exam['violation_limit']; ?> lần</strong></div>
                <div class="policy-row d-flex justify-content-between py-3 gap-3"><span>Xử lý khi chạm giới hạn</span><strong><?php echo $exam['violation_action'] === 'auto_submit' ? 'Tự động nộp bài' : 'Ghi log cảnh báo'; ?></strong></div>
            <?php endif; ?>
        </div>
    </section>
    <div class="alert alert-warning" role="note">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>Rời tab, thu nhỏ cửa sổ hoặc thao tác bị cấm có thể được ghi nhận theo quy chế bài thi.
    </div>
    <form id="start-exam-form" method="post" action="index.php?action=start_exam" class="text-end mb-4">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <input type="hidden" name="exam_id" value="<?php echo (int) $exam['exam_id']; ?>">
        <button type="submit" class="btn btn-primary btn-lg"><i class="fa-solid fa-play me-2"></i><?php echo $activeAttemptId ? 'Tiếp tục làm bài' : 'Bắt đầu làm bài'; ?></button>
    </form>

    <section aria-labelledby="attempt-history-title">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 id="attempt-history-title" class="h5 fw-bold mb-0">
                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Lịch sử làm bài
            </h2>
            <span class="badge rounded-pill bg-primary-subtle text-primary">
                <?php echo $completedAttemptCount; ?> lần
            </span>
        </div>
        <?php if (!$attemptHistory): ?>
            <div class="card history-card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fa-regular fa-folder-open fs-2 mb-2"></i>
                    <p class="mb-0">Bạn chưa có lịch sử làm bài.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="d-grid gap-3">
                <?php foreach ($attemptHistory as $history): ?>
                    <article class="card history-card">
                        <div class="card-body p-3 p-md-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div>
                                    <div class="text-muted small mb-1">
                                        Lần <?php echo $completedAttemptCount--; ?> ·
                                        <?php echo htmlspecialchars($history['end_time'] ?? 'Chưa xác định'); ?>
                                    </div>
                                    <div class="d-flex flex-wrap gap-3 small">
                                        <span><i class="fa-solid fa-circle-check text-success me-1"></i><?php echo (int) $history['correct_count']; ?>/<?php echo (int) $history['question_count']; ?> câu đúng</span>
                                        <span><i class="fa-regular fa-clock text-primary me-1"></i><?php echo htmlspecialchars($history['submit_reason'] === 'time_expired' ? 'Hết giờ' : ($history['submit_reason'] === 'violation_limit' ? 'Tự động nộp' : 'Đã nộp')); ?></span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <strong class="history-score text-end text-primary fs-5"><?php echo number_format((float) $history['total_score'], 2); ?> điểm</strong>
                                    <a class="btn btn-outline-primary btn-sm text-nowrap" href="index.php?action=attempt_detail&amp;attempt_id=<?php echo (int) $history['attempt_id']; ?>&amp;from=exam&amp;exam_id=<?php echo (int) $exam['exam_id']; ?>">
                                        <i class="fa-solid fa-eye me-1"></i>Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<script>
document.getElementById('start-exam-form').addEventListener('submit', async function (event) {
    event.preventDefault();
    if (!window.confirm('Bạn đã sẵn sàng bắt đầu bài thi?')) return;
    if (document.documentElement.requestFullscreen) {
        try { await document.documentElement.requestFullscreen(); } catch (error) {}
    }
    this.submit();
});
</script>
</body>
</html>