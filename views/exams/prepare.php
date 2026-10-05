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
    <form id="start-exam-form" method="post" action="index.php?action=start_exam" class="text-end">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <input type="hidden" name="exam_id" value="<?php echo (int) $exam['exam_id']; ?>">
        <button type="submit" class="btn btn-primary btn-lg"><i class="fa-solid fa-play me-2"></i>Bắt đầu làm bài</button>
    </form>
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