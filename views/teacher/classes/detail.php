<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f7fc">
    <title><?= htmlspecialchars($class['class_name'], ENT_QUOTES, 'UTF-8') ?> - EduTest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-app">
<?php $appPageTitle = $class['class_name']; $appActiveAction = 'teacher_classes'; require __DIR__ . '/../../partials/app-shell-start.php'; ?>
<main>
            <div class="admin-page-heading">
                <div>
                    <p class="admin-eyebrow">Chi tiết lớp học</p>
                    <h1><?= htmlspecialchars($class['class_name'], ENT_QUOTES, 'UTF-8') ?></h1>
                    <p>Mã lớp <strong><?= htmlspecialchars($class['class_code'], ENT_QUOTES, 'UTF-8') ?></strong> · <?= (int) $class['student_count'] ?> học sinh</p>
                </div>
                <a href="index.php?action=teacher_classes" class="btn-admin-outline"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Danh sách lớp</a>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> class-alert" role="<?= $flash['type'] === 'success' ? 'status' : 'alert' ?>">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-2" aria-hidden="true"></i>
                    <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="class-card-list">
                <section id="members" class="admin-panel class-card" aria-labelledby="members-heading">
                    <div class="class-card-heading">
                        <div class="class-title">
                            <span class="class-card-icon" aria-hidden="true"><i class="fa-solid fa-users"></i></span>
                            <div>
                                <h2 id="members-heading">Danh sách học sinh</h2>
                                <span class="class-code">Thành viên hiện tại của lớp</span>
                            </div>
                        </div>
                        <span class="user-count-badge"><i class="fa-solid fa-user-graduate" aria-hidden="true"></i><?= count($students) ?> học sinh</span>
                    </div>
                    <div class="class-card-body class-member-management">
                        <div class="class-roster">
                            <?php if (!$students): ?>
                                <p class="class-roster-empty">Lớp chưa có học sinh. Hãy thêm học sinh để bắt đầu quản lý.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Học sinh</th>
                                                <th scope="col">Email</th>
                                                <th scope="col">Ngày tham gia</th>
                                                <th scope="col" class="text-end">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($students as $student): ?>
                                                <tr>
                                                    <th scope="row"><?= htmlspecialchars($student['full_name'], ENT_QUOTES, 'UTF-8') ?></th>
                                                    <td><?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($student['joined_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td class="text-end">
                                                        <form action="index.php?action=teacher_class_remove_student" method="POST" onsubmit="return confirm('Bỏ học sinh này khỏi lớp? Lịch sử làm bài và điểm số vẫn được giữ nguyên.');">
                                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                            <input type="hidden" name="class_id" value="<?= (int) $class['class_id'] ?>">
                                                            <input type="hidden" name="student_id" value="<?= (int) $student['user_id'] ?>">
                                                            <button type="submit" class="btn-admin-danger btn-admin-compact">
                                                                <i class="fa-solid fa-user-minus" aria-hidden="true"></i>Bỏ khỏi lớp
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="class-edit-form">
                            <p class="mb-1 fw-semibold">Cần thêm học sinh?</p>
                            <p class="text-secondary small mb-2">Chọn từ các tài khoản học sinh đang hoạt động.</p>
                            <button type="button" class="btn-admin-primary class-save-button" data-bs-toggle="modal" data-bs-target="#addStudentModal" <?= !$availableStudents ? 'disabled' : '' ?>>
                                <i class="fa-solid fa-user-plus" aria-hidden="true"></i> Thêm học sinh vào lớp
                            </button>
                            <?php if (!$availableStudents): ?>
                                <p class="class-roster-empty mt-2">Không còn học sinh đang hoạt động để thêm.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <section id="assign-exam" class="admin-panel class-card" aria-labelledby="exam-heading">
                    <div class="class-card-heading">
                        <div class="class-title">
                            <span class="class-card-icon" aria-hidden="true"><i class="fa-solid fa-file-circle-check"></i></span>
                            <div>
                                <h2 id="exam-heading">Giao đề thi cho lớp</h2>
                                <span class="class-code">Chỉ đề có ít nhất một câu hỏi mới được gán</span>
                            </div>
                        </div>
                    </div>
                    <div class="class-card-body class-exam-assignment">
                        <form action="index.php?action=teacher_class_assign_exam" method="POST" class="class-edit-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="class_id" value="<?= (int) $class['class_id'] ?>">
                            <label for="exam_id" class="form-label fw-semibold">Đề thi của bạn</label>
                            <select class="form-select" id="exam_id" name="exam_id" required>
                                <option value="">Chọn đề thi</option>
                                <?php foreach ($exams as $exam): ?>
                                    <?php
                                    $examClassId = $exam['class_id'] === null ? null : (int) $exam['class_id'];
                                    $isAssignedHere = $examClassId === (int) $class['class_id'];
                                    $canAssignExam = (int) $exam['question_count'] > 0
                                        && ($examClassId === null || $isAssignedHere);
                                    ?>
                                    <option value="<?= (int) $exam['exam_id'] ?>" <?= $canAssignExam ? '' : 'disabled' ?>>
                                        <?= htmlspecialchars($exam['title'], ENT_QUOTES, 'UTF-8') ?>
                                        (<?= (int) $exam['question_count'] ?> câu<?= $isAssignedHere ? ', đã gán cho lớp này' : ($examClassId !== null ? ', đã gán lớp khác' : '') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!$exams): ?>
                                <p class="class-roster-empty">Bạn chưa có đề thi. Hãy tạo đề trước khi giao cho lớp.</p>
                            <?php endif; ?>
                            <button type="submit" class="btn-admin-primary class-save-button">
                                <i class="fa-solid fa-link" aria-hidden="true"></i> Giao đề thi
                            </button>
                        </form>
                        <p class="class-roster-empty">Theo cấu trúc hiện tại, mỗi đề thi chỉ có thể giao cho một lớp.</p>
                    </div>
                </section>
            </div>
<?php require __DIR__ . '/../../partials/app-shell-end.php'; ?>

<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="index.php?action=teacher_class_add_student" method="POST">
                <div class="modal-header">
                    <div>
                        <p class="admin-eyebrow mb-1">Danh sách học sinh khả dụng</p>
                        <h2 class="modal-title fs-5" id="addStudentModalLabel">Thêm học sinh vào lớp</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="class_id" value="<?= (int) $class['class_id'] ?>">
                    <label for="available_student_id" class="form-label fw-semibold">Chọn học sinh</label>
                    <select class="form-select" id="available_student_id" name="student_id" required>
                        <option value="">Chọn tài khoản học sinh</option>
                        <?php foreach ($availableStudents as $student): ?>
                            <option value="<?= (int) $student['user_id'] ?>">
                                <?= htmlspecialchars($student['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                (<?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="form-text mb-0">Học sinh đã thuộc lớp này không hiển thị trong danh sách.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-admin-outline" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn-admin-primary">
                        <i class="fa-solid fa-user-plus" aria-hidden="true"></i> Thêm vào lớp
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
