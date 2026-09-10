<?php
class Exam {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findPublishedExam($examId) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM exams
             WHERE exam_id = ? AND status = 'published'
             AND (start_time IS NULL OR start_time <= NOW())
             AND (end_time IS NULL OR end_time >= NOW())"
        );
        $stmt->execute([$examId]);
        return $stmt->fetch();
    }

    public function getQuestions($examId) {
        $stmt = $this->pdo->prepare(
            "SELECT q.question_id, q.content, q.image_url, q.question_type,
                    eq.score_weight, a.answer_id, a.content AS answer_content,
                    a.order_index
             FROM exam_questions eq
             INNER JOIN questions q ON q.question_id = eq.question_id
             LEFT JOIN answers a ON a.question_id = q.question_id
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
                    'image_url' => $row['image_url'],
                    'question_type' => $row['question_type'],
                    'score_weight' => $row['score_weight'],
                    'answers' => []
                ];
            }
            if ($row['answer_id'] !== null) {
                $questions[$questionId]['answers'][] = [
                    'answer_id' => $row['answer_id'],
                    'content' => $row['answer_content']
                ];
            }
        }
        return array_values($questions);
    }

    public function findActiveAttempt($examId, $studentId) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM exam_attempts
             WHERE exam_id = ? AND student_id = ? AND status = 'in_progress'
             ORDER BY attempt_id DESC LIMIT 1"
        );
        $stmt->execute([$examId, $studentId]);
        return $stmt->fetch();
    }

    public function createAttempt($examId, $studentId) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO exam_attempts (exam_id, student_id) VALUES (?, ?)"
        );
        $stmt->execute([$examId, $studentId]);
        return $this->pdo->lastInsertId();
    }

    public function completeAttempt($attemptId, $studentId, $answers) {
        $this->pdo->beginTransaction();
        try {
            $check = $this->pdo->prepare(
                "SELECT attempt_id FROM exam_attempts
                 WHERE attempt_id = ? AND student_id = ? AND status = 'in_progress'"
            );
            $check->execute([$attemptId, $studentId]);
            if (!$check->fetch()) {
                $this->pdo->rollBack();
                return false;
            }

            $insert = $this->pdo->prepare(
                "INSERT INTO attempt_answers (attempt_id, question_id, answer_id)
                 VALUES (?, ?, ?)"
            );
            foreach ($answers as $questionId => $answerIds) {
                foreach ((array) $answerIds as $answerId) {
                    if (ctype_digit((string) $answerId)) {
                        $insert->execute([$attemptId, (int) $questionId, (int) $answerId]);
                    }
                }
            }

            $update = $this->pdo->prepare(
                "UPDATE exam_attempts SET status = 'completed', end_time = NOW()
                 WHERE attempt_id = ? AND student_id = ?"
            );
            $update->execute([$attemptId, $studentId]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function logViolation($attemptId, $studentId, $type, $details = null) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO violation_logs (attempt_id, violation_type, details)
             SELECT attempt_id, ?, ? FROM exam_attempts
             WHERE attempt_id = ? AND student_id = ? AND status = 'in_progress'"
        );
        return $stmt->execute([$type, $details, $attemptId, $studentId]);
    }
}
?>
