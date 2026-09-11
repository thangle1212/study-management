<?php
class ExamController {
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

    public function take() {
        $this->requireLogin();
        $examId = (int) ($_GET['exam_id'] ?? 0);

        $stmt = $this->pdo->prepare(
            "SELECT e.*, COUNT(eq.id) AS question_count
             FROM exams e
             LEFT JOIN exam_questions eq ON eq.exam_id = e.exam_id
             WHERE e.exam_id = ? AND e.status = 'published'
             GROUP BY e.exam_id"
        );
        $stmt->execute([$examId]);
        $exam = $stmt->fetch();

        if (!$exam) {
            die('Đề thi không tồn tại hoặc chưa được công khai.');
        }

        $stmt = $this->pdo->prepare(
            "SELECT q.question_id, q.content, q.question_type, a.answer_id, a.content AS answer_content
             FROM exam_questions eq
             JOIN questions q ON q.question_id = eq.question_id
             JOIN answers a ON a.question_id = q.question_id
             WHERE eq.exam_id = ?
             ORDER BY eq.order_index, q.question_id, a.order_index, a.answer_id"
        );
        $stmt->execute([$examId]);
        $questions = [];
        foreach ($stmt->fetchAll() as $row) {
            $questionId = $row['question_id'];
            if (!isset($questions[$questionId])) {
                $questions[$questionId] = [
                    'question_id' => $questionId,
                    'content' => $row['content'],
                    'question_type' => $row['question_type'],
                    'answers' => []
                ];
            }
            $questions[$questionId]['answers'][] = [
                'answer_id' => $row['answer_id'],
                'content' => $row['answer_content']
            ];
        }

        require __DIR__ . '/../views/exams/take.php';
    }

    public function submit() {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=dashboard');
            exit;
        }

        $examId = (int) ($_POST['exam_id'] ?? 0);
        $answers = $_POST['answers'] ?? [];
        $studentId = (int) $_SESSION['user']['id'];

        $stmt = $this->pdo->prepare(
            "SELECT eq.question_id, eq.score_weight, q.question_type
             FROM exam_questions eq
             JOIN questions q ON q.question_id = eq.question_id
             WHERE eq.exam_id = ?"
        );
        $stmt->execute([$examId]);
        $examQuestions = $stmt->fetchAll();
        if (!$examQuestions) {
            die('Đề thi chưa có câu hỏi.');
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO exam_attempts (exam_id, student_id, end_time, status, total_score)
                 VALUES (?, ?, NOW(), 'completed', 0)"
            );
            $stmt->execute([$examId, $studentId]);
            $attemptId = $this->pdo->lastInsertId();
            $insertAnswer = $this->pdo->prepare(
                "INSERT INTO attempt_answers (attempt_id, question_id, answer_id, is_correct)
                 VALUES (?, ?, ?, ?)"
            );
            $score = 0;

            foreach ($examQuestions as $examQuestion) {
                $questionId = (int) $examQuestion['question_id'];
                $selectedIds = $answers[$questionId] ?? [];
                $selectedIds = is_array($selectedIds) ? $selectedIds : [$selectedIds];
                $selectedIds = array_values(array_filter(array_map('intval', $selectedIds)));
                $correctStmt = $this->pdo->prepare(
                    "SELECT answer_id FROM answers WHERE question_id = ? AND is_correct = TRUE"
                );
                $correctStmt->execute([$questionId]);
                $correctIds = array_map('intval', $correctStmt->fetchAll(PDO::FETCH_COLUMN));
                sort($correctIds);
                $checkedIds = $selectedIds;
                sort($checkedIds);
                $isCorrect = $checkedIds === $correctIds && !empty($correctIds);

                if ($selectedIds) {
                    foreach ($selectedIds as $selectedId) {
                        $insertAnswer->execute([$attemptId, $questionId, $selectedId, $isCorrect ? 1 : 0]);
                    }
                } else {
                    $insertAnswer->execute([$attemptId, $questionId, null, 0]);
                }
                if ($isCorrect) {
                    $score += (float) $examQuestion['score_weight'];
                }
            }

            $updateAttempt = $this->pdo->prepare(
                "UPDATE exam_attempts SET total_score = ? WHERE attempt_id = ?"
            );
            $updateAttempt->execute([$score, $attemptId]);
            $this->pdo->commit();
            header('Location: index.php?action=exam_result&attempt_id=' . $attemptId);
            exit;
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function result() {
        $this->requireLogin();
        $attemptId = (int) ($_GET['attempt_id'] ?? 0);
        $stmt = $this->pdo->prepare(
            "SELECT ea.*, e.title, u.full_name
             FROM exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             JOIN users u ON u.user_id = ea.student_id
             WHERE ea.attempt_id = ? AND (ea.student_id = ? OR e.teacher_id = ?)"
        );
        $userId = (int) $_SESSION['user']['id'];
        $stmt->execute([$attemptId, $userId, $userId]);
        $attempt = $stmt->fetch();
        if (!$attempt) {
            die('Không tìm thấy kết quả bài làm.');
        }
        require __DIR__ . '/../views/exams/result.php';
    }

    public function statistics() {
        $this->requireLogin();
        $examId = (int) ($_GET['exam_id'] ?? 0);
        $userId = (int) $_SESSION['user']['id'];
        $stmt = $this->pdo->prepare(
            "SELECT e.exam_id, e.title
             FROM exams e
             WHERE e.exam_id = ? AND e.teacher_id = ?"
        );
        $stmt->execute([$examId, $userId]);
        $exam = $stmt->fetch();
        if (!$exam) {
            die('Bạn không có quyền xem thống kê đề thi này.');
        }

        $stmt = $this->pdo->prepare(
            "SELECT u.full_name, ea.total_score, ea.end_time
             FROM exam_attempts ea
             JOIN users u ON u.user_id = ea.student_id
             WHERE ea.exam_id = ? AND ea.status = 'completed'
             ORDER BY ea.end_time DESC"
        );
        $stmt->execute([$examId]);
        $scores = $stmt->fetchAll();

        $stmt = $this->pdo->prepare(
            "SELECT q.question_id, q.content, COUNT(ea.attempt_id) AS wrong_count
             FROM exam_questions eq
             JOIN questions q ON q.question_id = eq.question_id
             LEFT JOIN attempt_answers aa ON aa.question_id = q.question_id AND aa.is_correct = FALSE
             LEFT JOIN exam_attempts ea ON ea.attempt_id = aa.attempt_id AND ea.exam_id = ? AND ea.status = 'completed'
             WHERE eq.exam_id = ?
             GROUP BY q.question_id, q.content
             ORDER BY wrong_count DESC, q.question_id"
        );
        $stmt->execute([$examId, $examId]);
        $wrongQuestions = $stmt->fetchAll();
        require __DIR__ . '/../views/exams/statistics.php';
    }
}
?>