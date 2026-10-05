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

    <?php if (!$attempts): ?>
        <section class="card stat-card empty-state d-flex align-items-center justify-content-center text-center p-4">
            <div class="text-muted"><i class="fa-regular fa-chart-bar fs-1 mb-3"></i><h2 class="h5 text-dark">Chưa có dữ liệu thống kê</h2><p class="mb-3">Hãy hoàn thành một bài thi để xem tiến độ của bạn.</p><a href="index.php?action=dashboard" class="btn btn-primary rounded-3">Xem đề thi</a></div>
        </section>
    <?php else: ?>
        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="metric p-3 h-100"><div class="text-muted small">Số bài kiểm tra đã làm</div><div class="h3 fw-bold text-primary mb-0"><?php echo count($attempts); ?></div></div></div>
            <div class="col-md-4"><div class="metric p-3 h-100"><div class="text-muted small">Câu trả lời đúng</div><div class="h3 fw-bold text-success mb-0"><?php echo $totalCorrect; ?></div></div></div>
            <div class="col-md-4"><div class="metric p-3 h-100"><div class="text-muted small">Câu trả lời sai</div><div class="h3 fw-bold text-danger mb-0"><?php echo $totalWrong; ?></div></div></div>
        </div>
        <div class="row g-4">
            <div class="col-lg-8">
                <section class="card stat-card h-100">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-1"><i class="fa-solid fa-arrow-trend-up text-primary me-2"></i>Điểm qua các lần làm bài</h2>
                        <p class="text-muted small mb-4">Theo thứ tự thời gian hoàn thành</p>
                        <div class="chart-wrap"><canvas id="scoreChart"></canvas></div>
                    </div>
                </section>
            </div>
            <div class="col-lg-4">
                <section class="card stat-card h-100">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-1"><i class="fa-solid fa-circle-half-stroke text-success me-2"></i>Độ chính xác</h2>
                        <p class="text-muted small mb-4">Tổng hợp tất cả lần làm bài</p>
                        <div class="chart-wrap d-flex align-items-center justify-content-center"><canvas id="accuracyChart"></canvas></div>
                    </div>
                </section>
            </div>
        </div>
        <section class="card stat-card mt-4">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Lịch sử làm bài</h2>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Đề thi</th><th>Điểm</th><th>Đúng</th><th>Sai</th><th>Hoàn thành</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach (array_reverse($attempts) as $attempt): ?>
                            <tr>
                                <td class="fw-semibold"><?php echo htmlspecialchars($attempt['title']); ?></td>
                                <td class="fw-bold text-primary"><?php echo number_format((float) $attempt['total_score'], 2); ?></td>
                                <td class="text-success"><?php echo (int) $attempt['correct_count']; ?></td>
                                <td class="text-danger"><?php echo (int) $attempt['wrong_count']; ?></td>
                                <td class="text-muted small"><?php echo htmlspecialchars($attempt['end_time']); ?></td>
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
const scoreLabels = <?php echo json_encode(array_map(function ($attempt, $index) { return 'Lần ' . ($index + 1); }, $attempts, array_keys($attempts)), JSON_UNESCAPED_UNICODE); ?>;
const scoreData = <?php echo json_encode(array_map(function ($attempt) { return (float) $attempt['total_score']; }, $attempts)); ?>;
new Chart(document.getElementById('scoreChart'), {
    type: 'line',
    data: { labels: scoreLabels, datasets: [{ label: 'Điểm', data: scoreData, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,.12)', fill: true, tension: .35, pointRadius: 4, pointBackgroundColor: '#0d6efd' }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
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