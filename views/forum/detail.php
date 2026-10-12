<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($post['title']); ?> - Diễn đàn EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --brand: #0d6efd; --ink: #17233c; --soft: #f4f8ff; }
        body { background: linear-gradient(135deg, #eaf4ff 0%, #f8fbff 45%, #fff 100%); color: var(--ink); min-height: 100vh; }
        .topbar { background: rgba(255,255,255,.9); border-bottom: 1px solid #e5edf8; backdrop-filter: blur(12px); }
        .brand { color: var(--brand); font-size: 1.35rem; }
        .avatar-img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .comment-avatar-img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }
        .role-badge { font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 8px; font-weight: 600; }
        .role-student { background-color: #e3f2fd; color: #0d6efd; }
        .role-teacher { background-color: #e8f5e9; color: #2e7d32; }
        .role-admin { background-color: #ffebee; color: #c62828; }
        .post-detail-card { border: 1px solid #e5edf8; border-radius: 16px; box-shadow: 0 8px 24px rgba(31,74,125,.06); background-color: #fff; }
        .comment-card { border: 1px solid #e5edf8; border-radius: 12px; background-color: #fff; }
        .relation-box { background-color: var(--soft); border-left: 4px solid var(--brand); border-radius: 4px 12px 12px 4px; }
        .comment-btn { border-radius: 10px; }
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

<div class="container py-4 py-md-5" style="max-width: 900px;">
    <!-- Bài viết -->
    <article class="card post-detail-card p-4 p-md-5 mb-4">
        <!-- Tác giả & metadata -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
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
                    $avatarUrl = !empty($post['avatar']) && file_exists($post['avatar']) ? $post['avatar'] : 'https://api.dicebear.com/7.x/adventurer/svg?seed=' . urlencode($post['full_name']);
                ?>
                <img src="<?php echo htmlspecialchars($avatarUrl); ?>" class="avatar-img" alt="Avatar">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <strong class="text-dark fs-5"><?php echo htmlspecialchars($post['full_name']); ?></strong>
                        <span class="role-badge <?php echo $roleClass; ?>"><?php echo $roleLabel; ?></span>
                    </div>
                    <small class="text-muted"><i class="fa-regular fa-clock me-1"></i><?php echo date('H:i - d/m/Y', strtotime($post['created_at'])); ?></small>
                </div>
            </div>

            <!-- Nút chức năng cho tác giả/admin -->
            <?php if ($post['user_id'] === (int)$_SESSION['user']['id'] || $_SESSION['user']['role'] === 'admin'): ?>
                <div class="d-flex gap-2">
                    <a href="index.php?action=forum_edit&post_id=<?php echo (int) $post['post_id']; ?>" class="btn btn-sm btn-outline-secondary rounded-3">
                        <i class="fa-solid fa-pen-to-square me-1"></i>Sửa
                    </a>
                    <a href="index.php?action=forum_delete&post_id=<?php echo (int) $post['post_id']; ?>" class="btn btn-sm btn-outline-danger rounded-3" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?')">
                        <i class="fa-solid fa-trash me-1"></i>Xóa
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tiêu đề & Nội dung -->
        <h1 class="h2 fw-bold text-dark mb-3 pb-3 border-bottom"><?php echo htmlspecialchars($post['title']); ?></h1>
        
        

        <div class="post-content text-dark fs-5 lh-lg mb-4" style="white-space: pre-line;">
            <?php echo htmlspecialchars($post['content']); ?>
        </div>

        <!-- Hashtag (nếu có) -->
        <?php if (!empty($post['hashtag'])): ?>
            <div class="mb-3 d-flex flex-wrap gap-1">
                <?php 
                    $tags = explode(' ', $post['hashtag']);
                    foreach ($tags as $tag) {
                        $tag = trim($tag);
                        if ($tag !== '') {
                            $formattedTag = (strpos($tag, '#') === 0) ? $tag : '#' . $tag;
                            echo '<span class="badge bg-light text-primary border border-light-subtle rounded-pill px-2 py-1 small">' . htmlspecialchars($formattedTag) . '</span>';
                        }
                    }
                ?>
            </div>
        <?php endif; ?>

        <!-- Liên kết kèm theo -->
        <?php if ($post['exam_title'] || $post['question_content']): ?>
            <div class="relation-box p-3 mb-2">
                <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-link me-1"></i> Liên kết đi kèm:</h6>
                <?php if ($post['exam_title']): ?>
                    <div class="mb-2">
                        <span class="fw-semibold text-muted">Đề thi liên quan:</span>
                        <a href="index.php?action=take_exam&exam_id=<?php echo (int) $post['exam_id']; ?>" class="text-primary fw-bold text-decoration-none">
                            <?php echo htmlspecialchars($post['exam_title']); ?> <i class="fa-solid fa-arrow-up-right-from-square ms-1 small"></i>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if ($post['question_content']): ?>
                    <div>
                        <span class="fw-semibold text-muted">Câu hỏi thắc mắc:</span>
                        <div class="bg-white p-3 rounded-3 mt-1 border border-light font-monospace small">
                            <?php echo htmlspecialchars($post['question_content']); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </article>

    

    <!-- Phần bình luận -->
    <section class="mb-5">
        <h3 class="h4 fw-bold text-dark mb-4"><i class="fa-regular fa-comment-dots me-2 text-primary"></i>Bình luận (<?php echo count($allComments ?? $comments); ?>)</h3>

        <!-- Danh sách các bình luận -->
        <div class="d-flex flex-column gap-3 mb-4">
            <?php if (empty($comments)): ?>
                <div class="card p-4 text-center text-muted border-0 shadow-sm rounded-4">
                    <p class="mb-0">Chưa có bình luận nào. Hãy chia sẻ suy nghĩ của bạn!</p>
                </div>
            <?php else: ?>
                <?php 
                $currentUserId = (int)$_SESSION['user']['id'];
                $currentUserRole = $_SESSION['user']['role'];
                foreach ($comments as $comment):
                    $commentRoleLabel = 'Học sinh';
                    $commentRoleClass = 'role-student';
                    if ($comment['role'] === 'teacher') {
                        $commentRoleLabel = 'Giáo viên';
                        $commentRoleClass = 'role-teacher';
                    } elseif ($comment['role'] === 'admin') {
                        $commentRoleLabel = 'Admin';
                        $commentRoleClass = 'role-admin';
                    }
                    $commentAvatarUrl = !empty($comment['avatar']) && file_exists($comment['avatar']) ? $comment['avatar'] : 'https://api.dicebear.com/7.x/adventurer/svg?seed=' . urlencode($comment['full_name']);
                ?>
                    <div class="card comment-card p-3 shadow-sm border-light mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <img src="<?php echo htmlspecialchars($commentAvatarUrl); ?>" class="comment-avatar-img" alt="Avatar">
                                <div>
                                    <span class="fw-bold text-dark small"><?php echo htmlspecialchars($comment['full_name']); ?></span>
                                    <span class="role-badge <?php echo $commentRoleClass; ?> ms-1" style="font-size: 0.65rem;"><?php echo $commentRoleLabel; ?></span>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?php echo date('H:i - d/m/Y', strtotime($comment['created_at'])); ?></div>
                                </div>
                            </div>

                            <!-- Nút xóa bình luận chính -->
                            <?php if ($comment['user_id'] === $currentUserId || $currentUserRole === 'admin'): ?>
                                <a href="index.php?action=forum_comment_delete&comment_id=<?php echo (int)$comment['comment_id']; ?>&post_id=<?php echo (int)$post['post_id']; ?>" class="btn btn-link text-danger p-0" onclick="return confirm('Bạn có chắc chắn muốn xóa bình luận này?')">
                                    <i class="fa-solid fa-trash-can small"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="comment-text text-dark ps-2 pt-1 mb-2" style="white-space: pre-line; font-size: 0.95rem;">
                            <?php echo htmlspecialchars($comment['content']); ?>
                        </div>

                        <!-- Nút trả lời bình luận chính -->
                        <div class="ps-2">
                            <button class="btn btn-link btn-sm p-0 text-primary text-decoration-none small fw-semibold" type="button" onclick="toggleReplyForm(<?php echo (int)$comment['comment_id']; ?>)">
                                <i class="fa-solid fa-reply me-1"></i> Trả lời
                            </button>
                        </div>

                        <!-- Nút xem câu trả lời (TikTok style with pagination) -->
                        <?php 
                        $replies = $repliesMap[$comment['comment_id']] ?? [];
                        $replyCount = count($replies);
                        if ($replyCount > 0):
                        ?>
                            <div class="mt-2">
                                <button class="btn btn-link btn-sm p-0 text-primary text-decoration-none small fw-semibold" type="button" onclick="toggleReplies(<?php echo (int)$comment['comment_id']; ?>, <?php echo $replyCount; ?>)">
                                    <i class="fa-solid fa-comment-dots me-1"></i><span id="reply-toggle-text-<?php echo (int)$comment['comment_id']; ?>">Xem <?php echo $replyCount; ?> câu trả lời</span>
                                </button>
                            </div>

                            <div id="replies-container-<?php echo (int)$comment['comment_id']; ?>" class="mt-2" style="display: none;">
                                <div class="ms-3 mt-2 ps-3 border-start border-3 border-primary-subtle d-flex flex-column gap-2">
                                    <?php foreach ($replies as $index => $reply): 
                                        $replyRoleLabel = 'Học sinh';
                                        $replyRoleClass = 'role-student';
                                        if ($reply['role'] === 'teacher') {
                                            $replyRoleLabel = 'Giáo viên';
                                            $replyRoleClass = 'role-teacher';
                                        } elseif ($reply['role'] === 'admin') {
                                            $replyRoleLabel = 'Admin';
                                            $replyRoleClass = 'role-admin';
                                        }
                                        $replyAvatarUrl = !empty($reply['avatar']) && file_exists($reply['avatar']) ? $reply['avatar'] : 'https://api.dicebear.com/7.x/adventurer/svg?seed=' . urlencode($reply['full_name']);
                                        $isHidden = $index >= 5;
                                    ?>
                                        <div class="card p-2 bg-light border-0 shadow-none reply-item-<?php echo (int)$comment['comment_id']; ?>" style="<?php echo $isHidden ? 'display: none;' : ''; ?>">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <img src="<?php echo htmlspecialchars($replyAvatarUrl); ?>" class="comment-avatar-img" style="width: 30px; height: 30px;" alt="Avatar">
                                                    <div>
                                                        <span class="fw-bold text-dark small">
                                                            <?php echo htmlspecialchars($reply['full_name']); ?>
                                                            <?php if (!empty($reply['reply_target_name'])): ?>
                                                                <span class="text-primary fw-bold mx-1">▷</span> <?php echo htmlspecialchars($reply['reply_target_name']); ?>
                                                            <?php endif; ?>
                                                        </span>
                                                        <span class="role-badge <?php echo $replyRoleClass; ?> ms-1" style="font-size: 0.6rem;"><?php echo $replyRoleLabel; ?></span>
                                                        <div class="text-muted" style="font-size: 0.7rem;"><?php echo date('H:i - d/m/Y', strtotime($reply['created_at'])); ?></div>
                                                    </div>
                                                </div>

                                                <?php if ($reply['user_id'] === $currentUserId || $currentUserRole === 'admin'): ?>
                                                    <a href="index.php?action=forum_comment_delete&comment_id=<?php echo (int)$reply['comment_id']; ?>&post_id=<?php echo (int)$post['post_id']; ?>" class="btn btn-link text-danger p-0" onclick="return confirm('Bạn có chắc chắn muốn xóa phản hồi này?')">
                                                        <i class="fa-solid fa-trash-can small"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <div class="comment-text text-dark ps-2 pt-1" style="white-space: pre-line; font-size: 0.9rem;">
                                                <?php echo htmlspecialchars($reply['content']); ?>
                                            </div>

                                            <!-- Nút trả lời cho phản hồi này -->
                                            <div class="ps-2 mt-1">
                                                <button class="btn btn-link btn-sm p-0 text-primary text-decoration-none small fw-semibold" type="button" onclick="toggleReplyForm(<?php echo (int)$reply['comment_id']; ?>)">
                                                    <i class="fa-solid fa-reply me-1"></i> Trả lời
                                                </button>
                                            </div>

                                            <!-- Form trả lời ẩn/hiện cho phản hồi này -->
                                            <div id="reply-form-<?php echo (int)$reply['comment_id']; ?>" class="mt-2 ps-2" style="display: none;">
                                                <form method="post" action="index.php?action=forum_comment_create">
                                                    <input type="hidden" name="post_id" value="<?php echo (int) $post['post_id']; ?>">
                                                    <input type="hidden" name="parent_id" value="<?php echo (int) $reply['comment_id']; ?>">
                                                    <div class="input-group">
                                                        <input type="text" name="content" class="form-control form-control-sm rounded-start-3" placeholder="Viết phản hồi tới <?php echo htmlspecialchars($reply['full_name']); ?>..." required>
                                                        <button class="btn btn-sm btn-primary rounded-end-3 px-3" type="submit"><i class="fa-solid fa-paper-plane me-1"></i>Gửi</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="ms-3 ps-3 mt-2 d-flex justify-content-between align-items-center">
                                    <?php if ($replyCount > 5): ?>
                                        <button class="btn btn-link btn-sm p-0 text-primary text-decoration-none small fw-semibold" type="button" id="load-more-btn-<?php echo (int)$comment['comment_id']; ?>" onclick="loadMoreReplies(<?php echo (int)$comment['comment_id']; ?>)">
                                            Xem thêm phản hồi (<span id="load-more-count-<?php echo (int)$comment['comment_id']; ?>"><?php echo ($replyCount - 5); ?></span>)
                                        </button>
                                    <?php else: ?>
                                        <div></div>
                                    <?php endif; ?>
                                    <button class="btn btn-link btn-sm p-0 text-muted text-decoration-none small fw-semibold" type="button" onclick="toggleReplies(<?php echo (int)$comment['comment_id']; ?>, <?php echo $replyCount; ?>)">
                                        Ẩn ^
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Form trả lời ẩn/hiện cho bình luận chính -->
                        <div id="reply-form-<?php echo (int)$comment['comment_id']; ?>" class="mt-3 ps-2" style="display: none;">
                            <form method="post" action="index.php?action=forum_comment_create">
                                <input type="hidden" name="post_id" value="<?php echo (int) $post['post_id']; ?>">
                                <input type="hidden" name="parent_id" value="<?php echo (int) $comment['comment_id']; ?>">
                                <div class="input-group">
                                    <input type="text" name="content" class="form-control form-control-sm rounded-start-3" placeholder="Viết phản hồi tới <?php echo htmlspecialchars($comment['full_name']); ?>..." required>
                                    <button class="btn btn-sm btn-primary rounded-end-3 px-3" type="submit"><i class="fa-solid fa-paper-plane me-1"></i>Gửi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Form viết bình luận -->
        <div class="card p-4 border-0 shadow-sm rounded-4">
            <h5 class="fw-bold text-dark mb-3">Viết bình luận của bạn</h5>
            <form method="post" action="index.php?action=forum_comment_create">
                <input type="hidden" name="post_id" value="<?php echo (int) $post['post_id']; ?>">
                <div class="mb-3">
                    <textarea name="content" class="form-control rounded-3" rows="3" placeholder="Nhập bình luận tại đây..." required></textarea>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary comment-btn px-4 py-2"><i class="fa-solid fa-paper-plane me-1"></i>Gửi bình luận</button>
                </div>
            </form>
        </div>
    </section>
</div>
<script>
function toggleReplyForm(commentId) {
    var formDiv = document.getElementById('reply-form-' + commentId);
    if (formDiv.style.display === 'none' || formDiv.style.display === '') {
        formDiv.style.display = 'block';
        var input = formDiv.querySelector('input');
        if (input) input.focus();
    } else {
        formDiv.style.display = 'none';
    }
}

function toggleReplies(commentId, totalCount) {
    var container = document.getElementById('replies-container-' + commentId);
    var textSpan = document.getElementById('reply-toggle-text-' + commentId);
    if (container.style.display === 'none' || container.style.display === '') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

function loadMoreReplies(commentId) {
    var items = document.querySelectorAll('.reply-item-' + commentId);
    var hiddenItems = Array.from(items).filter(function(item) {
        return item.style.display === 'none';
    });
    
    var toShow = hiddenItems.slice(0, 5);
    toShow.forEach(function(item) {
        item.style.display = 'block';
    });

    var remaining = hiddenItems.length - toShow.length;
    var btn = document.getElementById('load-more-btn-' + commentId);
    if (btn) {
        if (remaining <= 0) {
            btn.style.display = 'none';
        } else {
            var countSpan = document.getElementById('load-more-count-' + commentId);
            if (countSpan) countSpan.innerText = remaining;
        }
    }
}
</script>
</body>
</html>