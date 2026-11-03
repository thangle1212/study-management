<?php
$appPageTitle = 'Lớp học của tôi';
$appActiveAction = 'student_classes';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f7fc">
    <title>Lớp học của tôi - EduTest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-app">
<?php
require __DIR__ . '/../../partials/app-shell-start.php';
?>
<main>
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow">Không gian học tập</p>
            <h1>Lớp học của tôi</h1>
            <p>Xem các lớp bạn đã tham gia và đề thi giáo viên giao.</p>
        </div>
        <span class="user-count-badge"><i class="fa-solid fa-school" aria-hidden="true"></i><?= count($classes) ?> lớp</span>
    </div>

    <?php if (!$classes): ?>
        <section class="admin-panel user-empty-state" aria-label="Chưa tham gia lớp học">
            <span class="user-empty-icon" aria-hidden="true"><i class="fa-solid fa-school"></i></span>
            <h2>Chưa có lớp học</h2>
            <p>Khi giáo viên thêm bạn vào lớp, thông tin lớp và đề thi được giao sẽ hiển thị tại đây.</p>
        </section>
    <?php else: ?>
        <div class="student-class-list">
            <?php foreach ($classes as $class): ?>
                <?php
                $classExams = array_values(array_filter($assignedExams, static function ($exam) use ($class) {
                    return (int) $exam['class_id'] === (int) $class['class_id'];
                }));
                ?>
                <section class="admin-panel student-class-card" aria-labelledby="student-class-<?= (int) $class['class_id'] ?>">
                    <header class="student-class-heading">
                        <span class="user-exam-icon" aria-hidden="true"><i class="fa-solid fa-school"></i></span>
                        <div class="student-class-title">
                            <h2 id="student-class-<?= (int) $class['class_id'] ?>"><?= htmlspecialchars($class['class_name'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <p>Mã lớp <strong><?= htmlspecialchars($class['class_code'], ENT_QUOTES, 'UTF-8') ?></strong> <span aria-hidden="true">·</span> Giáo viên <?= htmlspecialchars($class['teacher_name'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <span class="user-count-badge"><?= count($classExams) ?> đề thi</span>
                    </header>

                    <?php if ($classExams): ?>
                        <div class="student-class-exams">
                            <?php foreach ($classExams as $exam): ?>
                                <article class="student-assigned-exam">
                                    <span class="student-assigned-icon" aria-hidden="true"><i class="fa-solid fa-file-circle-check"></i></span>
                                    <div class="student-assigned-exam-copy">
                                        <h3><?= htmlspecialchars($exam['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                        <p><?= (int) $exam['duration_minutes'] ?> phút
                                            <?php if (!empty($exam['start_time'])): ?>
                                                <span aria-hidden="true">·</span> Mở <?= htmlspecialchars(date('d/m/Y H:i', strtotime($exam['start_time'])), ENT_QUOTES, 'UTF-8') ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <a href="index.php?action=prepare_exam&amp;exam_id=<?= (int) $exam['exam_id'] ?>" class="btn-admin-primary student-exam-action">
                                        Xem đề thi <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                    </a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="student-class-empty">Giáo viên chưa giao đề thi cho lớp này.</p>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../../partials/app-shell-end.php'; ?>
</body>
</html>
