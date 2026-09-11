<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thống kê bài thi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --brand: #0d6efd; --ink: #17233c; }
        body { background: linear-gradient(135deg, #eaf4ff 0%, #f8fbff 48%, #fff 100%); color: var(--ink); }
        .topbar { background: rgba(255,255,255,.9); border-bottom: 1px solid #e5edf8; backdrop-filter: blur(12px); }
        .brand { color: var(--brand); font-size: 1.35rem; }
        .stat-card { border: 1px solid #e5edf8; border-radius: 18px; box-shadow: 0 10px 28px rgba(31,74,125,.07); }
        .page-heading { border: 0; border-radius: 22px; background: linear-gradient(135deg, #0d6efd, #438df4); color: #fff; box-shadow: 0 16px 36px rgba(13,110,253,.18); }
        .table thead th { color: #6c7b93; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; border-bottom-width: 1px; }
        .table tbody tr:last-child td { border-bottom: 0; }
    </style>
</head>
<body>
<nav class="navbar topbar sticky-top">
    <div class="container">
        <a class="navbar-brand brand fw-bold" href="index.php?action=dashboard"><i class="fa-solid fa-graduation-cap me-2"></i>EduTest</a>
        <a href="index.php?action=dashboard" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-left me-1"></i>Trang tổng quan</a>
    </div>
</nav>
<div class="container py-4 py-md-5">
    <div class="page-heading p-4 p-md-5 mb-4">
        <div class="small opacity-75 mb-2"><i class="fa-solid fa-chart-line me-1"></i> BÁO CÁO KẾT QUẢ</div>
        <h1 class="h2 fw-bold mb-2"><?php echo htmlspecialchars($exam['title']); ?></h1>
        <p class="mb-0 opacity-75">Theo dõi điểm số và nhận diện những câu hỏi học sinh thường mắc lỗi.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-1"><i class="fa-solid fa-ranking-star text-primary me-2"></i>Bảng điểm</h5>
                    <p class="text-muted small mb-4">Danh sách các lượt nộp bài gần nhất</p>
                    <?php if (!$scores): ?>
                        <p class="text-muted">Chưa có học sinh nộp bài.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead><tr><th>Học sinh</th><th>Điểm</th><th>Thời gian</th></tr></thead>
                                <tbody>
                                <?php foreach ($scores as $score): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($score['full_name']); ?></td>
                                        <td class="fw-bold"><?php echo number_format((float) $score['total_score'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($score['end_time']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-1"><i class="fa-solid fa-chart-column text-danger me-2"></i>Phân tích câu sai</h5>
                    <p class="text-muted small mb-3">Số lượt trả lời sai theo từng câu hỏi</p>
                    <canvas id="wrongQuestionsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const labels = <?php echo json_encode(array_map(function ($item) { return 'Câu ' . $item['question_id']; }, $wrongQuestions), JSON_UNESCAPED_UNICODE); ?>;
const wrongCounts = <?php echo json_encode(array_map(function ($item) { return (int) $item['wrong_count']; }, $wrongQuestions)); ?>;
new Chart(document.getElementById('wrongQuestionsChart'), {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Lượt sai', data: wrongCounts, backgroundColor: '#dc3545' }] },
    options: { indexAxis: 'y', responsive: true, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
</body>
</html>
