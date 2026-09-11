<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tổng quan - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --brand: #0d6efd; --ink: #17233c; }
        body { background: linear-gradient(135deg, #eaf4ff 0%, #f8fbff 48%, #fff 100%); color: var(--ink); min-height: 100vh; }
        .topbar { background: rgba(255,255,255,.9); border-bottom: 1px solid #e5edf8; backdrop-filter: blur(12px); }
        .brand { color: var(--brand); font-size: 1.35rem; }
        .welcome { border: 0; border-radius: 22px; background: linear-gradient(135deg, #0d6efd, #438df4); color: #fff; box-shadow: 0 16px 36px rgba(13,110,253,.18); }
        .exam-card { border: 1px solid #e5edf8; border-radius: 16px; box-shadow: 0 8px 24px rgba(31,74,125,.06); transition: transform .2s ease, box-shadow .2s ease; }
        .exam-card:hover { transform: translateY(-3px); box-shadow: 0 13px 30px rgba(31,74,125,.11); }
        .exam-icon { width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; }
    </style>
</head>
<body>
<nav class="navbar topbar sticky-top">
    <div class="container py-2">
        <a class="navbar-brand brand fw-bold" href="index.php?action=dashboard"><i class="fa-solid fa-graduation-cap me-2"></i>EduTest</a>
        <div class="d-flex align-items-center gap-3">
            <span class="d-none d-md-inline text-muted small">Xin chào, <strong><?php echo htmlspecialchars($_SESSION['user']['full_name']); ?></strong></span>
            <a href="index.php?action=logout" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-right-from-bracket me-1"></i>Đăng xuất</a>
        </div>
    </div>
</nav>
<div class="container py-4 py-md-5">
    <?php
    $publishedExams = $pdo->query("SELECT exam_id, title FROM exams WHERE status = 'published' ORDER BY created_at DESC")->fetchAll();
    $teacherExams = [];
    if ($_SESSION['user']['role'] === 'teacher') {
        $examStatement = $pdo->prepare("SELECT exam_id, title FROM exams WHERE teacher_id = ? ORDER BY created_at DESC");
        $examStatement->execute([(int) $_SESSION['user']['id']]);
        $teacherExams = $examStatement->fetchAll();
    }
    ?>
    <section class="welcome p-4 p-md-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="small opacity-75 text-uppercase mb-2"><i class="fa-solid fa-sparkles me-1"></i> Không gian học tập của bạn</div>
                <h1 class="h2 fw-bold mb-2">Chào mừng, <?php echo htmlspecialchars($_SESSION['user']['full_name']); ?>!</h1>
                <p class="mb-0 opacity-75">Bạn đang đăng nhập với vai trò <strong><?php echo htmlspecialchars($_SESSION['user']['role']); ?></strong>. Chọn một hoạt động để bắt đầu.</p>
            </div>
            <div class="col-lg-4 text-lg-end"><i class="fa-solid fa-chart-line display-3 opacity-25"></i></div>
        </div>
    </section>

    <?php if ($_SESSION['user']['role'] === 'student'): ?>
        <div class="d-flex justify-content-between align-items-end mb-3 gap-3">
            <div><h2 class="h4 fw-bold mb-1">Đề thi dành cho bạn</h2><p class="text-muted small mb-0">Các đề thi đang được mở</p></div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2"><?php echo count($publishedExams); ?> đề thi</span>
                <a href="index.php?action=student_statistics" class="btn btn-sm btn-outline-primary rounded-3"><i class="fa-solid fa-chart-line me-1"></i>Thống kê của tôi</a>
            </div>
        </div>
        <div class="row g-3">
            <?php if (!$publishedExams): ?>
                <div class="col-12"><div class="card exam-card p-4 text-center text-muted"><i class="fa-regular fa-folder-open fs-2 mb-2"></i><p class="mb-0">Hiện chưa có đề thi được công khai.</p></div></div>
            <?php else: foreach ($publishedExams as $publishedExam): ?>
                <div class="col-md-6 col-xl-4"><div class="card exam-card h-100 p-3"><div class="d-flex gap-3 align-items-start"><span class="exam-icon bg-primary-subtle text-primary"><i class="fa-solid fa-file-pen"></i></span><div><h3 class="h6 fw-bold mb-1"><?php echo htmlspecialchars($publishedExam['title']); ?></h3><p class="text-muted small mb-3">Đề kiểm tra trực tuyến</p><a href="index.php?action=take_exam&exam_id=<?php echo (int) $publishedExam['exam_id']; ?>" class="btn btn-sm btn-primary rounded-3">Làm bài <i class="fa-solid fa-arrow-right ms-1"></i></a></div></div></div></div>
            <?php endforeach; endif; ?>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-between align-items-end mb-3">
            <div><h2 class="h4 fw-bold mb-1">Đề thi của bạn</h2><p class="text-muted small mb-0">Theo dõi kết quả và mức độ hoàn thành</p></div>
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2"><?php echo count($teacherExams); ?> đề thi</span>
        </div>
        <div class="row g-3">
            <?php if (!$teacherExams): ?>
                <div class="col-12"><div class="card exam-card p-4 text-center text-muted"><i class="fa-regular fa-folder-open fs-2 mb-2"></i><p class="mb-0">Bạn chưa có đề thi nào.</p></div></div>
            <?php else: foreach ($teacherExams as $teacherExam): ?>
                <div class="col-md-6 col-xl-4"><div class="card exam-card h-100 p-3"><div class="d-flex gap-3 align-items-start"><span class="exam-icon bg-success-subtle text-success"><i class="fa-solid fa-chart-column"></i></span><div><h3 class="h6 fw-bold mb-1"><?php echo htmlspecialchars($teacherExam['title']); ?></h3><p class="text-muted small mb-3">Bảng điểm và phân tích câu hỏi</p><a href="index.php?action=exam_statistics&exam_id=<?php echo (int) $teacherExam['exam_id']; ?>" class="btn btn-sm btn-outline-primary rounded-3">Xem thống kê <i class="fa-solid fa-arrow-right ms-1"></i></a></div></div></div></div>
            <?php endforeach; endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>