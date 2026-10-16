<?php

final class AutomaticGrading
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function validateExam($examId)
    {
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

    public function saveAnswers($attemptId, $examId, $answers)
    {
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

    public function orderQuestions(array $questions, $seed, $shuffleQuestions, $shuffleAnswers)
    {
        foreach ($questions as &$question) {
            if (!$shuffleAnswers) {
                continue;
            }
            $questionId = (int) $question['question_id'];
            usort($question['answers'], function ($left, $right) use ($seed, $questionId) {
                $leftHash = hash_hmac('sha256', 'answer:' . $questionId . ':' . $left['answer_id'], $seed);
                $rightHash = hash_hmac('sha256', 'answer:' . $questionId . ':' . $right['answer_id'], $seed);
                $comparison = strcmp($leftHash, $rightHash);
                return $comparison !== 0 ? $comparison : ((int) $left['answer_id'] <=> (int) $right['answer_id']);
            });
        }
        unset($question);

        $questions = array_values($questions);
        if ($shuffleQuestions) {
            usort($questions, function ($left, $right) use ($seed) {
                $leftId = (int) $left['question_id'];
                $rightId = (int) $right['question_id'];
                $leftHash = hash_hmac('sha256', 'question:' . $leftId, $seed);
                $rightHash = hash_hmac('sha256', 'question:' . $rightId, $seed);
                $comparison = strcmp($leftHash, $rightHash);
                return $comparison !== 0 ? $comparison : ($leftId <=> $rightId);
            });
        }

        return $questions;
    }

    public function snapshotCorrectAnswers($attemptId, $examId)
    {
        $stmt = $this->pdo->prepare(
            "INSERT IGNORE INTO attempt_correct_answers (attempt_id, question_id, answer_id)
             SELECT ?, eq.question_id, a.answer_id
             FROM exam_questions eq
             JOIN answers a ON a.question_id = eq.question_id
             WHERE eq.exam_id = ? AND a.is_correct = TRUE"
        );
        $stmt->execute([$attemptId, $examId]);
    }

    public function finalize($attemptId, $studentId, $answers, $reason)
    {
        $this->pdo->beginTransaction();
        try {
            $completed = $this->finalizeInTransaction($attemptId, $studentId, $answers, $reason);
            if ($completed) {
                $this->pdo->commit();
            } else {
                $this->pdo->rollBack();
            }
            return $completed;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function finalizeInTransaction($attemptId, $studentId, $answers, $reason)
    {
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
            $this->saveAnswers($attemptId, (int) $attempt['exam_id'], $answers);
        } else {
            $this->ensureUnanswered($attemptId, (int) $attempt['exam_id']);
        }
        $this->snapshotCorrectAnswers($attemptId, (int) $attempt['exam_id']);

        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT question_id FROM exam_questions WHERE exam_id = ?"
        );
        $stmt->execute([(int) $attempt['exam_id']]);
        $examQuestions = $stmt->fetchAll();
        if (!$examQuestions) {
            throw new RuntimeException('Đề thi chưa có câu hỏi.');
        }

        $gradingError = $this->validateExam((int) $attempt['exam_id']);
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
        $correctQuestionCount = 0;
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
                $correctQuestionCount++;
            }
        }

        $score = round(($correctQuestionCount / count($examQuestions)) * 10, 2);
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

    public function findAutosaveAttempt($attemptId, $studentId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT ea.exam_id, ea.start_time,
                    COALESCE(ea.duration_minutes_snapshot, e.duration_minutes) AS duration_minutes
             FROM exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             WHERE ea.attempt_id = ? AND ea.student_id = ?
               AND ea.status = 'in_progress' FOR UPDATE"
        );
        $stmt->execute([$attemptId, $studentId]);
        return $stmt->fetch();
    }

    public function autosave($attemptId, $studentId, $answers)
    {
        $this->pdo->beginTransaction();
        try {
            $attempt = $this->findAutosaveAttempt($attemptId, $studentId);
            if (!$attempt) {
                $this->pdo->rollBack();
                return null;
            }
            if (time() >= strtotime($attempt['start_time']) + ((int) $attempt['duration_minutes'] * 60)) {
                $this->finalizeInTransaction($attemptId, $studentId, $answers, 'time_expired');
                $this->pdo->commit();
                return ['saved' => false, 'forced_submit' => true, 'reason' => 'time_expired'];
            }
            $this->saveAnswers($attemptId, (int) $attempt['exam_id'], $answers);
            $this->pdo->commit();
            return ['saved' => true];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function recordViolation($attemptId, $studentId, $violationType, $details)
    {
        $this->pdo->beginTransaction();
        try {
        $stmt = $this->pdo->prepare(
            "SELECT ea.status,
                    COALESCE(ea.anti_cheat_enabled_snapshot, e.anti_cheat_enabled) AS anti_cheat_enabled,
                    COALESCE(ea.violation_limit_snapshot, e.violation_limit) AS violation_limit,
                    COALESCE(ea.violation_action_snapshot, e.violation_action) AS violation_action
             FROM exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             WHERE ea.attempt_id = ? AND ea.student_id = ? FOR UPDATE"
        );
        $stmt->execute([$attemptId, $studentId]);
        $attempt = $stmt->fetch();
        if (!$attempt || $attempt['status'] !== 'in_progress') {
            $this->pdo->rollBack();
            return null;
        }
        if (!(int) $attempt['anti_cheat_enabled']) {
            $this->pdo->commit();
            return ['count' => 0, 'forced_submit' => false];
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
            $this->finalizeInTransaction($attemptId, $studentId, null, 'violation_limit');
        }

        $result = [
            'count' => $violationCount,
            'limit' => $limit,
            'forced_submit' => $forcedSubmit,
            'attempt_id' => $attemptId
        ];
        $this->pdo->commit();
        return $result;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function ensureUnanswered($attemptId, $examId)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO attempt_answers (attempt_id, question_id, answer_id, is_correct)
             SELECT ?, DISTINCT_QUESTIONS.question_id, NULL, 0
             FROM (
                 SELECT DISTINCT question_id FROM exam_questions WHERE exam_id = ?
             ) AS DISTINCT_QUESTIONS
             WHERE NOT EXISTS (
                 SELECT 1 FROM attempt_answers aa
                 WHERE aa.attempt_id = ? AND aa.question_id = DISTINCT_QUESTIONS.question_id
             )"
        );
        $stmt->execute([$attemptId, $examId, $attemptId]);
    }
}
