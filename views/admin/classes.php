<?php
$membershipIds = [];
$teacherIds = array_fill_keys(array_map('intval', array_column($teachers, 'user_id')), true);
foreach ($memberships as $membership) {
    $membershipIds[$membership['class_id']][(int) $membership['student_id']] = true;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f7fc">
    <title>Quản lý lớp học - EduTest</title>
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
        <a class="admin-brand" href="index.php?action=admin_dashboard">
            <span class="admin-brand-mark" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
            <span>EduTest</span>
        </a>
        <nav class="admin-nav" aria-label="Điều hướng quản trị">
            <a class="admin-nav-link" href="index.php?action=admin_dashboard"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i><span>Tổng quan</span></a>
            <a class="admin-nav-link" href="index.php?action=admin_users"><i class="fa-solid fa-users-gear" aria-hidden="true"></i><span>Tài khoản</span></a>
            <a class="admin-nav-link active" href="index.php?action=admin_classes" aria-current="page"><i class="fa-solid fa-school" aria-hidden="true"></i><span>Lớp học</span></a>
            <a class="admin-nav-link" href="index.php?action=forum"><i class="fa-regular fa-comments" aria-hidden="true"></i><span>Diễn đàn</span></a>
        </nav>
        <div class="admin-sidebar-bottom">
            <span class="admin-account-avatar" aria-hidden="true"><i class="fa-solid fa-user-shield"></i></span>
            <div class="admin-account-copy">
                <div class="admin-account-name"><?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="admin-account-role">Quản trị viên</div>
            </div>
            <a class="admin-logout" href="index.php?action=logout" aria-label="Đăng xuất" title="Đăng xuất"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i></a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <p class="admin-breadcrumb">EduTest <span aria-hidden="true">/</span> <strong>Lớp học</strong></p>
            <a href="index.php?action=forum" class="admin-topbar-link"><i class="fa-regular fa-comments me-1" aria-hidden="true"></i> Diễn đàn</a>
        </header>

        <main class="admin-content">
            <div class="admin-page-heading">
                <div>
                    <p class="admin-eyebrow">Quản trị đào tạo</p>
                    <h1>Quản lý lớp học</h1>
                    <p>Tạo lớp, phân công giáo viên và quản lý danh sách học sinh.</p>
                </div>
                <a href="index.php?action=admin_dashboard" class="btn-admin-outline"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i> Tổng quan</a>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> class-alert" role="<?= $flash['type'] === 'success' ? 'status' : 'alert' ?>">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-2" aria-hidden="true"></i>
                    <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <section class="class-overview" aria-label="Tổng quan lớp học">
                <article class="admin-metric">
                    <div class="admin-metric-head"><span class="admin-metric-label">Tổng số lớp</span><span class="admin-metric-icon" aria-hidden="true"><i class="fa-solid fa-school"></i></span></div>
                    <div class="admin-metric-value"><?= number_format(count($classes), 0, ',', '.') ?></div>
                    <div class="admin-metric-note">Lớp học trên hệ thống</div>
                </article>
                <article class="admin-metric">
                    <div class="admin-metric-head"><span class="admin-metric-label">Giáo viên hoạt động</span><span class="admin-metric-icon" aria-hidden="true"><i class="fa-solid fa-chalkboard-user"></i></span></div>
                    <div class="admin-metric-value"><?= number_format(count($teachers), 0, ',', '.') ?></div>
                    <div class="admin-metric-note">Có thể phụ trách lớp</div>
                </article>
                <article class="admin-metric">
                    <div class="admin-metric-head"><span class="admin-metric-label">Học sinh hoạt động</span><span class="admin-metric-icon" aria-hidden="true"><i class="fa-solid fa-user-graduate"></i></span></div>
                    <div class="admin-metric-value"><?= number_format(count($students), 0, ',', '.') ?></div>
                    <div class="admin-metric-note">Có thể gán vào lớp</div>
                </article>
            </section>

            <details class="admin-panel admin-create-details class-create-details" <?= $errors ? 'open' : '' ?>>
                <summary class="admin-create-summary">
                    <span class="admin-create-icon" aria-hidden="true"><i class="fa-solid fa-plus"></i></span>
                    <span>Tạo lớp mới</span>
                </summary>
                <div class="admin-create-form">
                    <?php if ($errors && (isset($errors['class_name']) || isset($errors['class_code']) || isset($errors['teacher_id']))): ?>
                        <div class="alert alert-danger py-2" id="classCreateErrors" role="alert" tabindex="-1">
                            <?php foreach (['class_name', 'class_code', 'teacher_id'] as $field): ?>
                                <?php if (isset($errors[$field])): ?>
                                    <div><?= htmlspecialchars($errors[$field], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <form action="index.php?action=admin_classes" method="POST" class="row g-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="operation" value="create">
                        <div class="col-md-5">
                            <label for="new-class-name" class="form-label">Tên lớp</label>
                            <input id="new-class-name" name="class_name" type="text" class="form-control" maxlength="100" required value="<?= htmlspecialchars($formData['class_name'], ENT_QUOTES, 'UTF-8') ?>" <?= isset($errors['class_name']) ? 'aria-invalid="true" aria-describedby="new-class-name-error"' : '' ?>>
                            <?php if (isset($errors['class_name'])): ?>
                                <div class="invalid-feedback d-block" id="new-class-name-error"><?= htmlspecialchars($errors['class_name'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <label for="new-class-code" class="form-label">Mã lớp</label>
                            <input id="new-class-code" name="class_code" type="text" class="form-control text-uppercase" minlength="4" maxlength="10" pattern="[A-Za-z0-9]{4,10}" autocomplete="off" required value="<?= htmlspecialchars($formData['class_code'], ENT_QUOTES, 'UTF-8') ?>" <?= isset($errors['class_code']) ? 'aria-invalid="true" aria-describedby="new-class-code-help new-class-code-error"' : 'aria-describedby="new-class-code-help"' ?>>
                            <?php if (isset($errors['class_code'])): ?>
                                <div class="invalid-feedback d-block" id="new-class-code-error"><?= htmlspecialchars($errors['class_code'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                            <div class="form-text" id="new-class-code-help">4–10 chữ cái hoặc chữ số.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="new-class-teacher" class="form-label">Giáo viên phụ trách</label>
                            <select id="new-class-teacher" name="teacher_id" class="form-select" required <?= isset($errors['teacher_id']) ? 'aria-invalid="true" aria-describedby="new-class-teacher-error"' : '' ?>>
                                <option value="">Chọn giáo viên</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= (int) $teacher['user_id'] ?>" <?= (string) $teacher['user_id'] === (string) $formData['teacher_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($teacher['full_name'] . ' · ' . $teacher['email'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['teacher_id'])): ?>
                                <div class="invalid-feedback d-block" id="new-class-teacher-error"><?= htmlspecialchars($errors['teacher_id'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12">
                            <?php if (!$teachers): ?>
                                <p class="text-danger small mb-2">Chưa có giáo viên đang hoạt động. Hãy tạo hoặc kích hoạt tài khoản giáo viên trước.</p>
                            <?php endif; ?>
                            <button type="submit" class="btn-admin-primary" <?= !$teachers ? 'disabled' : '' ?>>
                                <i class="fa-solid fa-school" aria-hidden="true"></i> Tạo lớp
                            </button>
                        </div>
                    </form>
                </div>
            </details>

            <section class="class-list-section" aria-labelledby="classes-heading">
                <div class="user-section-heading">
                    <div>
                        <p class="admin-eyebrow">Danh sách lớp</p>
                        <h2 id="classes-heading">Lớp học hiện có</h2>
                        <p>Cập nhật người phụ trách và phân bổ học sinh vào một hoặc nhiều lớp.</p>
                    </div>
                    <span class="user-count-badge"><i class="fa-solid fa-school" aria-hidden="true"></i><?= count($classes) ?> lớp</span>
                </div>

                <?php if (!$classes): ?>
                    <div class="admin-panel user-empty-state">
                        <span class="user-empty-icon" aria-hidden="true"><i class="fa-regular fa-folder-open"></i></span>
                        <h3>Chưa có lớp học nào</h3>
                        <p>Tạo lớp đầu tiên để bắt đầu phân công giáo viên và học sinh.</p>
                    </div>
                <?php else: ?>
                    <div class="class-card-list">
                        <?php foreach ($classes as $class): ?>
                            <?php
                            $classId = (int) $class['class_id'];
                            $classMembers = $studentsByClass[$classId] ?? [];
                            $availableStudentCount = 0;
                            foreach ($students as $student) {
                                if (!isset($membershipIds[$classId][(int) $student['user_id']])) {
                                    $availableStudentCount++;
                                }
                            }
                            $classErrorName = 'class_name_' . $classId;
                            $classErrorTeacher = 'teacher_id_' . $classId;
                            ?>
                            <article class="admin-panel class-card">
                                <div class="class-card-heading">
                                    <div class="class-title">
                                        <span class="class-card-icon" aria-hidden="true"><i class="fa-solid fa-school"></i></span>
                                        <div>
                                            <h3><?= htmlspecialchars($class['class_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                            <span class="class-code"><i class="fa-solid fa-ticket" aria-hidden="true"></i> Mã lớp: <strong><?= htmlspecialchars($class['class_code'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                                        </div>
                                    </div>
                                    <span class="user-count-badge"><i class="fa-solid fa-user-graduate" aria-hidden="true"></i><?= (int) $class['student_count'] ?> học sinh</span>
                                </div>

                                <div class="class-card-body">
                                    <form action="index.php?action=admin_classes" method="POST" class="class-edit-form">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="operation" value="update">
                                        <input type="hidden" name="class_id" value="<?= $classId ?>">
                                        <div>
                                            <label for="class-name-<?= $classId ?>" class="form-label">Tên lớp</label>
                                            <input id="class-name-<?= $classId ?>" name="class_name" type="text" class="form-control" maxlength="100" required value="<?= htmlspecialchars($class['class_name'], ENT_QUOTES, 'UTF-8') ?>" <?= isset($errors[$classErrorName]) ? 'aria-invalid="true"' : '' ?>>
                                            <?php if (isset($errors[$classErrorName])): ?>
                                                <div class="invalid-feedback d-block"><?= htmlspecialchars($errors[$classErrorName], ENT_QUOTES, 'UTF-8') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <label for="class-teacher-<?= $classId ?>" class="form-label">Giáo viên phụ trách</label>
                                            <select id="class-teacher-<?= $classId ?>" name="teacher_id" class="form-select" required <?= isset($errors[$classErrorTeacher]) ? 'aria-invalid="true"' : '' ?>>
                                                <?php if (!isset($teacherIds[(int) $class['teacher_id']])): ?>
                                                    <option value="" selected disabled>Giáo viên hiện tại không còn hoạt động</option>
                                                <?php endif; ?>
                                                <?php foreach ($teachers as $teacher): ?>
                                                    <option value="<?= (int) $teacher['user_id'] ?>" <?= (int) $teacher['user_id'] === (int) $class['teacher_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($teacher['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if (isset($errors[$classErrorTeacher])): ?>
                                                <div class="invalid-feedback d-block"><?= htmlspecialchars($errors[$classErrorTeacher], ENT_QUOTES, 'UTF-8') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <button type="submit" class="btn-admin-outline class-save-button"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Lưu lớp</button>
                                    </form>

                                    <div class="class-roster">
                                        <div class="class-roster-heading">
                                            <h4><i class="fa-solid fa-users" aria-hidden="true"></i> Học sinh trong lớp</h4>
                                            <span><?= count($classMembers) ?> thành viên</span>
                                        </div>
                                        <?php if (!$classMembers): ?>
                                            <p class="class-roster-empty">Lớp chưa có học sinh. Dùng biểu mẫu bên dưới để gán học sinh.</p>
                                        <?php else: ?>
                                            <ul class="class-member-list">
                                                <?php foreach ($classMembers as $member): ?>
                                                    <li class="class-member">
                                                        <span class="class-member-avatar" aria-hidden="true"><i class="fa-solid fa-user-graduate"></i></span>
                                                        <span class="class-member-copy">
                                                            <strong><?= htmlspecialchars($member['full_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                            <small><?= htmlspecialchars($member['email'], ENT_QUOTES, 'UTF-8') ?><?= $member['status'] !== 'active' ? ' · Tài khoản không hoạt động' : '' ?></small>
                                                        </span>
                                                        <form action="index.php?action=admin_classes" method="POST" onsubmit="return confirm('Bỏ học sinh này khỏi lớp? Lịch sử làm bài vẫn được giữ nguyên.');">
                                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                            <input type="hidden" name="operation" value="remove_student">
                                                            <input type="hidden" name="class_id" value="<?= $classId ?>">
                                                            <input type="hidden" name="student_id" value="<?= (int) $member['student_id'] ?>">
                                                            <button type="submit" class="class-remove-button" aria-label="Bỏ <?= htmlspecialchars($member['full_name'], ENT_QUOTES, 'UTF-8') ?> khỏi lớp" title="Bỏ khỏi lớp">
                                                                <i class="fa-solid fa-user-minus" aria-hidden="true"></i>
                                                            </button>
                                                        </form>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>

                                        <?php if ($students): ?>
                                            <form action="index.php?action=admin_classes" method="POST" class="class-assign-form">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="operation" value="assign_student">
                                                <input type="hidden" name="class_id" value="<?= $classId ?>">
                                                <label for="student-<?= $classId ?>" class="form-label">Gán học sinh vào lớp</label>
                                                <div class="input-group">
                                                    <select id="student-<?= $classId ?>" name="student_id" class="form-select" required>
                                                        <option value="">Chọn học sinh</option>
                                                        <?php foreach ($students as $student): ?>
                                                            <?php $isAssigned = isset($membershipIds[$classId][(int) $student['user_id']]); ?>
                                                            <option value="<?= (int) $student['user_id'] ?>" <?= $isAssigned ? 'disabled' : '' ?>>
                                                                <?= htmlspecialchars($student['full_name'] . ' · ' . $student['email'] . ($isAssigned ? ' · Đã gán' : ''), ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="btn-admin-primary" <?= $availableStudentCount === 0 ? 'disabled' : '' ?>>
                                                        <i class="fa-solid fa-user-plus" aria-hidden="true"></i> Gán học sinh
                                                    </button>
                                                </div>
                                            </form>
                                        <?php else: ?>
                                            <p class="class-roster-empty mb-0">Chưa có học sinh đang hoạt động để gán vào lớp.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>
<script>
    const invalidField = document.querySelector('[aria-invalid="true"]');
    if (invalidField) invalidField.focus();
</script>
</body>
</html>
