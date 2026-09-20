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
                    , ea.attempt_id, ea.student_id
             FROM exam_attempts ea
             JOIN users u ON u.user_id = ea.student_id
             WHERE ea.exam_id = ? AND ea.status = 'completed'
             ORDER BY u.full_name, ea.end_time, ea.attempt_id"
        );
        $stmt->execute([$examId]);
        $scores = $stmt->fetchAll();
        $attemptNumbers = [];
        foreach ($scores as &$score) {
            $studentId = (int) $score['student_id'];
            $attemptNumbers[$studentId] = ($attemptNumbers[$studentId] ?? 0) + 1;
            $score['attempt_number'] = $attemptNumbers[$studentId];
        }
        unset($score);

        $scoreDistribution = [];
        foreach ($scores as $score) {
            $scoreValue = number_format((float) $score['total_score'], 2, '.', '');
            $scoreDistribution[$scoreValue] = ($scoreDistribution[$scoreValue] ?? 0) + 1;
        }
        uksort($scoreDistribution, function ($left, $right) {
            return (float) $left <=> (float) $right;
        });

                $stmt = $this->pdo->prepare(
                        "SELECT COUNT(*) AS total_question_count,
                                        SUM(CASE
                                                WHEN EXISTS (
                                                        SELECT 1
                                                        FROM attempt_answers aa_wrong
                                                        WHERE aa_wrong.attempt_id = ea.attempt_id
                                                            AND aa_wrong.question_id = eq.question_id
                                                            AND aa_wrong.is_correct = FALSE
                                                ) THEN 0
                                                WHEN (
                                                        SELECT COUNT(*)
                                                        FROM attempt_answers aa_selected
                                                        WHERE aa_selected.attempt_id = ea.attempt_id
                                                            AND aa_selected.question_id = eq.question_id
                                                            AND aa_selected.answer_id IS NOT NULL
                                                ) = (
                                                        SELECT COUNT(*)
                                                        FROM answers a_correct
                                                        WHERE a_correct.question_id = eq.question_id
                                                            AND a_correct.is_correct = TRUE
                                                )
                                                AND (
                                                        SELECT COUNT(*)
                                                        FROM attempt_answers aa_answered
                                                        WHERE aa_answered.attempt_id = ea.attempt_id
                                                            AND aa_answered.question_id = eq.question_id
                                                            AND aa_answered.answer_id IS NOT NULL
                                                ) > 0 THEN 1
                                                ELSE 0
                                        END) AS correct_count
                         FROM exam_attempts ea
                         JOIN exam_questions eq ON eq.exam_id = ea.exam_id
                         WHERE ea.exam_id = ? AND ea.status = 'completed'"
                );
                $stmt->execute([$examId]);
                $chartStats = $stmt->fetch();
                $totalQuestionCount = (int) ($chartStats['total_question_count'] ?? 0);
                $correctCount = (int) ($chartStats['correct_count'] ?? 0);
                $wrongCount = max(0, $totalQuestionCount - $correctCount);
        require __DIR__ . '/../views/exams/statistics.php';
    }

    public function attemptDetail() {
        $this->requireLogin();
        $attemptId = (int) ($_GET['attempt_id'] ?? 0);
        $userId = (int) $_SESSION['user']['id'];

        $stmt = $this->pdo->prepare(
            "SELECT ea.*, e.title, e.teacher_id, u.full_name
             FROM exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             JOIN users u ON u.user_id = ea.student_id
             WHERE ea.attempt_id = ? AND ea.status = 'completed'
             AND (e.teacher_id = ? OR ea.student_id = ?)"
        );
        $stmt->execute([$attemptId, $userId, $userId]);
        $attempt = $stmt->fetch();
        if (!$attempt) {
            die('Không tìm thấy lượt nộp hoặc bạn không có quyền xem lượt nộp này.');
        }

        $stmt = $this->pdo->prepare(
            "SELECT eq.question_id, eq.order_index, q.content, q.question_type,
                    a.answer_id, a.content AS answer_content, a.is_correct
             FROM exam_questions eq
             JOIN questions q ON q.question_id = eq.question_id
             LEFT JOIN answers a ON a.question_id = q.question_id
             WHERE eq.exam_id = ?
             ORDER BY eq.order_index, q.question_id, a.order_index, a.answer_id"
        );
        $stmt->execute([(int) $attempt['exam_id']]);
        $questions = [];
        foreach ($stmt->fetchAll() as $row) {
            $questionId = (int) $row['question_id'];
            if (!isset($questions[$questionId])) {
                $questions[$questionId] = [
                    'question_id' => $questionId,
                    'content' => $row['content'],
                    'question_type' => $row['question_type'],
                    'answers' => [],
                    'selected_ids' => [],
                    'correct' => false
                ];
            }
            if ($row['answer_id'] !== null) {
                $questions[$questionId]['answers'][] = [
                    'answer_id' => (int) $row['answer_id'],
                    'content' => $row['answer_content'],
                    'is_correct' => (bool) $row['is_correct']
                ];
            }
        }

        $stmt = $this->pdo->prepare(
            "SELECT question_id, answer_id
             FROM attempt_answers
             WHERE attempt_id = ? AND answer_id IS NOT NULL"
        );
        $stmt->execute([$attemptId]);
        foreach ($stmt->fetchAll() as $selected) {
            $questionId = (int) $selected['question_id'];
            if (isset($questions[$questionId])) {
                $questions[$questionId]['selected_ids'][] = (int) $selected['answer_id'];
            }
        }

        foreach ($questions as &$question) {
            $correctIds = [];
            foreach ($question['answers'] as $answer) {
                if ($answer['is_correct']) {
                    $correctIds[] = $answer['answer_id'];
                }
            }
            $selectedIds = $question['selected_ids'];
            sort($correctIds);
            sort($selectedIds);
            $question['correct'] = $selectedIds === $correctIds && !empty($correctIds);
        }
        unset($question);

        require __DIR__ . '/../views/exams/attempt-detail.php';
    }

    public function studentStatistics() {
        $this->requireLogin();
        $studentId = (int) $_SESSION['user']['id'];

        $stmt = $this->pdo->prepare(
            "SELECT ea.attempt_id, e.title, ea.total_score, ea.end_time,
                    COUNT(DISTINCT CASE WHEN aa.is_correct = TRUE THEN aa.question_id END) AS correct_count,
                    COUNT(DISTINCT CASE WHEN aa.is_correct = FALSE THEN aa.question_id END) AS wrong_count
             FROM exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             LEFT JOIN attempt_answers aa ON aa.attempt_id = ea.attempt_id
             WHERE ea.student_id = ? AND ea.status = 'completed'
             GROUP BY ea.attempt_id, e.title, ea.total_score, ea.end_time
             ORDER BY ea.end_time ASC"
        );
        $stmt->execute([$studentId]);
        $attempts = $stmt->fetchAll();

        $totalCorrect = 0;
        $totalWrong = 0;
        foreach ($attempts as $attempt) {
            $totalCorrect += (int) $attempt['correct_count'];
            $totalWrong += (int) $attempt['wrong_count'];
        }

        require __DIR__ . '/../views/exams/student-statistics.php';
    }
}
?>