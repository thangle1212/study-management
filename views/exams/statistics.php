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
        <div class="d-flex gap-2">
            <a href="index.php?action=exam_monitoring&exam_id=<?php echo (int) $exam['exam_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-tower-broadcast me-1"></i>Giám sát</a>
            <a href="index.php?action=dashboard" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-left me-1"></i>Trang tổng quan</a>
        </div>
    </div>
</nav>
<div class="container py-4 py-md-5">
    <div class="page-heading p-4 p-md-5 mb-4">
        <div class="small opacity-75 mb-2"><i class="fa-solid fa-chart-line me-1"></i> BÁO CÁO KẾT QUẢ</div>
        <h1 class="h2 fw-bold mb-2"><?php echo htmlspecialchars($exam['title']); ?></h1>
        <p class="mb-0 opacity-75">Theo dõi điểm số và nhận diện những câu hỏi học sinh thường mắc lỗi.</p>
    </div>

    <?php if (($_GET['settings'] ?? '') === 'saved'): ?>
        <div class="alert alert-success" role="status">Đã cập nhật cấu hình đề thi.</div>
    <?php endif; ?>

    <section class="card stat-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div><h2 class="h5 fw-bold mb-1">Cấu hình giám sát</h2><p class="text-muted small mb-0">Áp dụng cho các lượt thi mới.</p></div>
                <i class="fa-solid fa-shield-halved text-primary fs-4"></i>
            </div>
            <form method="post" action="index.php?action=update_exam_settings" class="row g-3 align-items-end">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="exam_id" value="<?php echo (int) $exam['exam_id']; ?>">
                <div class="col-sm-6 col-lg-3">
                    <label for="exam-duration" class="form-label">Thời gian (phút)</label>
                    <input id="exam-duration" class="form-control" type="number" name="duration_minutes" min="1" max="360" value="<?php echo (int) $exam['duration_minutes']; ?>" required>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="violation-limit" class="form-label">Giới hạn vi phạm</label>
                    <input id="violation-limit" class="form-control" type="number" name="violation_limit" min="1" max="20" value="<?php echo (int) $exam['violation_limit']; ?>" required>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="violation-action" class="form-label">Khi vượt giới hạn</label>
                    <select id="violation-action" class="form-select" name="violation_action">
                        <option value="auto_submit" <?php echo $exam['violation_action'] === 'auto_submit' ? 'selected' : ''; ?>>Tự động nộp bài</option>
                        <option value="log_only" <?php echo $exam['violation_action'] === 'log_only' ? 'selected' : ''; ?>>Chỉ ghi log</option>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-3 d-flex justify-content-between align-items-center gap-3">
                    <label class="form-check-label" for="anti-cheat-enabled">Chống gian lận</label>
                    <div class="form-check form-switch fs-5 mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="anti-cheat-enabled" name="anti_cheat_enabled" value="1" <?php echo (int) $exam['anti_cheat_enabled'] ? 'checked' : ''; ?>>
                    </div>
                </div>
                <div class="col-12 text-end"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Lưu cấu hình</button></div>
            </form>
        </div>
    </section>

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
                                <thead><tr><th>Học sinh</th><th>Lần nộp</th><th>Điểm</th><th>Vi phạm</th><th>Trạng thái</th><th>Thời gian</th></tr></thead>
                                <tbody>
                                <?php foreach ($scores as $score): ?>
                                    <tr>
                                        <td><a class="fw-semibold text-decoration-none" href="index.php?action=attempt_detail&attempt_id=<?php echo (int) $score['attempt_id']; ?>"><?php echo htmlspecialchars($score['full_name']); ?></a></td>
                                        <td><a href="index.php?action=attempt_detail&attempt_id=<?php echo (int) $score['attempt_id']; ?>" class="badge rounded-pill bg-primary-subtle text-primary text-decoration-none">Lần <?php echo (int) $score['attempt_number']; ?></a></td>
                                        <td class="fw-bold"><?php echo number_format((float) $score['total_score'], 2); ?></td>
                                        <td><?php echo (int) $score['violation_count']; ?></td>
                                        <td>
                                            <?php if ($score['review_status'] === 'pending'): ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis">Chờ xem xét</span>
                                            <?php elseif ($score['submit_reason'] === 'violation_limit'): ?>
                                                <span class="badge bg-danger-subtle text-danger">Nộp do vi phạm</span>
                                            <?php elseif ($score['submit_reason'] === 'time_expired'): ?>
                                                <span class="badge bg-secondary-subtle text-secondary">Hết giờ</span>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success">Đã nộp</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><a href="index.php?action=attempt_detail&attempt_id=<?php echo (int) $score['attempt_id']; ?>" class="text-decoration-none small"><?php echo htmlspecialchars($score['end_time']); ?> <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></a></td>
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
                    <h5 class="fw-bold mb-1"><i class="fa-solid fa-chart-pie text-primary me-2"></i>Tổng quan đúng / sai</h5>
                    <p class="text-muted small mb-3">Tổng số câu đúng và sai trong các lượt làm bài</p>
                    <?php if (!$scores): ?>
                        <p class="text-muted">Chưa có dữ liệu để thống kê.</p>
                    <?php else: ?>
                        <div style="height: 280px;"><canvas id="correctWrongChart"></canvas></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card stat-card mt-4">
        <div class="card-body">
            <h5 class="fw-bold mb-1"><i class="fa-solid fa-chart-column text-primary me-2"></i>Phổ điểm</h5>
            <p class="text-muted small mb-3">Số lượt sinh viên đạt từng mức điểm</p>
            <?php if (!$scores): ?>
                <p class="text-muted mb-0">Chưa có dữ liệu để thống kê.</p>
            <?php else: ?>
                <div style="height: 280px;"><canvas id="scoreDistributionChart"></canvas></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php if ($scores): ?>
<script>
new Chart(document.getElementById('correctWrongChart'), {
    type: 'doughnut',
    data: {
        labels: ['Câu đúng', 'Câu sai'],
        datasets: [{
            data: [<?php echo $correctCount; ?>, <?php echo $wrongCount; ?>],
            backgroundColor: ['#198754', '#dc3545'],
            borderColor: '#ffffff',
            borderWidth: 4,
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '62%',
        plugins: { legend: { position: 'bottom' } }
    }
});

new Chart(document.getElementById('scoreDistributionChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($scoreDistribution), JSON_UNESCAPED_UNICODE); ?>,
        datasets: [{
            label: 'Số lượt đạt điểm',
            data: <?php echo json_encode(array_values($scoreDistribution)); ?>,
            backgroundColor: '#0d6efd',
            borderRadius: 8,
            maxBarThickness: 56
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'Số lượt' } },
            x: { title: { display: true, text: 'Mức điểm' } }
        }
    }
});
</script>
<?php endif; ?>
</body>
</html>
