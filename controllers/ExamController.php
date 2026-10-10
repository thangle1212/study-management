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
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    private function requireStudent() {
        $this->requireLogin();
        if (($_SESSION['user']['role'] ?? '') !== 'student') {
            http_response_code(403);
            exit('Chỉ học sinh mới được làm bài thi.');
        }
    }

    private function requirePostWithCsrf() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ. Hãy tải lại trang và thử lại.');
        }
    }

    private function sendJson($payload, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function ensureAutomaticallyGradableExam($examId) {
        $stmt = $this->pdo->prepare(
            "SELECT q.question_id, q.question_type,
                    COUNT(a.answer_id) AS answer_count,
                    COALESCE(SUM(CASE WHEN a.is_correct = TRUE THEN 1 ELSE 0 END), 0) AS correct_count
             FROM exam_questions eq
             JOIN questions q ON q.question_id = eq.question_id
             LEFT JOIN answers a ON a.question_id = q.question_id
             WHERE eq.exam_id = ?
             GROUP BY q.question_id, q.question_type"
        );
        $stmt->execute([$examId]);
        $questions = $stmt->fetchAll();
        if (!$questions) {
            return 'Đề thi chưa có câu hỏi.';
        }

        foreach ($questions as $question) {
            if (!in_array($question['question_type'], ['single_choice', 'multiple_choice'], true)) {
                return 'Đề thi có câu hỏi chưa được hỗ trợ chấm tự động. Chỉ hỗ trợ câu hỏi một đáp án và nhiều đáp án.';
            }
            if ((int) $question['answer_count'] === 0 || (int) $question['correct_count'] === 0) {
                return 'Đề thi có câu hỏi chưa được cấu hình đáp án đúng.';
            }
            if ($question['question_type'] === 'single_choice' && (int) $question['correct_count'] !== 1) {
                return 'Câu hỏi một đáp án phải có đúng một đáp án đúng.';
            }
        }

        return null;
    }

    private function saveAttemptAnswers($attemptId, $examId, $answers) {
        $answers = is_array($answers) ? $answers : [];
        $stmt = $this->pdo->prepare(
            "SELECT eq.question_id, a.answer_id
             FROM exam_questions eq
             LEFT JOIN answers a ON a.question_id = eq.question_id
             WHERE eq.exam_id = ?"
        );
        $stmt->execute([$examId]);
        $validAnswers = [];
        foreach ($stmt->fetchAll() as $row) {
            $questionId = (int) $row['question_id'];
            $validAnswers[$questionId] = $validAnswers[$questionId] ?? [];
            if ($row['answer_id'] !== null) {
                $validAnswers[$questionId][] = (int) $row['answer_id'];
            }
        }

        $delete = $this->pdo->prepare("DELETE FROM attempt_answers WHERE attempt_id = ?");
        $delete->execute([$attemptId]);
        $insert = $this->pdo->prepare(
            "INSERT INTO attempt_answers (attempt_id, question_id, answer_id, is_correct)
             VALUES (?, ?, ?, 0)"
        );
        foreach ($validAnswers as $questionId => $validIds) {
            $selectedIds = $answers[$questionId] ?? [];
            $selectedIds = is_array($selectedIds) ? $selectedIds : [$selectedIds];
            $selectedIds = array_values(array_unique(array_filter(array_map('intval', $selectedIds))));
            $selectedIds = array_values(array_intersect($selectedIds, $validIds));
            if (!$selectedIds) {
                $insert->execute([$attemptId, $questionId, null]);
                continue;
            }
            foreach ($selectedIds as $answerId) {
                $insert->execute([$attemptId, $questionId, $answerId]);
            }
        }
    }

    private function ensureUnansweredQuestions($attemptId, $examId) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO attempt_answers (attempt_id, question_id, answer_id, is_correct)
             SELECT ?, DISTINCT_QUESTIONS.question_id, NULL, 0
             FROM (
                 SELECT DISTINCT question_id
                 FROM exam_questions
                 WHERE exam_id = ?
             ) AS DISTINCT_QUESTIONS
             WHERE NOT EXISTS (
                   SELECT 1
                   FROM attempt_answers aa
                   WHERE aa.attempt_id = ? AND aa.question_id = DISTINCT_QUESTIONS.question_id
               )"
        );
        $stmt->execute([$attemptId, $examId, $attemptId]);
    }

    private function snapshotCorrectAnswers($attemptId, $examId) {
        $stmt = $this->pdo->prepare(
            "INSERT IGNORE INTO attempt_correct_answers (attempt_id, question_id, answer_id)
             SELECT ?, eq.question_id, a.answer_id
             FROM exam_questions eq
             JOIN answers a ON a.question_id = eq.question_id
             WHERE eq.exam_id = ? AND a.is_correct = TRUE"
        );
        $stmt->execute([$attemptId, $examId]);
    }

    private function finalizeAttempt($attemptId, $studentId, $answers, $reason) {
        $stmt = $this->pdo->prepare(
                "SELECT ea.attempt_id, ea.exam_id, ea.start_time,
                    COALESCE(ea.duration_minutes_snapshot, e.duration_minutes) AS duration_minutes
             FROM exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             WHERE ea.attempt_id = ? AND ea.student_id = ? AND ea.status = 'in_progress'
             FOR UPDATE"
        );
        $stmt->execute([$attemptId, $studentId]);
        $attempt = $stmt->fetch();
        if (!$attempt) {
            return false;
        }

        if (time() >= strtotime($attempt['start_time']) + ((int) $attempt['duration_minutes'] * 60)) {
            $reason = 'time_expired';
        }
        if ($answers !== null) {
            $this->saveAttemptAnswers($attemptId, (int) $attempt['exam_id'], $answers);
        } else {
            $this->ensureUnansweredQuestions($attemptId, (int) $attempt['exam_id']);
        }
        $this->snapshotCorrectAnswers($attemptId, (int) $attempt['exam_id']);

        $stmt = $this->pdo->prepare(
            "SELECT question_id, MAX(score_weight) AS score_weight
             FROM exam_questions
             WHERE exam_id = ?
             GROUP BY question_id"
        );
        $stmt->execute([(int) $attempt['exam_id']]);
        $examQuestions = $stmt->fetchAll();
        if (!$examQuestions) {
            throw new RuntimeException('Đề thi chưa có câu hỏi.');
        }
        $gradingError = $this->ensureAutomaticallyGradableExam((int) $attempt['exam_id']);
        if ($gradingError !== null) {
            throw new RuntimeException($gradingError);
        }

        $selectedStmt = $this->pdo->prepare(
            "SELECT answer_id FROM attempt_answers
             WHERE attempt_id = ? AND question_id = ? AND answer_id IS NOT NULL"
        );
        $correctStmt = $this->pdo->prepare(
            "SELECT answer_id FROM attempt_correct_answers
             WHERE attempt_id = ? AND question_id = ?"
        );
        $markAnswers = $this->pdo->prepare(
            "UPDATE attempt_answers SET is_correct = ?
             WHERE attempt_id = ? AND question_id = ?"
        );
        $score = 0;
        foreach ($examQuestions as $examQuestion) {
            $questionId = (int) $examQuestion['question_id'];
            $selectedStmt->execute([$attemptId, $questionId]);
            $selectedIds = array_map('intval', $selectedStmt->fetchAll(PDO::FETCH_COLUMN));
            $correctStmt->execute([$attemptId, $questionId]);
            $correctIds = array_map('intval', $correctStmt->fetchAll(PDO::FETCH_COLUMN));
            sort($selectedIds);
            sort($correctIds);
            $isCorrect = $selectedIds === $correctIds && !empty($correctIds);
            $markAnswers->execute([$isCorrect ? 1 : 0, $attemptId, $questionId]);
            if ($isCorrect) {
                $score += (float) $examQuestion['score_weight'];
            }
        }

        $reviewStatus = $reason === 'violation_limit' ? 'pending' : 'not_required';
        $update = $this->pdo->prepare(
            "UPDATE exam_attempts
             SET total_score = ?, end_time = NOW(), status = 'completed',
                 submit_reason = ?, review_status = ?
             WHERE attempt_id = ? AND status = 'in_progress'"
        );
        $update->execute([$score, $reason, $reviewStatus, $attemptId]);
        return true;
    }

    public function prepare() {
        $this->requireStudent();
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
        $gradingError = $this->ensureAutomaticallyGradableExam($examId);
        if ($gradingError !== null) {
            die($gradingError);
        }

        $studentId = (int) $_SESSION['user']['id'];
        $historyStmt = $this->pdo->prepare(
            "SELECT ea.attempt_id, ea.total_score, ea.start_time, ea.end_time,
                    ea.submit_reason, ea.review_status,
                    COUNT(DISTINCT CASE WHEN aa.is_correct = TRUE THEN aa.question_id END) AS correct_count,
                    COUNT(DISTINCT eq.question_id) AS question_count
             FROM exam_attempts ea
             LEFT JOIN attempt_answers aa ON aa.attempt_id = ea.attempt_id
             JOIN exam_questions eq ON eq.exam_id = ea.exam_id
             WHERE ea.exam_id = ? AND ea.student_id = ?
               AND ea.status IN ('completed', 'voided')
             GROUP BY ea.attempt_id, ea.total_score, ea.start_time, ea.end_time,
                      ea.submit_reason, ea.review_status
             ORDER BY ea.end_time DESC, ea.attempt_id DESC"
        );
        $historyStmt->execute([$examId, $studentId]);
        $attemptHistory = $historyStmt->fetchAll();
        $completedAttemptCount = count($attemptHistory);
        $activeAttemptStmt = $this->pdo->prepare(
            "SELECT attempt_id
             FROM exam_attempts
             WHERE exam_id = ? AND student_id = ? AND status = 'in_progress'
             ORDER BY attempt_id DESC LIMIT 1"
        );
        $activeAttemptStmt->execute([$examId, $studentId]);
        $activeAttemptId = $activeAttemptStmt->fetchColumn();

        $csrfToken = $_SESSION['csrf_token'];
        require __DIR__ . '/../views/exams/prepare.php';
    }

    public function start() {
        $this->requireStudent();
        $this->requirePostWithCsrf();
        $examId = (int) ($_POST['exam_id'] ?? 0);
        $studentId = (int) $_SESSION['user']['id'];
        $stmt = $this->pdo->prepare(
            "SELECT exam_id, duration_minutes, anti_cheat_enabled,
                    violation_limit, violation_action
             FROM exams WHERE exam_id = ? AND status = 'published'"
        );
        $stmt->execute([$examId]);
        $exam = $stmt->fetch();
        if (!$exam) {
            http_response_code(404);
            exit('Đề thi không tồn tại hoặc chưa được công khai.');
        }
        $gradingError = $this->ensureAutomaticallyGradableExam($examId);
        if ($gradingError !== null) {
            http_response_code(422);
            exit($gradingError);
        }

        $attemptStmt = $this->pdo->prepare(
            "SELECT attempt_id FROM exam_attempts
             WHERE exam_id = ? AND student_id = ? AND status = 'in_progress'
             ORDER BY attempt_id DESC LIMIT 1"
        );
        $attemptStmt->execute([$examId, $studentId]);
        $attemptId = $attemptStmt->fetchColumn();
        if (!$attemptId) {
            $attemptStmt = $this->pdo->prepare(
                "INSERT INTO exam_attempts
                    (exam_id, student_id, status, duration_minutes_snapshot,
                     anti_cheat_enabled_snapshot, violation_limit_snapshot,
                     violation_action_snapshot)
                 VALUES (?, ?, 'in_progress', ?, ?, ?, ?)"
            );
            $attemptStmt->execute([
                $examId,
                $studentId,
                (int) $exam['duration_minutes'],
                (int) $exam['anti_cheat_enabled'],
                (int) $exam['violation_limit'],
                $exam['violation_action']
            ]);
            $attemptId = $this->pdo->lastInsertId();
        }
        $this->snapshotCorrectAnswers((int) $attemptId, $examId);
        header('Location: index.php?action=take_exam&attempt_id=' . (int) $attemptId);
        exit;
    }

    public function take() {
        $this->requireStudent();
        $attemptId = (int) ($_GET['attempt_id'] ?? 0);
        $studentId = (int) $_SESSION['user']['id'];
        $stmt = $this->pdo->prepare(
            "SELECT ea.attempt_id, ea.exam_id, ea.start_time, ea.status,
                    e.title,
                    COALESCE(ea.duration_minutes_snapshot, e.duration_minutes) AS duration_minutes,
                    COALESCE(ea.anti_cheat_enabled_snapshot, e.anti_cheat_enabled) AS anti_cheat_enabled,
                    COALESCE(ea.violation_limit_snapshot, e.violation_limit) AS violation_limit,
                    COALESCE(ea.violation_action_snapshot, e.violation_action) AS violation_action,
                    COUNT(eq.id) AS question_count
             FROM exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             LEFT JOIN exam_questions eq ON eq.exam_id = e.exam_id
             WHERE ea.attempt_id = ? AND ea.student_id = ?
             GROUP BY ea.attempt_id"
        );
        $stmt->execute([$attemptId, $studentId]);
        $exam = $stmt->fetch();
        if (!$exam || $exam['status'] !== 'in_progress') {
            http_response_code(403);
            exit('Lượt thi không hợp lệ hoặc đã được nộp.');
        }
        $gradingError = $this->ensureAutomaticallyGradableExam((int) $exam['exam_id']);
        if ($gradingError !== null) {
            http_response_code(422);
            exit($gradingError);
        }

        if (time() >= strtotime($exam['start_time']) + ((int) $exam['duration_minutes'] * 60)) {
            $this->pdo->beginTransaction();
            try {
                $this->finalizeAttempt($attemptId, $studentId, null, 'time_expired');
                $this->pdo->commit();
            } catch (Throwable $exception) {
                $this->pdo->rollBack();
                throw $exception;
            }
            header('Location: index.php?action=exam_result&attempt_id=' . $attemptId);
            exit;
        }

        $stmt = $this->pdo->prepare(
            "SELECT q.question_id, q.content, q.question_type, a.answer_id, a.content AS answer_content
             FROM exam_questions eq
             JOIN questions q ON q.question_id = eq.question_id
             JOIN answers a ON a.question_id = q.question_id
             WHERE eq.exam_id = ?
             ORDER BY eq.order_index, q.question_id, a.order_index, a.answer_id"
        );
        $stmt->execute([(int) $exam['exam_id']]);
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

        $savedAnswers = [];
        $stmt = $this->pdo->prepare(
            "SELECT question_id, answer_id FROM attempt_answers
             WHERE attempt_id = ? AND answer_id IS NOT NULL"
        );
        $stmt->execute([$attemptId]);
        foreach ($stmt->fetchAll() as $savedAnswer) {
            $questionId = (int) $savedAnswer['question_id'];
            $savedAnswers[$questionId][] = (int) $savedAnswer['answer_id'];
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM violation_logs WHERE attempt_id = ?");
        $stmt->execute([$attemptId]);
        $violationCount = (int) $stmt->fetchColumn();
        $csrfToken = $_SESSION['csrf_token'];

        require __DIR__ . '/../views/exams/take.php';
    }

    public function autosave() {
        $this->requireStudent();
        $this->requirePostWithCsrf();
        $attemptId = (int) ($_POST['attempt_id'] ?? 0);
        $studentId = (int) $_SESSION['user']['id'];
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "SELECT ea.exam_id, ea.start_time,
                    COALESCE(ea.duration_minutes_snapshot, e.duration_minutes) AS duration_minutes
                 FROM exam_attempts ea JOIN exams e ON e.exam_id = ea.exam_id
                 WHERE ea.attempt_id = ? AND ea.student_id = ?
                   AND ea.status = 'in_progress' FOR UPDATE"
            );
            $stmt->execute([$attemptId, $studentId]);
            $attempt = $stmt->fetch();
            if (!$attempt) {
                $this->pdo->rollBack();
                $this->sendJson(['error' => 'Lượt thi không hợp lệ hoặc đã nộp.'], 403);
            }
            if (time() >= strtotime($attempt['start_time']) + ((int) $attempt['duration_minutes'] * 60)) {
                $this->finalizeAttempt($attemptId, $studentId, $_POST['answers'] ?? [], 'time_expired');
                $this->pdo->commit();
                $this->sendJson(['saved' => false, 'forced_submit' => true, 'reason' => 'time_expired']);
            }
            $this->saveAttemptAnswers($attemptId, (int) $attempt['exam_id'], $_POST['answers'] ?? []);
            $this->pdo->commit();
            $this->sendJson(['saved' => true]);
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function submit() {
        $this->requireStudent();
        $this->requirePostWithCsrf();
        $attemptId = (int) ($_POST['attempt_id'] ?? 0);
        $studentId = (int) $_SESSION['user']['id'];
        $answers = array_key_exists('answers', $_POST) ? $_POST['answers'] : null;
        $this->pdo->beginTransaction();
        try {
            $completed = $this->finalizeAttempt($attemptId, $studentId, $answers, 'manual');
            if (!$completed) {
                $this->pdo->rollBack();
                http_response_code(403);
                die('Lượt thi không hợp lệ hoặc đã được nộp.');
            }
            $this->pdo->commit();
            header('Location: index.php?action=exam_result&attempt_id=' . $attemptId);
            exit;
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function logViolation() {
        $this->requireStudent();
        $this->requirePostWithCsrf();

        $attemptId = (int) ($_POST['attempt_id'] ?? 0);
        $violationType = $_POST['violation_type'] ?? '';
        $allowedTypes = ['tab_hidden', 'window_blur', 'context_menu', 'copy', 'cut', 'paste', 'shortcut'];
        if (!$attemptId || !in_array($violationType, $allowedTypes, true)) {
            $this->sendJson(['error' => 'Loại vi phạm không hợp lệ.'], 400);
        }

        $details = substr(trim((string) ($_POST['details'] ?? '')), 0, 255);
        $studentId = (int) $_SESSION['user']['id'];
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "SELECT ea.exam_id, ea.status,
                    COALESCE(ea.anti_cheat_enabled_snapshot, e.anti_cheat_enabled) AS anti_cheat_enabled,
                    COALESCE(ea.violation_limit_snapshot, e.violation_limit) AS violation_limit,
                    COALESCE(ea.violation_action_snapshot, e.violation_action) AS violation_action
                 FROM exam_attempts ea JOIN exams e ON e.exam_id = ea.exam_id
                 WHERE ea.attempt_id = ? AND ea.student_id = ? FOR UPDATE"
            );
            $stmt->execute([$attemptId, $studentId]);
            $attempt = $stmt->fetch();
            if (!$attempt || $attempt['status'] !== 'in_progress') {
                $this->pdo->rollBack();
                $this->sendJson(['error' => 'Lượt thi không còn hoạt động.'], 403);
            }
            if (!(int) $attempt['anti_cheat_enabled']) {
                $this->pdo->commit();
                $this->sendJson(['count' => 0, 'forced_submit' => false]);
            }

            $stmt = $this->pdo->prepare(
                "INSERT INTO violation_logs (attempt_id, violation_type, details)
                 VALUES (?, ?, ?)"
            );
            $stmt->execute([$attemptId, $violationType, $details]);
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM violation_logs WHERE attempt_id = ?");
            $stmt->execute([$attemptId]);
            $violationCount = (int) $stmt->fetchColumn();
            $limit = max(1, (int) $attempt['violation_limit']);
            $forcedSubmit = $violationCount >= $limit && $attempt['violation_action'] === 'auto_submit';
            if ($forcedSubmit) {
                $this->finalizeAttempt($attemptId, $studentId, null, 'violation_limit');
            }
            $this->pdo->commit();
            $this->sendJson([
                'count' => $violationCount,
                'limit' => $limit,
                'forced_submit' => $forcedSubmit,
                'attempt_id' => $attemptId
            ]);
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function updateSettings() {
        $this->requireLogin();
        $this->requirePostWithCsrf();
        $examId = (int) ($_POST['exam_id'] ?? 0);
        $teacherId = (int) $_SESSION['user']['id'];
        if (($_SESSION['user']['role'] ?? '') !== 'teacher') {
            http_response_code(403);
            exit('Chỉ giáo viên sở hữu đề thi mới được cấu hình.');
        }

        $duration = filter_var($_POST['duration_minutes'] ?? null, FILTER_VALIDATE_INT);
        $limit = filter_var($_POST['violation_limit'] ?? null, FILTER_VALIDATE_INT);
        $action = $_POST['violation_action'] ?? '';
        if (!$duration || $duration < 1 || $duration > 360 || !$limit || $limit < 1 || $limit > 20
            || !in_array($action, ['auto_submit', 'log_only'], true)) {
            http_response_code(400);
            exit('Thông số cấu hình không hợp lệ.');
        }

        $stmt = $this->pdo->prepare(
            "UPDATE exams SET duration_minutes = ?, anti_cheat_enabled = ?,
                    violation_limit = ?, violation_action = ?
             WHERE exam_id = ? AND teacher_id = ?"
        );
        $stmt->execute([
            $duration,
            isset($_POST['anti_cheat_enabled']) ? 1 : 0,
            $limit,
            $action,
            $examId,
            $teacherId
        ]);
        header('Location: index.php?action=exam_statistics&exam_id=' . $examId . '&settings=saved');
        exit;
    }

    private function fetchMonitoringAttempts($examId, $teacherId) {
        $stmt = $this->pdo->prepare(
            "SELECT ea.attempt_id, ea.status, ea.submit_reason, ea.review_status,
                    ea.start_time, ea.end_time, u.full_name,
                    COUNT(v.violation_id) AS violation_count
             FROM exams e
             JOIN exam_attempts ea ON ea.exam_id = e.exam_id
             JOIN users u ON u.user_id = ea.student_id
             LEFT JOIN violation_logs v ON v.attempt_id = ea.attempt_id
             WHERE e.exam_id = ? AND e.teacher_id = ?
             GROUP BY ea.attempt_id
             ORDER BY (ea.status = 'in_progress') DESC, ea.start_time DESC"
        );
        $stmt->execute([$examId, $teacherId]);
        return $stmt->fetchAll();
    }

    public function monitoring() {
        $this->requireLogin();
        $examId = (int) ($_GET['exam_id'] ?? 0);
        $teacherId = (int) $_SESSION['user']['id'];
        $stmt = $this->pdo->prepare(
            "SELECT exam_id, title FROM exams WHERE exam_id = ? AND teacher_id = ?"
        );
        $stmt->execute([$examId, $teacherId]);
        $exam = $stmt->fetch();
        if (!$exam || ($_SESSION['user']['role'] ?? '') !== 'teacher') {
            http_response_code(403);
            exit('Bạn không có quyền giám sát đề thi này.');
        }
        $attempts = $this->fetchMonitoringAttempts($examId, $teacherId);
        $csrfToken = $_SESSION['csrf_token'];
        require __DIR__ . '/../views/exams/monitoring.php';
    }

    public function monitoringData() {
        $this->requireLogin();
        $examId = (int) ($_GET['exam_id'] ?? 0);
        $teacherId = (int) $_SESSION['user']['id'];
        if (($_SESSION['user']['role'] ?? '') !== 'teacher') {
            $this->sendJson(['error' => 'Không có quyền xem dữ liệu giám sát.'], 403);
        }
        $attempts = $this->fetchMonitoringAttempts($examId, $teacherId);
        if (!$attempts) {
            $stmt = $this->pdo->prepare("SELECT 1 FROM exams WHERE exam_id = ? AND teacher_id = ?");
            $stmt->execute([$examId, $teacherId]);
            if (!$stmt->fetch()) {
                $this->sendJson(['error' => 'Không tìm thấy đề thi.'], 404);
            }
        }
        $this->sendJson(['attempts' => $attempts]);
    }

    public function reviewAttempt() {
        $this->requireLogin();
        $this->requirePostWithCsrf();
        $attemptId = (int) ($_POST['attempt_id'] ?? 0);
        $decision = $_POST['decision'] ?? '';
        $note = trim((string) ($_POST['review_note'] ?? ''));
        $note = substr($note, 0, 500);
        $teacherId = (int) $_SESSION['user']['id'];
        if (($_SESSION['user']['role'] ?? '') !== 'teacher'
            || !in_array($decision, ['void', 'allow_retry'], true)) {
            http_response_code(403);
            exit('Yêu cầu phúc khảo không hợp lệ.');
        }

        $reviewStatus = $decision === 'void' ? 'voided' : 'retake_allowed';
        $attemptStatus = $decision === 'void' ? 'voided' : 'completed';
        $stmt = $this->pdo->prepare(
            "UPDATE exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             SET ea.review_status = ?, ea.review_note = ?, ea.reviewed_by = ?,
                 ea.reviewed_at = NOW(), ea.status = ?
             WHERE ea.attempt_id = ? AND e.teacher_id = ?
               AND ea.review_status = 'pending'"
        );
        $stmt->execute([$reviewStatus, $note ?: null, $teacherId, $attemptStatus, $attemptId, $teacherId]);
        if (!$stmt->rowCount()) {
            http_response_code(404);
            exit('Không tìm thấy lượt thi đang chờ phúc khảo.');
        }
        header('Location: index.php?action=attempt_detail&attempt_id=' . $attemptId . '&review=saved');
        exit;
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
            "SELECT e.exam_id, e.title, e.class_id
                    , e.duration_minutes, e.anti_cheat_enabled
                    , e.violation_limit, e.violation_action
             FROM exams e
             WHERE e.exam_id = ? AND e.teacher_id = ?"
        );
        $stmt->execute([$examId, $userId]);
        $exam = $stmt->fetch();
        if (!$exam) {
            die('Bạn không có quyền xem thống kê đề thi này.');
        }
        $csrfToken = $_SESSION['csrf_token'];

        $stmt = $this->pdo->prepare(
                "SELECT u.full_name, ea.total_score, ea.end_time, ea.attempt_id,
                    ea.student_id, ea.submit_reason, ea.review_status,
                        COUNT(DISTINCT v.violation_id) AS violation_count,
                        COUNT(DISTINCT CASE WHEN aa.is_correct = TRUE THEN aa.question_id END) AS correct_count
                 FROM exam_attempts ea
                 JOIN users u ON u.user_id = ea.student_id
                     LEFT JOIN violation_logs v ON v.attempt_id = ea.attempt_id
                     LEFT JOIN attempt_answers aa ON aa.attempt_id = ea.attempt_id
                 WHERE ea.exam_id = ? AND ea.status = 'completed'
                 GROUP BY ea.attempt_id, u.full_name, ea.total_score, ea.end_time,
                      ea.student_id, ea.submit_reason, ea.review_status
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

        $questionCountStmt = $this->pdo->prepare(
            "SELECT COUNT(DISTINCT question_id)
             FROM exam_questions
             WHERE exam_id = ?"
        );
        $questionCountStmt->execute([$examId]);
        $questionCount = (int) $questionCountStmt->fetchColumn();
        $participantCount = count($scores);
        $averageScore = null;
        $highestScore = null;
        $lowestScore = null;
        $passCount = 0;
        $minimumCorrectCount = (int) ceil($questionCount / 2);
        if ($scores) {
            $scoreValues = array_map(function ($row) {
                return (float) $row['total_score'];
            }, $scores);
            $averageScore = array_sum($scoreValues) / $participantCount;
            $highestScore = max($scoreValues);
            $lowestScore = min($scoreValues);
            $passCount = count(array_filter($scores, function ($score) use ($minimumCorrectCount) {
                return (int) $score['correct_count'] >= $minimumCorrectCount;
            }));
        }

        $passRate = $participantCount ? ($passCount / $participantCount) * 100 : null;

        $violationStmt = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM exam_attempts
             WHERE exam_id = ? AND status = 'completed' AND review_status = 'pending'"
        );
        $violationStmt->execute([$examId]);
        $violationWarningCount = (int) $violationStmt->fetchColumn();

        $distributionMode = $_GET['distribution_mode'] ?? 'highest';
        if (!in_array($distributionMode, ['first', 'highest', 'all'], true)) {
            $distributionMode = 'highest';
        }
        $distributionScores = $scores;
        if ($distributionMode !== 'all') {
            $selectedAttempts = [];
            foreach ($scores as $score) {
                $studentId = (int) $score['student_id'];
                if (!isset($selectedAttempts[$studentId])) {
                    $selectedAttempts[$studentId] = $score;
                    continue;
                }
                $current = $selectedAttempts[$studentId];
                $isHigher = (float) $score['total_score'] > (float) $current['total_score'];
                $isLater = $score['end_time'] > $current['end_time']
                    || ($score['end_time'] === $current['end_time'] && (int) $score['attempt_id'] > (int) $current['attempt_id']);
                if (($distributionMode === 'highest' && ($isHigher || ((float) $score['total_score'] === (float) $current['total_score'] && $isLater)))
                    || ($distributionMode === 'first' && !$isLater)) {
                    $selectedAttempts[$studentId] = $score;
                }
            }
            $distributionScores = array_values($selectedAttempts);
        }

        $scoreDistribution = [];
        foreach ($distributionScores as $score) {
            $scoreValue = number_format((float) $score['total_score'], 2, '.', '');
            $scoreDistribution[$scoreValue] = ($scoreDistribution[$scoreValue] ?? 0) + 1;
        }
        uksort($scoreDistribution, function ($left, $right) {
            return (float) $left <=> (float) $right;
        });

        $questionStatsStmt = $this->pdo->prepare(
            "SELECT eq.question_id, eq.order_index,
                    COUNT(DISTINCT ea.attempt_id) AS attempt_count,
                    COUNT(DISTINCT CASE WHEN aa.is_correct = TRUE THEN ea.attempt_id END) AS correct_count,
                    q.content
             FROM exam_questions eq
             JOIN questions q ON q.question_id = eq.question_id
             LEFT JOIN exam_attempts ea
               ON ea.exam_id = eq.exam_id AND ea.status = 'completed'
             LEFT JOIN attempt_answers aa
               ON aa.attempt_id = ea.attempt_id
              AND aa.question_id = eq.question_id
             WHERE eq.exam_id = ?
             GROUP BY eq.question_id, eq.order_index, q.content
             ORDER BY eq.order_index, eq.question_id"
        );
        $questionStatsStmt->execute([$examId]);
        $questionStats = $questionStatsStmt->fetchAll();
        $totalCorrectAnswers = 0;
        $totalWrongAnswers = 0;
        foreach ($questionStats as &$questionStat) {
            $questionStat['attempt_count'] = (int) $questionStat['attempt_count'];
            $questionStat['correct_count'] = (int) $questionStat['correct_count'];
            $questionStat['wrong_count'] = max(0, $questionStat['attempt_count'] - $questionStat['correct_count']);
            $questionStat['wrong_rate'] = $questionStat['attempt_count']
                ? ($questionStat['wrong_count'] / $questionStat['attempt_count']) * 100
                : 0;
            $totalCorrectAnswers += $questionStat['correct_count'];
            $totalWrongAnswers += $questionStat['wrong_count'];
        }
        unset($questionStat);
        $totalAnsweredQuestions = $totalCorrectAnswers + $totalWrongAnswers;
        $correctPercentage = $totalAnsweredQuestions
            ? ($totalCorrectAnswers / $totalAnsweredQuestions) * 100
            : 0;
        $wrongPercentage = $totalAnsweredQuestions
            ? ($totalWrongAnswers / $totalAnsweredQuestions) * 100
            : 0;
        $topDifficultQuestions = $questionStats;
        usort($topDifficultQuestions, function ($left, $right) {
            return $right['wrong_rate'] <=> $left['wrong_rate'];
        });
        $topDifficultQuestions = array_slice($topDifficultQuestions, 0, 5);
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
             WHERE ea.attempt_id = ? AND ea.status IN ('completed', 'voided')
             AND (e.teacher_id = ? OR ea.student_id = ?)"
        );
        $stmt->execute([$attemptId, $userId, $userId]);
        $attempt = $stmt->fetch();
        if (!$attempt) {
            die('Không tìm thấy lượt nộp hoặc bạn không có quyền xem lượt nộp này.');
        }

        $stmt = $this->pdo->prepare(
            "SELECT violation_type, details, occurred_at
             FROM violation_logs WHERE attempt_id = ? ORDER BY occurred_at"
        );
        $stmt->execute([$attemptId]);
        $violationLogs = $stmt->fetchAll();
        $csrfToken = $_SESSION['csrf_token'];

        $stmt = $this->pdo->prepare(
            "SELECT eq.question_id, eq.order_index, q.content, q.question_type,
                    a.answer_id, a.content AS answer_content,
                    CASE WHEN EXISTS (
                        SELECT 1 FROM attempt_correct_answers snapshot
                        WHERE snapshot.attempt_id = ?
                    ) THEN aca.answer_id IS NOT NULL ELSE a.is_correct END AS is_correct
             FROM exam_questions eq
             JOIN questions q ON q.question_id = eq.question_id
             LEFT JOIN answers a ON a.question_id = q.question_id
             LEFT JOIN attempt_correct_answers aca
               ON aca.attempt_id = ? AND aca.question_id = q.question_id
              AND aca.answer_id = a.answer_id
             WHERE eq.exam_id = ?
             ORDER BY eq.order_index, q.question_id, a.order_index, a.answer_id"
        );
        $stmt->execute([$attemptId, $attemptId, (int) $attempt['exam_id']]);
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