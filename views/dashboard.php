<?php
$userRole = $_SESSION['user']['role'] ?? '';
$userName = $_SESSION['user']['full_name'] ?? 'Người dùng';
$roleLabels = [
    'student' => 'Học sinh',
    'teacher' => 'Giáo viên',
];
$roleLabel = $roleLabels[$userRole] ?? 'Thành viên';
$userIcon = $userRole === 'teacher' ? 'fa-chalkboard-user' : 'fa-user-graduate';
$studentClasses = [];
$studentId = (int) ($_SESSION['user']['id'] ?? 0);
$publishedExams = [];
if ($userRole === 'student') {
    $classStatement = $pdo->prepare(
        "SELECT c.class_id, c.class_name, c.class_code, teacher.full_name AS teacher_name
         FROM class_students cs
         INNER JOIN classes c ON c.class_id = cs.class_id
         INNER JOIN users teacher ON teacher.user_id = c.teacher_id
         WHERE cs.student_id = ?
         ORDER BY c.class_name, c.class_id"
    );
    $classStatement->execute([$studentId]);
    $studentClasses = $classStatement->fetchAll(PDO::FETCH_ASSOC);

    $examStatement = $pdo->prepare(
        "SELECT e.exam_id, e.title, e.status, e.start_time, e.end_time, c.class_name,
                CASE
                    WHEN (e.start_time IS NULL OR e.start_time <= NOW())
                     AND (e.end_time IS NULL OR e.end_time >= NOW())
                    THEN 1 ELSE 0
                END AS can_start
         FROM exams e
         LEFT JOIN classes c ON c.class_id = e.class_id
         WHERE (e.status = 'published' OR (e.status = 'scheduled' AND e.class_id IS NOT NULL))
           AND (e.end_time IS NULL OR e.end_time >= NOW())
           AND ((e.status = 'published' AND e.class_id IS NULL) OR EXISTS (
               SELECT 1 FROM class_students cs
               WHERE cs.class_id = e.class_id AND cs.student_id = ?
           ))
         ORDER BY e.created_at DESC"
    );
    $examStatement->execute([$studentId]);
    $publishedExams = $examStatement->fetchAll(PDO::FETCH_ASSOC);
}
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
<?php
$appPageTitle = 'Không gian học tập';
$appActiveAction = 'dashboard';
$appTopbarHtml = '<a href="index.php?action=forum" class="admin-topbar-link"><i class="fa-regular fa-comments me-1" aria-hidden="true"></i> Diễn đàn</a>';
require __DIR__ . '/partials/app-shell-start.php';
?>
<main>
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

            <?php if ($userRole === 'student'): ?>
                <section class="student-dashboard-classes" aria-labelledby="student-classes-heading">
                    <div class="user-section-heading">
                        <div>
                            <p class="admin-eyebrow">Lớp học</p>
                            <h2 id="student-classes-heading">Lớp bạn đang tham gia</h2>
                            <p>Đề thi được giao sẽ xuất hiện trong từng lớp.</p>
                        </div>
                        <a href="index.php?action=student_classes" class="btn-admin-outline">
                            <i class="fa-solid fa-school" aria-hidden="true"></i> Xem lớp học
                        </a>
                    </div>
                    <?php if ($studentClasses): ?>
                        <div class="student-dashboard-class-grid">
                            <?php foreach (array_slice($studentClasses, 0, 3) as $class): ?>
                                <article class="admin-panel student-dashboard-class">
                                    <span class="user-exam-icon" aria-hidden="true"><i class="fa-solid fa-school"></i></span>
                                    <div>
                                        <h3><?= htmlspecialchars($class['class_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                        <p>Mã lớp <?= htmlspecialchars($class['class_code'], ENT_QUOTES, 'UTF-8') ?></p>
                                        <span>GV <?= htmlspecialchars($class['teacher_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="admin-panel student-dashboard-empty">
                            <p>Bạn chưa được thêm vào lớp nào. Lớp học sẽ xuất hiện tại đây khi giáo viên thêm bạn.</p>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <section class="user-exam-section" aria-labelledby="user-exams-heading">
                <div class="user-section-heading">
                    <div>
                        <p class="admin-eyebrow"><?= $userRole === 'student' ? 'Ôn luyện' : 'Quản lý nội dung' ?></p>
                        <h2 id="user-exams-heading"><?= $userRole === 'student' ? 'Đề thi dành cho bạn' : 'Đề thi của bạn' ?></h2>
                        <p><?= $userRole === 'student'
                            ? 'Đề công khai và đề được giao cho các lớp bạn tham gia.'
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
                            ? 'Đề đã công bố và đề được giao cho lớp của bạn sẽ xuất hiện tại đây.'
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
                                    <p><?= $userRole === 'student'
                                        ? (!empty($exam['class_name']) ? 'Giao cho lớp ' . htmlspecialchars($exam['class_name'], ENT_QUOTES, 'UTF-8') : 'Đề công khai')
                                        : 'Bảng điểm và phân tích câu hỏi' ?></p>
                                    <?php if ($userRole === 'student'): ?>
                                        <?php if (!empty($exam['can_start'])): ?>
                                            <a href="index.php?action=prepare_exam&amp;exam_id=<?= (int) $exam['exam_id'] ?>" class="btn-admin-primary user-exam-link">
                                                Làm bài <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="btn-admin-outline user-exam-link" aria-disabled="true">
                                                Mở vào <?= htmlspecialchars(date('d/m/Y H:i', strtotime($exam['start_time'])), ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        <?php endif; ?>
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
<?php require __DIR__ . '/partials/app-shell-end.php'; ?>
</body>
</html>
