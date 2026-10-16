<?php

final class StudentReport
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getDashboard($studentId, $selectedExamId, $selectedExamType)
    {
        $examTypeConditions = [
            'auto' => "(LOWER(e.title) LIKE '%tự động%' OR LOWER(e.title) LIKE '%tu dong%' OR LOWER(e.title) LIKE '%auto%')",
            'sample' => "(LOWER(e.title) LIKE '%đề kiểm tra mẫu%' OR LOWER(e.title) LIKE '%đề mẫu%' OR LOWER(e.title) LIKE '%de kiem tra mau%' OR LOWER(e.title) LIKE '%de mau%')",
            'hop' => "(LOWER(e.title) LIKE '%kiểm tra hợp%' OR LOWER(e.title) LIKE '%kiem tra hop%')"
        ];
        if (!isset($examTypeConditions[$selectedExamType])) {
            $selectedExamType = '';
        }

        $sql = "SELECT ea.attempt_id, ea.exam_id, e.title, ea.total_score, ea.start_time, ea.end_time,
                       10 AS max_score, ea.submit_reason, ea.review_status,
                       COUNT(DISTINCT eq.question_id) AS question_count,
                       COUNT(DISTINCT CASE WHEN aa.is_correct = TRUE THEN aa.question_id END) AS correct_count,
                       COUNT(DISTINCT CASE WHEN aa.is_correct = FALSE THEN aa.question_id END) AS wrong_count
                FROM exam_attempts ea
                JOIN exams e ON e.exam_id = ea.exam_id
                LEFT JOIN exam_questions eq ON eq.exam_id = ea.exam_id
                LEFT JOIN attempt_answers aa ON aa.attempt_id = ea.attempt_id
                WHERE ea.student_id = ? AND ea.status = 'completed'";
        $params = [$studentId];
        if ($selectedExamId) {
            $sql .= " AND ea.exam_id = ?";
            $params[] = $selectedExamId;
        }
        if ($selectedExamType !== '') {
            $sql .= " AND " . $examTypeConditions[$selectedExamType];
        }
        $sql .= " GROUP BY ea.attempt_id, ea.exam_id, e.title, ea.total_score, ea.start_time,
                           ea.end_time, ea.submit_reason, ea.review_status
                  ORDER BY ea.end_time ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $attempts = $stmt->fetchAll();

        $examStmt = $this->pdo->prepare(
            "SELECT DISTINCT e.exam_id, e.title
             FROM exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             WHERE ea.student_id = ? AND ea.status = 'completed'
             ORDER BY e.title"
        );
        $examStmt->execute([$studentId]);
        $availableExams = $examStmt->fetchAll();

        $totalCorrect = 0;
        $totalWrong = 0;
        $totalDurationSeconds = 0;
        foreach ($attempts as $attempt) {
            $totalCorrect += (int) $attempt['correct_count'];
            $totalWrong += (int) $attempt['wrong_count'];
            $totalDurationSeconds += max(0, strtotime($attempt['end_time']) - strtotime($attempt['start_time']));
        }
        $attemptCount = count($attempts);
        $examCount = count(array_unique(array_column($attempts, 'exam_id')));
        $averageScore = $attemptCount
            ? array_sum(array_map(function ($attempt) {
                return (float) $attempt['total_score'];
            }, $attempts)) / $attemptCount
            : 0;
        $accuracy = ($totalCorrect + $totalWrong)
            ? ($totalCorrect / ($totalCorrect + $totalWrong)) * 100
            : 0;

        return [
            'attempts' => $attempts,
            'availableExams' => $availableExams,
            'selectedExamId' => $selectedExamId,
            'selectedExamType' => $selectedExamType,
            'totalCorrect' => $totalCorrect,
            'totalWrong' => $totalWrong,
            'attemptCount' => $attemptCount,
            'examCount' => $examCount,
            'averageScore' => $averageScore,
            'accuracy' => $accuracy,
            'averageDurationSeconds' => $attemptCount
                ? (int) round($totalDurationSeconds / $attemptCount)
                : 0
        ];
    }
}
