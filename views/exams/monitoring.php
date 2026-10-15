<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giám sát - <?php echo htmlspecialchars($exam['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --ink: #17233c; }
        body { min-height: 100vh; background: linear-gradient(135deg, #eaf4ff, #f8fbff 52%, #fff); color: var(--ink); }
        .monitor-shell { max-width: 1180px; }
        .monitor-heading { border: 0; border-radius: 16px; background: linear-gradient(135deg, #0d6efd, #438df4); color: #fff; }
        .monitor-table { border: 1px solid #e5edf8; border-radius: 8px; overflow: hidden; }
        .table thead th { color: #64748b; font-size: .78rem; text-transform: uppercase; }
    </style>
</head>
<body>
<nav class="navbar bg-white border-bottom">
    <div class="container monitor-shell">
        <a class="navbar-brand fw-bold text-primary" href="index.php?action=dashboard"><i class="fa-solid fa-graduation-cap me-2"></i>EduTest</a>
        <a href="index.php?action=exam_statistics&exam_id=<?php echo (int) $exam['exam_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-chart-column me-1"></i>Thống kê</a>
    </div>
</nav>
<main class="container monitor-shell py-4 py-md-5">
    <section class="monitor-heading p-4 p-md-5 mb-4">
        <div class="small opacity-75 mb-2"><i class="fa-solid fa-tower-broadcast me-1"></i>GIÁM SÁT BÀI THI</div>
        <h1 class="h2 fw-bold mb-0"><?php echo htmlspecialchars($exam['title']); ?></h1>
    </section>
    <div class="d-flex justify-content-between align-items-center mb-3 gap-3">
        <h2 class="h5 fw-bold mb-0">Trạng thái sinh viên</h2>
        <span class="small text-muted" id="last-updated" aria-live="polite">Đang cập nhật...</span>
    </div>
    <div class="monitor-table table-responsive bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Sinh viên</th><th>Trạng thái</th><th>Vi phạm</th><th>Lý do nộp</th><th>Bắt đầu</th><th>Kết thúc</th><th></th></tr></thead>
            <tbody id="attempt-rows">
            <?php foreach ($attempts as $attempt): ?>
                <?php
                $statusLabels = ['in_progress' => 'Đang làm bài', 'completed' => 'Đã nộp bài', 'voided' => 'Đã hủy kết quả'];
                $reasonLabels = ['manual' => 'Nộp thủ công', 'time_expired' => 'Hết giờ', 'violation_limit' => 'Chạm giới hạn vi phạm'];
                ?>
                <tr>
                    <td class="fw-semibold"><?php echo htmlspecialchars($attempt['full_name']); ?></td>
                    <?php $isViolationSubmit = $attempt['submit_reason'] === 'violation_limit'; ?>
                    <td><span class="badge <?php echo $attempt['status'] === 'in_progress' ? 'bg-primary-subtle text-primary' : ($attempt['status'] === 'voided' ? 'bg-secondary-subtle text-secondary' : ($isViolationSubmit ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success')); ?>"><?php echo htmlspecialchars($isViolationSubmit ? 'Bị thu bài do vi phạm' : ($statusLabels[$attempt['status']] ?? $attempt['status'])); ?></span></td>
                    <td><?php echo (int) $attempt['violation_count']; ?></td>
                    <td><?php echo htmlspecialchars($reasonLabels[$attempt['submit_reason']] ?? ''); ?></td>
                    <td class="small text-nowrap"><?php echo htmlspecialchars($attempt['start_time']); ?></td>
                    <td class="small text-nowrap"><?php echo htmlspecialchars($attempt['end_time'] ?? ''); ?></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="index.php?action=attempt_detail&attempt_id=<?php echo (int) $attempt['attempt_id']; ?>" aria-label="Xem chi tiết lượt thi"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<script>
(function () {
    const examId = <?php echo json_encode((int) $exam['exam_id']); ?>;
    const rows = document.getElementById('attempt-rows');
    const updated = document.getElementById('last-updated');
    const statusLabels = { in_progress: 'Đang làm bài', completed: 'Đã nộp bài', voided: 'Đã hủy kết quả' };
    const reasonLabels = { manual: 'Nộp thủ công', time_expired: 'Hết giờ', violation_limit: 'Chạm giới hạn vi phạm' };

    function addCell(row, value, className) {
        const cell = document.createElement('td');
        cell.textContent = value || '';
        if (className) cell.className = className;
        row.appendChild(cell);
        return cell;
    }

    async function refresh() {
        try {
            const response = await fetch('index.php?action=exam_monitoring_data&exam_id=' + examId, { credentials: 'same-origin', cache: 'no-store' });
            if (!response.ok) throw new Error('Không thể tải dữ liệu.');
            const payload = await response.json();
            rows.replaceChildren();
            payload.attempts.forEach(function (attempt) {
                const row = document.createElement('tr');
                addCell(row, attempt.full_name, 'fw-semibold');
                const statusCell = document.createElement('td');
                const badge = document.createElement('span');
                const violationSubmit = attempt.submit_reason === 'violation_limit';
                badge.className = 'badge ' + (attempt.status === 'in_progress' ? 'bg-primary-subtle text-primary' : (attempt.status === 'voided' ? 'bg-secondary-subtle text-secondary' : (violationSubmit ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success')));
                badge.textContent = violationSubmit ? 'Bị thu bài do vi phạm' : (statusLabels[attempt.status] || attempt.status);
                statusCell.appendChild(badge);
                row.appendChild(statusCell);
                addCell(row, String(attempt.violation_count));
                addCell(row, reasonLabels[attempt.submit_reason] || '');
                addCell(row, attempt.start_time, 'small text-nowrap');
                addCell(row, attempt.end_time, 'small text-nowrap');
                const actionCell = document.createElement('td');
                const link = document.createElement('a');
                link.className = 'btn btn-sm btn-outline-primary';
                link.href = 'index.php?action=attempt_detail&attempt_id=' + encodeURIComponent(attempt.attempt_id);
                link.setAttribute('aria-label', 'Xem chi tiết lượt thi');
                link.innerHTML = '<i class="fa-solid fa-arrow-up-right-from-square"></i>';
                actionCell.appendChild(link);
                row.appendChild(actionCell);
                rows.appendChild(row);
            });
            updated.textContent = 'Cập nhật ' + new Date().toLocaleTimeString('vi-VN');
        } catch (error) {
            updated.textContent = 'Mất kết nối tới dữ liệu giám sát';
        }
    }

    window.setInterval(refresh, 5000);
    refresh();
}());
</script>
</body>
</html>