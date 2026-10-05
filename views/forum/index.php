<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Diễn đàn thảo luận - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --brand: #0d6efd; --ink: #17233c; --soft: #f4f8ff; }
        body { background: linear-gradient(135deg, #eaf4ff 0%, #f8fbff 45%, #fff 100%); color: var(--ink); min-height: 100vh; }
        .topbar { background: rgba(255,255,255,.9); border-bottom: 1px solid #e5edf8; backdrop-filter: blur(12px); }
        .brand { color: var(--brand); font-size: 1.35rem; }
        .forum-header { border: 0; border-radius: 22px; background: linear-gradient(135deg, #0d6efd, #438df4); color: #fff; box-shadow: 0 16px 36px rgba(13,110,253,.18); }
        .post-card { border: 1px solid #e5edf8; border-radius: 16px; box-shadow: 0 8px 24px rgba(31,74,125,.06); transition: transform .2s ease, box-shadow .2s ease; background-color: #fff; }
        .post-card:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(31,74,125,.1); }
        .avatar-img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .role-badge { font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 8px; font-weight: 600; }
        .role-student { background-color: #e3f2fd; color: #0d6efd; }
        .role-teacher { background-color: #e8f5e9; color: #2e7d32; }
        .role-admin { background-color: #ffebee; color: #c62828; }
        .search-box { border-radius: 30px; padding-left: 20px; border: 1px solid #ced4da; }
        .search-btn { border-radius: 30px; }
        .filter-select { border-radius: 12px; }
    </style>
</head>
<body>
<nav class="navbar topbar sticky-top">
    <div class="container py-2">
        <a class="navbar-brand brand fw-bold" href="index.php?action=dashboard"><i class="fa-solid fa-graduation-cap me-2"></i>EduTest</a>
        <div class="d-flex align-items-center gap-3">
            <a href="index.php?action=dashboard" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-chart-line me-1"></i>Trang tổng quan</a>
            <span class="d-none d-md-inline text-muted small">Xin chào, <strong><?php echo htmlspecialchars($_SESSION['user']['full_name']); ?></strong></span>
            <a href="index.php?action=logout" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-right-from-bracket me-1"></i>Đăng xuất</a>
        </div>
    </div>
</nav>

<div class="container py-4 py-md-5">
    <!-- Header của diễn đàn -->
    <section class="forum-header p-4 p-md-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-md-8">
                <div class="small opacity-75 text-uppercase mb-2"><i class="fa-solid fa-comments me-1"></i> Diễn đàn thảo luận khoa học</div>
                <h1 class="h2 fw-bold mb-2">Hỏi đáp & Trao đổi bài tập</h1>
                <p class="mb-0 opacity-75">Nơi học sinh và giáo viên chia sẻ kiến thức, thảo luận về các đề thi và giải đáp thắc mắc bài học.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="index.php?action=forum_create" class="btn btn-light text-primary fw-bold rounded-pill px-4 py-3 shadow-sm">
                    <i class="fa-solid fa-pen-nib me-2"></i>Tạo bài viết mới
                </a>
            </div>
        </div>
    </section>

    <!-- Bộ lọc & Tìm kiếm -->
    <div class="card p-3 mb-4 border-0 shadow-sm rounded-4">
        <form method="get" action="index.php" class="row g-3 align-items-center">
            <input type="hidden" name="action" value="forum">
            <div class="col-md-5">
                <div class="input-group">
                    <input type="text" name="search" class="form-control search-box" placeholder="Tìm kiếm bài viết..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary search-btn px-4" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </div>
            <div class="col-md-5">
                <select name="exam_id" class="form-select filter-select py-2" onchange="this.form.submit()">
                    <option value="">-- Tất cả đề thi --</option>
                    <?php foreach ($exams as $exam): ?>
                        <option value="<?php echo (int) $exam['exam_id']; ?>" <?php echo $filterExam === (int) $exam['exam_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($exam['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <a href="index.php?action=forum" class="btn btn-outline-secondary rounded-3 py-2"><i class="fa-solid fa-rotate-left me-1"></i>Đặt lại</a>
            </div>
        </form>
    </div>

    <!-- Danh sách bài viết -->
    <div class="row g-4">
        <?php if (empty($posts)): ?>
            <div class="col-12">
                <div class="card p-5 text-center text-muted border-0 shadow-sm rounded-4">
                    <i class="fa-regular fa-comments fs-1 mb-3 text-secondary"></i>
                    <h4 class="fw-bold text-dark">Chưa có bài viết nào</h4>
                    <p class="mb-0">Hãy là người đầu tiên bắt đầu cuộc trò chuyện!</p>
                    <div class="mt-3">
                        <a href="index.php?action=forum_create" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-plus me-1"></i> Đăng bài viết ngay</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="col-12">
                    <div class="card post-card p-4">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <?php 
                                    $roleLabel = 'Học sinh';
                                    $roleClass = 'role-student';
                                    if ($post['role'] === 'teacher') {
                                        $roleLabel = 'Giáo viên';
                                        $roleClass = 'role-teacher';
                                    } elseif ($post['role'] === 'admin') {
                                        $roleLabel = 'Admin';
                                        $roleClass = 'role-admin';
                                    }
                                    
                                    // Tạo avatar mặc định nếu không có avatar thực tế
                                    $avatarUrl = !empty($post['avatar']) && file_exists($post['avatar']) ? $post['avatar'] : 'https://api.dicebear.com/7.x/adventurer/svg?seed=' . urlencode($post['full_name']);
                                ?>
                                <img src="<?php echo htmlspecialchars($avatarUrl); ?>" class="avatar-img" alt="Avatar">
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <strong class="text-dark"><?php echo htmlspecialchars($post['full_name']); ?></strong>
                                        <span class="role-badge <?php echo $roleClass; ?>"><?php echo $roleLabel; ?></span>
                                    </div>
                                    <small class="text-muted"><i class="fa-regular fa-clock me-1"></i><?php echo date('H:i - d/m/Y', strtotime($post['created_at'])); ?></small>
                                </div>
                            </div>
                            
                            <!-- Nhãn đề thi/câu hỏi liên quan (nếu có) -->
                            <div class="d-flex flex-wrap gap-2">
                                <?php if ($post['exam_title']): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                                        <i class="fa-solid fa-file-invoice me-1"></i> Đề: <?php echo htmlspecialchars($post['exam_title']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($post['question_content']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2" title="<?php echo htmlspecialchars($post['question_content']); ?>">
                                        <i class="fa-solid fa-circle-question me-1"></i> Câu hỏi liên quan
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tiêu đề & Nội dung bài viết -->
                        <h3 class="h5 fw-bold mb-2">
                            <a href="index.php?action=forum_detail&post_id=<?php echo (int) $post['post_id']; ?>" class="text-dark text-decoration-none hover-primary">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h3>
                        <p class="text-muted mb-3">
                            <?php 
                                $plainText = strip_tags($post['content']);
                                echo htmlspecialchars(mb_strlen($plainText) > 180 ? mb_substr($plainText, 0, 180) . '...' : $plainText); 
                            ?>
                        </p>

                        <!-- Thống kê & Action -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <span class="text-primary small fw-semibold"><i class="fa-regular fa-comment-dots me-1"></i> <?php echo (int) $post['comment_count']; ?> bình luận</span>
                            <a href="index.php?action=forum_detail&post_id=<?php echo (int) $post['post_id']; ?>" class="btn btn-sm btn-outline-primary rounded-3 px-3">
                                Đọc thêm & Thảo luận <i class="fa-solid fa-angle-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>