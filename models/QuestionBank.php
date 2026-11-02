<?php
class QuestionBank {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getBanksForOwner($teacherId) {
        $stmt = $this->pdo->prepare(
            "SELECT qb.bank_id, qb.bank_name, qb.subject, qb.created_at,
                    COUNT(DISTINCT q.question_id) AS question_count
             FROM question_banks qb
             LEFT JOIN questions q ON q.bank_id = qb.bank_id
             WHERE qb.teacher_id = ?
             GROUP BY qb.bank_id, qb.bank_name, qb.subject, qb.created_at
             ORDER BY qb.bank_name"
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBank($bankId, $teacherId) {
        $stmt = $this->pdo->prepare(
            "SELECT bank_id, bank_name, subject, created_at
             FROM question_banks WHERE bank_id = ? AND teacher_id = ?"
        );
        $stmt->execute([$bankId, $teacherId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createBank($teacherId, $bankName, $subject) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO question_banks (teacher_id, bank_name, subject) VALUES (?, ?, ?)"
        );
        $stmt->execute([$teacherId, $bankName, $subject]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateBank($bankId, $teacherId, $bankName, $subject) {
        $stmt = $this->pdo->prepare(
            "UPDATE question_banks SET bank_name = ?, subject = ?
             WHERE bank_id = ? AND teacher_id = ?"
        );
        $stmt->execute([$bankName, $subject, $bankId, $teacherId]);
        return $stmt->rowCount() > 0 || $this->getBank($bankId, $teacherId) !== false;
    }

    public function getQuestionsForBank($bankId, $teacherId) {
        $stmt = $this->pdo->prepare(
            "SELECT q.question_id, q.content, q.image_url, q.question_type,
                    q.difficulty_level, q.created_at, a.answer_id,
                    a.content AS answer_content, a.is_correct, a.order_index
             FROM questions q
             INNER JOIN question_banks qb ON qb.bank_id = q.bank_id
             LEFT JOIN answers a ON a.question_id = q.question_id
             WHERE q.bank_id = ? AND qb.teacher_id = ?
             ORDER BY q.question_id, a.order_index, a.answer_id"
        );
        $stmt->execute([$bankId, $teacherId]);
        $questions = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $questionId = (int) $row['question_id'];
            if (!isset($questions[$questionId])) {
                $questions[$questionId] = [
                    'question_id' => $questionId,
                    'content' => $row['content'],
                    'image_url' => $row['image_url'],
                    'question_type' => $row['question_type'],
                    'difficulty_level' => $row['difficulty_level'],
                    'created_at' => $row['created_at'],
                    'answers' => []
                ];
            }
            if ($row['answer_id'] !== null) {
                $questions[$questionId]['answers'][] = [
                    'content' => $row['answer_content'],
                    'is_correct' => (bool) $row['is_correct']
                ];
            }
        }
        return array_values($questions);
    }

    public function getQuestion($questionId, $teacherId) {
        $stmt = $this->pdo->prepare(
            "SELECT q.question_id, q.bank_id, q.content, q.image_url, q.question_type,
                    q.difficulty_level, a.answer_id, a.content AS answer_content,
                    a.is_correct, a.order_index
             FROM questions q
             INNER JOIN question_banks qb ON qb.bank_id = q.bank_id
             LEFT JOIN answers a ON a.question_id = q.question_id
             WHERE q.question_id = ? AND qb.teacher_id = ?
             ORDER BY a.order_index, a.answer_id"
        );
        $stmt->execute([$questionId, $teacherId]);
        $question = null;
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($question === null) {
                $question = [
                    'question_id' => (int) $row['question_id'],
                    'bank_id' => (int) $row['bank_id'],
                    'content' => $row['content'],
                    'image_url' => $row['image_url'] ?? '',
                    'question_type' => $row['question_type'],
                    'difficulty_level' => $row['difficulty_level'],
                    'answers' => []
                ];
            }
            if ($row['answer_id'] !== null) {
                $question['answers'][] = [
                    'content' => $row['answer_content'],
                    'is_correct' => (bool) $row['is_correct']
                ];
            }
        }
        return $question;
    }

    public function createQuestion($teacherId, $questionData) {
        // Save the question and its answers atomically so banks never contain partial questions.
        $this->pdo->beginTransaction();
        try {
            $this->assertOwnedBank($questionData['bank_id'], $teacherId);
            $stmt = $this->pdo->prepare(
                "INSERT INTO questions (bank_id, content, image_url, question_type, difficulty_level)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $questionData['bank_id'],
                $questionData['content'],
                $questionData['image_url'],
                $questionData['question_type'],
                $questionData['difficulty_level']
            ]);
            $questionId = (int) $this->pdo->lastInsertId();
            $this->replaceAnswers($questionId, $questionData['answers']);
            $this->pdo->commit();
            return $questionId;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function updateQuestion($questionId, $teacherId, $questionData) {
        // Protect exam history before replacing question text or answer IDs.
        $this->pdo->beginTransaction();
        try {
            $this->assertOwnedQuestion($questionId, $teacherId);
            if ($this->isQuestionLockedForEdit($questionId)) {
                throw new DomainException('Câu hỏi thuộc đề đã công bố/lên lịch hoặc đã có lượt làm nên không thể sửa. Hãy tạo câu hỏi mới nếu cần thay đổi.');
            }
            $this->assertOwnedBank($questionData['bank_id'], $teacherId);
            $stmt = $this->pdo->prepare(
                "UPDATE questions
                 SET bank_id = ?, content = ?, image_url = ?, question_type = ?, difficulty_level = ?
                 WHERE question_id = ?"
            );
            $stmt->execute([
                $questionData['bank_id'],
                $questionData['content'],
                $questionData['image_url'],
                $questionData['question_type'],
                $questionData['difficulty_level'],
                $questionId
            ]);
            $this->replaceAnswers($questionId, $questionData['answers']);
            $this->pdo->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function deleteQuestion($questionId, $teacherId) {
        $this->assertOwnedQuestion($questionId, $teacherId);
        if ($this->isQuestionUsed($questionId)) {
            throw new DomainException('Câu hỏi đã được dùng trong đề thi nên không thể xóa.');
        }
        $stmt = $this->pdo->prepare(
            "DELETE FROM questions
             WHERE question_id = ?
             AND bank_id IN (SELECT bank_id FROM question_banks WHERE teacher_id = ?)"
        );
        $stmt->execute([$questionId, $teacherId]);
        return $stmt->rowCount() > 0;
    }

    public function deleteBank($bankId, $teacherId) {
        $this->assertOwnedBank($bankId, $teacherId);
        $usage = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM questions q
             WHERE q.bank_id = ?
             AND (
                EXISTS(SELECT 1 FROM exam_questions eq WHERE eq.question_id = q.question_id)
                OR EXISTS(SELECT 1 FROM attempt_answers aa WHERE aa.question_id = q.question_id)
                OR EXISTS(SELECT 1 FROM attempt_correct_answers aca WHERE aca.question_id = q.question_id)
             )"
        );
        $usage->execute([$bankId]);
        if ((int) $usage->fetchColumn() > 0) {
            throw new DomainException('Ngân hàng có câu hỏi đã được dùng trong đề thi nên không thể xóa.');
        }
        $stmt = $this->pdo->prepare(
            "DELETE FROM question_banks WHERE bank_id = ? AND teacher_id = ?"
        );
        $stmt->execute([$bankId, $teacherId]);
        return $stmt->rowCount() > 0;
    }

    private function replaceAnswers($questionId, $answers) {
        $delete = $this->pdo->prepare("DELETE FROM answers WHERE question_id = ?");
        $delete->execute([$questionId]);
        $insert = $this->pdo->prepare(
            "INSERT INTO answers (question_id, content, is_correct, order_index) VALUES (?, ?, ?, ?)"
        );
        foreach ($answers as $index => $answer) {
            $insert->execute([$questionId, $answer['content'], $answer['is_correct'] ? 1 : 0, $index]);
        }
    }

    private function assertOwnedBank($bankId, $teacherId) {
        if (!$this->getBank($bankId, $teacherId)) {
            throw new DomainException('Không tìm thấy ngân hàng câu hỏi hoặc bạn không có quyền truy cập.');
        }
    }

    private function assertOwnedQuestion($questionId, $teacherId) {
        $stmt = $this->pdo->prepare(
            "SELECT q.question_id
             FROM questions q
             INNER JOIN question_banks qb ON qb.bank_id = q.bank_id
             WHERE q.question_id = ? AND qb.teacher_id = ?"
        );
        $stmt->execute([$questionId, $teacherId]);
        if (!$stmt->fetchColumn()) {
            throw new DomainException('Không tìm thấy câu hỏi hoặc bạn không có quyền truy cập.');
        }
    }

    private function isQuestionUsed($questionId) {
        $stmt = $this->pdo->prepare(
            "SELECT
                EXISTS(SELECT 1 FROM exam_questions WHERE question_id = ?)
                OR EXISTS(SELECT 1 FROM attempt_answers WHERE question_id = ?)
                OR EXISTS(SELECT 1 FROM attempt_correct_answers WHERE question_id = ?)"
        );
        $stmt->execute([$questionId, $questionId, $questionId]);
        return (bool) $stmt->fetchColumn();
    }

    private function isQuestionLockedForEdit($questionId) {
        $stmt = $this->pdo->prepare(
            "SELECT
                EXISTS(
                    SELECT 1
                    FROM exam_questions eq
                    INNER JOIN exams e ON e.exam_id = eq.exam_id
                    WHERE eq.question_id = ? AND e.status IN ('published', 'scheduled')
                )
                OR EXISTS(SELECT 1 FROM attempt_answers WHERE question_id = ?)
                OR EXISTS(SELECT 1 FROM attempt_correct_answers WHERE question_id = ?)"
        );
        $stmt->execute([$questionId, $questionId, $questionId]);
        return (bool) $stmt->fetchColumn();
    }
}
