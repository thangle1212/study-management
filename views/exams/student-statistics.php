<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thống kê cá nhân - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --brand: #0d6efd; --ink: #17233c; }
        body { background: linear-gradient(135deg, #eaf4ff 0%, #f8fbff 48%, #fff 100%); color: var(--ink); }
        .topbar { background: rgba(255,255,255,.9); border-bottom: 1px solid #e5edf8; backdrop-filter: blur(12px); }
        .brand { color: var(--brand); font-size: 1.35rem; }
        .page-heading { border: 0; border-radius: 22px; background: linear-gradient(135deg, #0d6efd, #438df4); color: #fff; box-shadow: 0 16px 36px rgba(13,110,253,.18); }
        .stat-card { border: 1px solid #e5edf8; border-radius: 18px; box-shadow: 0 10px 28px rgba(31,74,125,.07); }
        .metric { border-radius: 14px; background: #f7faff; border: 1px solid #e5edf8; }
        .chart-wrap { min-height: 290px; }
        .empty-state { min-height: 260px; }
        .metric { height: 100%; }
        .chart-center { position: relative; height: 290px; }
        .chart-center canvas { position: relative; z-index: 1; }
        .chart-center-label { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none; z-index: 2; }
    </style>
</head>
<body>
<nav class="navbar topbar sticky-top">
    <div class="container">
        <a class="navbar-brand brand fw-bold" href="index.php?action=dashboard"><i class="fa-solid fa-graduation-cap me-2"></i>EduTest</a>
        <a href="index.php?action=dashboard" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-left me-1"></i>Trang tổng quan</a>
    </div>
</nav>

<main class="container py-4 py-md-5">
    <section class="page-heading p-4 p-md-5 mb-4">
        <div class="small opacity-75 text-uppercase mb-2"><i class="fa-solid fa-chart-line me-1"></i> TIẾN ĐỘ HỌC TẬP</div>
        <h1 class="h2 fw-bold mb-2">Thống kê cá nhân</h1>
        <p class="mb-0 opacity-75">Theo dõi kết quả làm bài và nhận biết mức độ chính xác của bạn.</p>
    </section>

    <?php if ($availableExams): ?>
        <form method="get" class="card stat-card mb-4">
            <div class="card-body d-flex flex-wrap align-items-center gap-3">
                <input type="hidden" name="action" value="student_statistics">
                <label for="exam-filter" class="fw-semibold mb-0">Xem theo đề thi:</label>
                <select id="exam-filter" name="exam_id" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="0">Tất cả đề thi</option>
                    <?php foreach ($availableExams as $availableExam): ?>
                        <option value="<?php echo (int) $availableExam['exam_id']; ?>" <?php echo $selectedExamId === (int) $availableExam['exam_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($availableExam['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    <?php endif; ?>

    <?php if (!$attempts): ?>
        <section class="card stat-card empty-state d-flex align-items-center justify-content-center text-center p-4">
            <div class="text-muted"><i class="fa-regular fa-chart-bar fs-1 mb-3"></i><h2 class="h5 text-dark">Chưa có dữ liệu thống kê</h2><p class="mb-3">Hãy hoàn thành một bài thi để xem tiến độ của bạn.</p><a href="index.php?action=dashboard" class="btn btn-primary rounded-3">Xem đề thi</a></div>
        </section>
    <?php else: ?>
        <div class="row row-cols-2 row-cols-lg-5 g-3 mb-4">
            <div class="col"><div class="metric p-3"><div class="text-muted small">Số đề đã tham gia</div><div class="h3 fw-bold text-primary mb-0"><?php echo $examCount; ?></div></div></div>
            <div class="col"><div class="metric p-3"><div class="text-muted small">Tổng lượt làm</div><div class="h3 fw-bold text-primary mb-0"><?php echo $attemptCount; ?></div></div></div>
            <div class="col"><div class="metric p-3"><div class="text-muted small">Điểm trung bình</div><div class="h3 fw-bold text-info mb-0"><?php echo number_format($averageScore, 2); ?></div></div></div>
            <div class="col"><div class="metric p-3"><div class="text-muted small">Tỷ lệ chính xác</div><div class="h3 fw-bold text-success mb-0"><?php echo number_format($accuracy, 1); ?>%</div><div class="small text-muted"><?php echo $totalCorrect; ?> đúng / <?php echo $totalWrong; ?> sai</div></div></div>
            <div class="col"><div class="metric p-3"><div class="text-muted small">Thời gian trung bình</div><div class="h3 fw-bold text-warning-emphasis mb-0"><?php echo floor($averageDurationSeconds / 60); ?>:<?php echo str_pad($averageDurationSeconds % 60, 2, '0', STR_PAD_LEFT); ?></div><div class="small text-muted">phút : giây</div></div></div>
        </div>
        <div class="row g-4">
            <div class="col-lg-8">
                <section class="card stat-card h-100">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-1"><i class="fa-solid fa-arrow-trend-up text-primary me-2"></i>Điểm qua các lần làm bài</h2>
                        <p class="text-muted small mb-4">Điểm quy đổi theo tỷ lệ % số điểm đạt được</p>
                        <div class="chart-wrap"><canvas id="scoreChart"></canvas></div>
                    </div>
                </section>
            </div>
            <div class="col-lg-4">
                <section class="card stat-card h-100">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-1"><i class="fa-solid fa-circle-half-stroke text-success me-2"></i>Độ chính xác</h2>
                        <p class="text-muted small mb-4">Tổng hợp tất cả lần làm bài</p>
                        <div class="chart-center d-flex align-items-center justify-content-center"><canvas id="accuracyChart"></canvas><div class="chart-center-label"><strong class="fs-3"><?php echo number_format($accuracy, 1); ?>%</strong><span class="small text-muted">Tỷ lệ đúng</span></div></div>
                    </div>
                </section>
            </div>
        </div>
        <section class="card stat-card mt-4">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Lịch sử làm bài</h2>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Lần thi</th><th>Đề thi</th><th>Điểm</th><th>Đúng</th><th>Sai</th><th>Thời lượng</th><th>Hoàn thành</th><th>Trạng thái</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach (array_reverse($attempts) as $attemptIndex => $attempt): ?>
                            <?php $durationSeconds = max(0, strtotime($attempt['end_time']) - strtotime($attempt['start_time'])); ?>
                            <tr>
                                <td class="text-nowrap">Lần <?php echo count($attempts) - $attemptIndex; ?></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($attempt['title']); ?></td>
                                <td class="fw-bold text-primary"><?php echo number_format((float) $attempt['total_score'], 2); ?></td>
                                <td class="text-success"><?php echo (int) $attempt['correct_count']; ?></td>
                                <td class="text-danger"><?php echo (int) $attempt['wrong_count']; ?></td>
                                <td class="text-nowrap"><?php echo floor($durationSeconds / 60); ?>:<?php echo str_pad($durationSeconds % 60, 2, '0', STR_PAD_LEFT); ?></td>
                                <td class="text-muted small"><?php echo htmlspecialchars($attempt['end_time']); ?></td>
                                <td>
                                    <?php if ($attempt['submit_reason'] === 'violation_limit'): ?>
                                        <span class="badge bg-danger-subtle text-danger">Vi phạm - Tự nộp</span>
                                    <?php elseif ($attempt['submit_reason'] === 'time_expired'): ?>
                                        <span class="badge bg-secondary-subtle text-secondary">Hết giờ</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success">Đã nộp</span>
                                    <?php endif; ?>
                                </td>
                                <td><a href="index.php?action=attempt_detail&attempt_id=<?php echo (int) $attempt['attempt_id']; ?>" class="btn btn-sm btn-outline-primary rounded-3 text-nowrap"><i class="fa-solid fa-eye me-1"></i>Chi tiết</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php if ($attempts): ?>
<script>
const scoreLabels = <?php echo json_encode(array_map(function ($attempt, $index) {
    return $attempt['title'] . ' - Lần ' . ($index + 1);
}, $attempts, array_keys($attempts)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
const scoreData = <?php echo json_encode(array_map(function ($attempt) {
    $maxScore = (float) $attempt['max_score'];
    return $maxScore > 0 ? round(((float) $attempt['total_score'] / $maxScore) * 100, 1) : 0;
}, $attempts)); ?>;
const scoreLabelPlugin = {
    id: 'scoreLabelPlugin',
    afterDatasetsDraw: function (chart) {
        const context = chart.ctx;
        chart.getDatasetMeta(0).data.forEach(function (point, index) {
            context.save();
            context.fillStyle = '#17233c';
            context.font = '600 11px sans-serif';
            context.textAlign = 'center';
            context.fillText(chart.data.datasets[0].data[index] + '%', point.x, point.y - 10);
            context.restore();
        });
    }
};
new Chart(document.getElementById('scoreChart'), {
    type: 'line',
    data: { labels: scoreLabels, datasets: [{ label: 'Tỷ lệ điểm (%)', data: scoreData, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,.12)', fill: true, tension: 0.2, pointRadius: 4, pointBackgroundColor: '#0d6efd', pointBorderColor: '#fff', pointBorderWidth: 2 }] },
    plugins: [scoreLabelPlugin],
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (context) { return context.raw + '%'; } } } }, scales: { y: { beginAtZero: true, max: 100, title: { display: true, text: 'Tỷ lệ điểm (%)' } } } }
});
new Chart(document.getElementById('accuracyChart'), {
    type: 'doughnut',
    data: { labels: ['Đúng', 'Sai'], datasets: [{ data: [<?php echo $totalCorrect; ?>, <?php echo $totalWrong; ?>], backgroundColor: ['#198754', '#dc3545'], borderWidth: 0 }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom' } } }
});
</script>
<?php endif; ?>
</body>
</html>