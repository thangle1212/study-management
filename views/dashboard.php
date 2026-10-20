<?php
$userRole = $_SESSION['user']['role'] ?? '';
$userName = $_SESSION['user']['full_name'] ?? 'Người dùng';
$roleLabels = [
    'student' => 'Học sinh',
    'teacher' => 'Giáo viên',
];
$roleLabel = $roleLabels[$userRole] ?? 'Thành viên';
$userIcon = $userRole === 'teacher' ? 'fa-chalkboard-user' : 'fa-user-graduate';
$publishedExams = $pdo->query("SELECT exam_id, title FROM exams WHERE status = 'published' ORDER BY created_at DESC")->fetchAll();
$teacherExams = [];
if ($userRole === 'teacher') {
    $examStatement = $pdo->prepare("SELECT exam_id, title FROM exams WHERE teacher_id = ? ORDER BY created_at DESC");
    $examStatement->execute([(int) $_SESSION['user']['id']]);
    $teacherExams = $examStatement->fetchAll();
}
$visibleExams = $userRole === 'student' ? $publishedExams : $teacherExams;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f7fc">
    <title>Không gian học tập - EduTest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-app">
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a class="admin-brand" href="index.php?action=dashboard">
            <span class="admin-brand-mark" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
            <span>EduTest</span>
        </a>
        <nav class="admin-nav" aria-label="Điều hướng chính">
            <a class="admin-nav-link active" href="index.php?action=dashboard" aria-current="page">
                <i class="fa-solid fa-house" aria-hidden="true"></i><span>Tổng quan</span>
            </a>
            <a class="admin-nav-link" href="index.php?action=profile">
                <i class="fa-regular fa-id-card" aria-hidden="true"></i><span>Hồ sơ của tôi</span>
            </a>
            <?php if ($userRole === 'student'): ?>
                <a class="admin-nav-link" href="index.php?action=student_statistics">
                    <i class="fa-solid fa-chart-line" aria-hidden="true"></i><span>Thống kê của tôi</span>
                </a>
            <?php elseif ($userRole === 'teacher'): ?>
                <a class="admin-nav-link" href="index.php?action=qe_banks">
                    <i class="fa-solid fa-layer-group" aria-hidden="true"></i><span>Ngân hàng câu hỏi</span>
                </a>
                <a class="admin-nav-link" href="index.php?action=qe_exam_create">
                    <i class="fa-solid fa-file-circle-plus" aria-hidden="true"></i><span>Tạo đề thi</span>
                </a>
            <?php endif; ?>
            <a class="admin-nav-link" href="index.php?action=forum">
                <i class="fa-regular fa-comments" aria-hidden="true"></i><span>Diễn đàn</span>
            </a>
        </nav>
        <div class="admin-sidebar-bottom">
            <span class="admin-account-avatar" aria-hidden="true"><i class="fa-solid <?= htmlspecialchars($userIcon, ENT_QUOTES, 'UTF-8') ?>"></i></span>
            <div class="admin-account-copy">
                <div class="admin-account-name"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="admin-account-role"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <a class="admin-logout" href="index.php?action=logout" aria-label="Đăng xuất" title="Đăng xuất">
                <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
            </a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <p class="admin-breadcrumb">EduTest <span aria-hidden="true">/</span> <strong>Không gian học tập</strong></p>
            <a href="index.php?action=forum" class="admin-topbar-link"><i class="fa-regular fa-comments me-1" aria-hidden="true"></i> Diễn đàn</a>
        </header>

        <main class="admin-content">
            <div class="admin-page-heading">
                <div>
                    <p class="admin-eyebrow">Bảng điều khiển cá nhân</p>
                    <h1>Chào mừng, <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></h1>
                    <p>Tiếp tục học tập và theo dõi hoạt động của bạn trên EduTest.</p>
                </div>
                <span class="user-role-badge"><i class="fa-solid <?= htmlspecialchars($userIcon, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <section class="admin-panel user-welcome-panel" aria-label="Lời chào">
                <div class="user-welcome-icon" aria-hidden="true"><i class="fa-solid fa-book-open-reader"></i></div>
                <div class="user-welcome-copy">
                    <p class="admin-eyebrow">Không gian học tập của bạn</p>
                    <h2>Sẵn sàng cho bước tiến tiếp theo?</h2>
                    <p><?= $userRole === 'student'
                        ? 'Chọn một đề thi đang mở để ôn luyện và củng cố kiến thức.'
                        : 'Quản lý đề thi, xây dựng câu hỏi và theo dõi kết quả học tập.' ?></p>
                </div>
                <a href="index.php?action=forum" class="btn-admin-outline user-welcome-action">
                    <i class="fa-regular fa-comments" aria-hidden="true"></i> Tham gia diễn đàn
                </a>
            </section>

            <section class="user-exam-section" aria-labelledby="user-exams-heading">
                <div class="user-section-heading">
                    <div>
                        <p class="admin-eyebrow"><?= $userRole === 'student' ? 'Ôn luyện' : 'Quản lý nội dung' ?></p>
                        <h2 id="user-exams-heading"><?= $userRole === 'student' ? 'Đề thi dành cho bạn' : 'Đề thi của bạn' ?></h2>
                        <p><?= $userRole === 'student'
                            ? 'Các đề thi đang được công khai trên hệ thống.'
                            : 'Theo dõi các đề thi bạn đã tạo và xem phân tích kết quả.' ?></p>
                    </div>
                    <div class="user-section-actions">
                        <span class="user-count-badge"><i class="fa-regular fa-file-lines" aria-hidden="true"></i><?= count($visibleExams) ?> đề thi</span>
                        <?php if ($userRole === 'student'): ?>
                            <a href="index.php?action=student_statistics" class="btn-admin-outline">
                                <i class="fa-solid fa-chart-line" aria-hidden="true"></i> Thống kê của tôi
                            </a>
                        <?php elseif ($userRole === 'teacher'): ?>
                            <a href="index.php?action=qe_exam_create" class="btn-admin-primary">
                                <i class="fa-solid fa-plus" aria-hidden="true"></i> Tạo đề thi
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!$visibleExams): ?>
                    <div class="admin-panel user-empty-state">
                        <span class="user-empty-icon" aria-hidden="true"><i class="fa-regular fa-folder-open"></i></span>
                        <h3><?= $userRole === 'student' ? 'Chưa có đề thi được mở' : 'Chưa có đề thi nào' ?></h3>
                        <p><?= $userRole === 'student'
                            ? 'Các đề thi mới sẽ xuất hiện tại đây khi được công khai.'
                            : 'Bắt đầu tạo đề thi đầu tiên để chia sẻ nội dung học tập với học sinh.' ?></p>
                        <?php if ($userRole === 'teacher'): ?>
                            <a href="index.php?action=qe_exam_create" class="btn-admin-primary"><i class="fa-solid fa-plus" aria-hidden="true"></i> Tạo đề thi</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="user-exam-grid">
                        <?php foreach ($visibleExams as $exam): ?>
                            <article class="admin-panel user-exam-card">
                                <span class="user-exam-icon" aria-hidden="true">
                                    <i class="fa-solid <?= $userRole === 'student' ? 'fa-file-pen' : 'fa-chart-column' ?>"></i>
                                </span>
                                <div class="user-exam-copy">
                                    <h3><?= htmlspecialchars($exam['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p><?= $userRole === 'student' ? 'Đề kiểm tra trực tuyến' : 'Bảng điểm và phân tích câu hỏi' ?></p>
                                    <?php if ($userRole === 'student'): ?>
                                        <a href="index.php?action=prepare_exam&amp;exam_id=<?= (int) $exam['exam_id'] ?>" class="btn-admin-primary user-exam-link">
                                            Làm bài <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?action=exam_statistics&amp;exam_id=<?= (int) $exam['exam_id'] ?>" class="btn-admin-outline user-exam-link">
                                            Xem thống kê <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>
</body>
</html>
