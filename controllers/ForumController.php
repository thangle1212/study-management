<?php
class ForumController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    private function requireLogin() {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?action=login');
            exit;
        }
    }

    // --- 1. DANH SÁCH BÀI VIẾT ---
    public function index() {
        $this->requireLogin();
        
        $search = trim($_GET['search'] ?? '');
        $filterExam = (int) ($_GET['exam_id'] ?? 0);

        $query = "SELECT p.*, u.full_name, u.avatar, u.role, 
                         e.title AS exam_title, 
                         q.content AS question_content,
                         (SELECT COUNT(*) FROM forum_comments c WHERE c.post_id = p.post_id AND c.status = 'active') AS comment_count
                  FROM forum_posts p
                  JOIN users u ON p.user_id = u.user_id
                  LEFT JOIN exams e ON p.exam_id = e.exam_id
                  LEFT JOIN questions q ON p.question_id = q.question_id
                  WHERE p.status = 'active'";
        
        $params = [];
        if ($search !== '') {
            $query .= " AND (p.title LIKE ? OR p.content LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($filterExam > 0) {
            $query .= " AND p.exam_id = ?";
            $params[] = $filterExam;
        }

        $query .= " ORDER BY p.created_at DESC";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        // Lấy danh sách đề thi để hiển thị bộ lọc
        $exams = $this->pdo->query("SELECT exam_id, title FROM exams WHERE status = 'published' ORDER BY title ASC")->fetchAll();

        require __DIR__ . '/../views/forum/index.php';
    }

    // --- 2. CHI TIẾT BÀI VIẾT & BÌNH LUẬN ---
    public function detail() {
        $this->requireLogin();
        $postId = (int) ($_GET['post_id'] ?? 0);

        // Lấy bài viết
        $stmt = $this->pdo->prepare(
            "SELECT p.*, u.full_name, u.avatar, u.role, 
                    e.title AS exam_title, 
                    q.content AS question_content
             FROM forum_posts p
             JOIN users u ON p.user_id = u.user_id
             LEFT JOIN exams e ON p.exam_id = e.exam_id
             LEFT JOIN questions q ON p.question_id = q.question_id
             WHERE p.post_id = ? AND p.status = 'active'"
        );
        $stmt->execute([$postId]);
        $post = $stmt->fetch();

        if (!$post) {
            die('Bài viết không tồn tại hoặc đã bị ẩn.');
        }

        // Lấy danh sách bình luận
        $stmt = $this->pdo->prepare(
            "SELECT c.*, u.full_name, u.avatar, u.role
             FROM forum_comments c
             JOIN users u ON c.user_id = u.user_id
             WHERE c.post_id = ? AND c.status = 'active'
             ORDER BY c.created_at ASC"
        );
        $stmt->execute([$postId]);
        $comments = $stmt->fetchAll();

        require __DIR__ . '/../views/forum/detail.php';
    }

    // --- 3. TẠO BÀI VIẾT MỚI ---
    public function create() {
        $this->requireLogin();
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $examId = $_POST['exam_id'] !== '' ? (int)$_POST['exam_id'] : null;
            $questionId = $_POST['question_id'] !== '' ? (int)$_POST['question_id'] : null;
            $userId = (int)$_SESSION['user']['id'];

            if ($title === '' || $content === '') {
                $error = 'Vui lòng nhập đầy đủ tiêu đề và nội dung bài viết.';
            } else {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO forum_posts (user_id, exam_id, question_id, title, content) 
                     VALUES (?, ?, ?, ?, ?)"
                );
                if ($stmt->execute([$userId, $examId, $questionId, $title, $content])) {
                    header('Location: index.php?action=forum');
                    exit;
                } else {
                    $error = 'Có lỗi xảy ra, vui lòng thử lại.';
                }
            }
        }

        // Lấy danh sách đề thi và câu hỏi để làm liên kết
        $exams = $this->pdo->query("SELECT exam_id, title FROM exams WHERE status = 'published' ORDER BY title ASC")->fetchAll();
        $questions = $this->pdo->query(
            "SELECT q.question_id, q.content, e.title AS exam_title 
             FROM questions q
             JOIN exam_questions eq ON q.question_id = eq.question_id
             JOIN exams e ON eq.exam_id = e.exam_id
             ORDER BY e.title ASC, q.question_id ASC"
        )->fetchAll();

        require __DIR__ . '/../views/forum/create.php';
    }

    // --- 4. CHỈNH SỬA BÀI VIẾT ---
    public function edit() {
        $this->requireLogin();
        $postId = (int) ($_GET['post_id'] ?? 0);
        $userId = (int) $_SESSION['user']['id'];
        $userRole = $_SESSION['user']['role'];

        $stmt = $this->pdo->prepare("SELECT * FROM forum_posts WHERE post_id = ? AND status = 'active'");
        $stmt->execute([$postId]);
        $post = $stmt->fetch();

        if (!$post) {
            die('Bài viết không tồn tại.');
        }

        // Chỉ cho phép tác giả hoặc admin chỉnh sửa
        if ($post['user_id'] !== $userId && $userRole !== 'admin') {
            die('Bạn không có quyền chỉnh sửa bài viết này.');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $examId = $_POST['exam_id'] !== '' ? (int)$_POST['exam_id'] : null;
            $questionId = $_POST['question_id'] !== '' ? (int)$_POST['question_id'] : null;

            if ($title === '' || $content === '') {
                $error = 'Tiêu đề và nội dung không được bỏ trống.';
            } else {
                $stmt = $this->pdo->prepare(
                    "UPDATE forum_posts 
                     SET title = ?, content = ?, exam_id = ?, question_id = ? 
                     WHERE post_id = ?"
                );
                if ($stmt->execute([$title, $content, $examId, $questionId, $postId])) {
                    header("Location: index.php?action=forum_detail&post_id=$postId");
                    exit;
                } else {
                    $error = 'Không thể cập nhật bài viết.';
                }
            }
        }

        $exams = $this->pdo->query("SELECT exam_id, title FROM exams WHERE status = 'published' ORDER BY title ASC")->fetchAll();
        $questions = $this->pdo->query(
            "SELECT q.question_id, q.content, e.title AS exam_title 
             FROM questions q
             JOIN exam_questions eq ON q.question_id = eq.question_id
             JOIN exams e ON eq.exam_id = e.exam_id
             ORDER BY e.title ASC, q.question_id ASC"
        )->fetchAll();

        require __DIR__ . '/../views/forum/create.php'; // dùng chung view create nhưng điền sẵn thông tin
    }

    // --- 5. XÓA BÀI VIẾT (Đổi status thành 'deleted') ---
    public function delete() {
        $this->requireLogin();
        $postId = (int) ($_GET['post_id'] ?? 0);
        $userId = (int) $_SESSION['user']['id'];
        $userRole = $_SESSION['user']['role'];

        $stmt = $this->pdo->prepare("SELECT user_id FROM forum_posts WHERE post_id = ?");
        $stmt->execute([$postId]);
        $post = $stmt->fetch();

        if ($post) {
            if ($post['user_id'] === $userId || $userRole === 'admin') {
                $stmt = $this->pdo->prepare("UPDATE forum_posts SET status = 'deleted' WHERE post_id = ?");
                $stmt->execute([$postId]);
            }
        }
        header('Location: index.php?action=forum');
        exit;
    }

    // --- 6. THÊM BÌNH LUẬN ---
    public function commentCreate() {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postId = (int) ($_POST['post_id'] ?? 0);
            $content = trim($_POST['content'] ?? '');
            $userId = (int) $_SESSION['user']['id'];

            if ($content !== '' && $postId > 0) {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO forum_comments (post_id, user_id, content) 
                     VALUES (?, ?, ?)"
                );
                $stmt->execute([$postId, $userId, $content]);
            }
            header("Location: index.php?action=forum_detail&post_id=$postId");
            exit;
        }
        header('Location: index.php?action=forum');
        exit;
    }

    // --- 7. XÓA BÌNH LUẬN ---
    public function commentDelete() {
        $this->requireLogin();
        $commentId = (int) ($_GET['comment_id'] ?? 0);
        $postId = (int) ($_GET['post_id'] ?? 0);
        $userId = (int) $_SESSION['user']['id'];
        $userRole = $_SESSION['user']['role'];

        $stmt = $this->pdo->prepare("SELECT user_id FROM forum_comments WHERE comment_id = ?");
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch();

        if ($comment) {
            if ($comment['user_id'] === $userId || $userRole === 'admin') {
                $stmt = $this->pdo->prepare("UPDATE forum_comments SET status = 'deleted' WHERE comment_id = ?");
                $stmt->execute([$commentId]);
            }
        }
        header("Location: index.php?action=forum_detail&post_id=$postId");
        exit;
    }
}
?>