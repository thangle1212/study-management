<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($post) ? 'Chỉnh sửa bài viết' : 'Đăng bài viết mới'; ?> - Diễn đàn EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --brand: #0d6efd; --ink: #17233c; --soft: #f4f8ff; }
        body { background: linear-gradient(135deg, #eaf4ff 0%, #f8fbff 45%, #fff 100%); color: var(--ink); min-height: 100vh; }
        .topbar { background: rgba(255,255,255,.9); border-bottom: 1px solid #e5edf8; backdrop-filter: blur(12px); }
        .brand { color: var(--brand); font-size: 1.35rem; }
        .form-card { border: 1px solid #e5edf8; border-radius: 16px; box-shadow: 0 8px 24px rgba(31,74,125,.06); background-color: #fff; }
        .form-label { font-weight: 600; color: var(--ink); }
        .form-control, .form-select { border-radius: 10px; border: 1px solid #ced4da; padding: 0.75rem 1rem; }
        .form-control:focus, .form-select:focus { border-color: var(--brand); box-shadow: 0 0 0 0.25rem rgba(13,110,253,.15); }
    </style>
</head>
<body>
<nav class="navbar topbar sticky-top">
    <div class="container py-2">
        <a class="navbar-brand brand fw-bold" href="index.php?action=dashboard"><i class="fa-solid fa-graduation-cap me-2"></i>EduTest</a>
        <div class="d-flex align-items-center gap-3">
            <a href="index.php?action=forum" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-left me-1"></i>Về diễn đàn</a>
            <span class="d-none d-md-inline text-muted small">Xin chào, <strong><?php echo htmlspecialchars($_SESSION['user']['full_name']); ?></strong></span>
            <a href="index.php?action=logout" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-right-from-bracket me-1"></i>Đăng xuất</a>
        </div>
    </div>
</nav>

<div class="container py-4 py-md-5" style="max-width: 800px;">
    <div class="card form-card p-4 p-md-5">
        <h1 class="h3 fw-bold text-dark mb-4 text-center">
            <i class="fa-solid <?php echo isset($post) ? 'fa-pen-to-square' : 'fa-pen-nib'; ?> text-primary me-2"></i>
            <?php echo isset($post) ? 'Chỉnh sửa bài viết' : 'Tạo bài viết mới'; ?>
        </h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger rounded-3" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo isset($post) ? 'index.php?action=forum_edit&post_id=' . (int)$post['post_id'] : 'index.php?action=forum_create'; ?>">
            <!-- Tiêu đề bài viết -->
            <div class="mb-4">
                <label for="title" class="form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" placeholder="VD: Giúp em giải câu 5 đề thi học kì I" required value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>">
                <div class="form-text">Tiêu đề ngắn gọn, tóm tắt được nội dung câu hỏi/thảo luận.</div>
            </div>

            <!-- Nội dung bài viết -->
            <div class="mb-4">
                <label for="content" class="form-label">Nội dung chi tiết <span class="text-danger">*</span></label>
                <textarea class="form-control" id="content" name="content" rows="8" placeholder="Viết nội dung thắc mắc hoặc thông tin cần thảo luận chi tiết tại đây..." required><?php echo htmlspecialchars($post['content'] ?? ''); ?></textarea>
            </div>

            <div class="row">
                <!-- Liên kết đề thi -->
                <div class="col-md-6 mb-4">
                    <label for="exam_id" class="form-label">Liên kết đề thi (Không bắt buộc)</label>
                    <select class="form-select" id="exam_id" name="exam_id">
                        <option value="">-- Chọn đề thi liên quan --</option>
                        <?php foreach ($exams as $exam): ?>
                            <option value="<?php echo (int) $exam['exam_id']; ?>" <?php echo (isset($post) && (int)$post['exam_id'] === (int)$exam['exam_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($exam['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Liên kết câu hỏi cụ thể -->
                <div class="col-md-6 mb-4">
                    <label for="question_id" class="form-label">Liên kết câu hỏi (Không bắt buộc)</label>
                    <select class="form-select" id="question_id" name="question_id">
                        <option value="">-- Chọn câu hỏi liên quan --</option>
                        <?php foreach ($questions as $question): ?>
                            <?php 
                                // Rút gọn nội dung câu hỏi để hiển thị trong select
                                $shortContent = mb_strlen($question['content']) > 40 ? mb_substr($question['content'], 0, 40) . '...' : $question['content'];
                            ?>
                            <option value="<?php echo (int) $question['question_id']; ?>" <?php echo (isset($post) && (int)$post['question_id'] === (int)$question['question_id']) ? 'selected' : ''; ?>>
                                [<?php echo htmlspecialchars($question['exam_title']); ?>] - <?php echo htmlspecialchars($shortContent); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Các nút hành động -->
            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="index.php?action=forum" class="btn btn-outline-secondary rounded-3 px-4 py-2">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary rounded-3 px-4 py-2">
                    <i class="fa-solid fa-paper-plane me-1"></i> <?php echo isset($post) ? 'Cập nhật' : 'Đăng bài viết'; ?>
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>