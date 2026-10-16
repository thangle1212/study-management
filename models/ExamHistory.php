<?php

final class ExamHistory
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findResult($attemptId, $userId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT ea.*, e.title, u.full_name
             FROM exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             JOIN users u ON u.user_id = ea.student_id
             WHERE ea.attempt_id = ? AND (ea.student_id = ? OR e.teacher_id = ?)"
        );
        $stmt->execute([$attemptId, $userId, $userId]);
        return $stmt->fetch();
    }

    public function findDetail($attemptId, $userId)
    {
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
            return null;
        }

        $stmt = $this->pdo->prepare(
            "SELECT violation_type, details, occurred_at
             FROM violation_logs WHERE attempt_id = ? ORDER BY occurred_at"
        );
        $stmt->execute([$attemptId]);
        $violationLogs = $stmt->fetchAll();

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
            "SELECT question_id, answer_id FROM attempt_answers
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

        return [
            'attempt' => $attempt,
            'violationLogs' => $violationLogs,
            'questions' => $questions
        ];
    }
}
