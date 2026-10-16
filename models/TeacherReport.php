<?php

final class TeacherReport
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findExam($examId, $teacherId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT exam_id, title, class_id, duration_minutes, anti_cheat_enabled,
                    shuffle_questions, shuffle_answers, violation_limit, violation_action
             FROM exams WHERE exam_id = ? AND teacher_id = ?"
        );
        $stmt->execute([$examId, $teacherId]);
        return $stmt->fetch();
    }

    public function getStatistics($examId, $teacherId, $distributionMode)
    {
        $exam = $this->findExam($examId, $teacherId);
        if (!$exam) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            "SELECT u.full_name, ea.total_score, ea.end_time, ea.attempt_id,
                    ea.student_id, ea.status, ea.submit_reason, ea.review_status,
                    COUNT(DISTINCT v.violation_id) AS violation_count,
                    COUNT(DISTINCT CASE WHEN aa.is_correct = TRUE THEN aa.question_id END) AS correct_count
             FROM exam_attempts ea
             JOIN users u ON u.user_id = ea.student_id
             LEFT JOIN violation_logs v ON v.attempt_id = ea.attempt_id
             LEFT JOIN attempt_answers aa ON aa.attempt_id = ea.attempt_id
             WHERE ea.exam_id = ? AND ea.status IN ('completed', 'voided')
             GROUP BY ea.attempt_id, u.full_name, ea.total_score, ea.end_time,
                      ea.student_id, ea.status, ea.submit_reason, ea.review_status
             ORDER BY u.full_name, ea.end_time, ea.attempt_id"
        );
        $stmt->execute([$examId]);
        $scoreRows = $stmt->fetchAll();
        $attemptNumbers = [];
        foreach ($scoreRows as &$score) {
            $studentId = (int) $score['student_id'];
            $attemptNumbers[$studentId] = ($attemptNumbers[$studentId] ?? 0) + 1;
            $score['attempt_number'] = $attemptNumbers[$studentId];
        }
        unset($score);
        $scores = array_values(array_filter($scoreRows, function ($score) {
            return $score['status'] === 'completed';
        }));

        $questionCountStmt = $this->pdo->prepare(
            "SELECT COUNT(DISTINCT question_id) FROM exam_questions WHERE exam_id = ?"
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

        $violationStmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM exam_attempts
             WHERE exam_id = ? AND status = 'completed' AND review_status = 'pending'"
        );
        $violationStmt->execute([$examId]);
        $violationWarningCount = (int) $violationStmt->fetchColumn();

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
                    || ($score['end_time'] === $current['end_time']
                        && (int) $score['attempt_id'] > (int) $current['attempt_id']);
                if (($distributionMode === 'highest'
                        && ($isHigher || ((float) $score['total_score'] === (float) $current['total_score'] && $isLater)))
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
               ON aa.attempt_id = ea.attempt_id AND aa.question_id = eq.question_id
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

        $topDifficultQuestions = $questionStats;
        usort($topDifficultQuestions, function ($left, $right) {
            return $right['wrong_rate'] <=> $left['wrong_rate'];
        });

        return [
            'exam' => $exam,
            'scoreRows' => $scoreRows,
            'scores' => $scores,
            'questionCount' => $questionCount,
            'participantCount' => $participantCount,
            'averageScore' => $averageScore,
            'highestScore' => $highestScore,
            'lowestScore' => $lowestScore,
            'passCount' => $passCount,
            'minimumCorrectCount' => $minimumCorrectCount,
            'passRate' => $participantCount ? ($passCount / $participantCount) * 100 : null,
            'violationWarningCount' => $violationWarningCount,
            'distributionMode' => $distributionMode,
            'scoreDistribution' => $scoreDistribution,
            'questionStats' => $questionStats,
            'topDifficultQuestions' => array_slice($topDifficultQuestions, 0, 5),
            'totalCorrectAnswers' => $totalCorrectAnswers,
            'totalWrongAnswers' => $totalWrongAnswers,
            'totalAnsweredQuestions' => $totalAnsweredQuestions,
            'correctPercentage' => $totalAnsweredQuestions
                ? ($totalCorrectAnswers / $totalAnsweredQuestions) * 100
                : 0,
            'wrongPercentage' => $totalAnsweredQuestions
                ? ($totalWrongAnswers / $totalAnsweredQuestions) * 100
                : 0
        ];
    }

    public function getMonitoring($examId, $teacherId)
    {
        $exam = $this->findExam($examId, $teacherId);
        if (!$exam) {
            return null;
        }
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
        return ['exam' => $exam, 'attempts' => $stmt->fetchAll()];
    }

    public function updateSettings($examId, $teacherId, array $settings)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE exams SET duration_minutes = ?, anti_cheat_enabled = ?,
                violation_limit = ?, violation_action = ?,
                shuffle_questions = ?, shuffle_answers = ?
             WHERE exam_id = ? AND teacher_id = ?"
        );
        $stmt->execute([
            $settings['duration_minutes'], $settings['anti_cheat_enabled'],
            $settings['violation_limit'], $settings['violation_action'],
            $settings['shuffle_questions'], $settings['shuffle_answers'],
            $examId, $teacherId
        ]);
        return $stmt->rowCount() > 0;
    }

    public function reviewAttempt($attemptId, $teacherId, $decision, $note)
    {
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
        return $stmt->rowCount() > 0;
    }
}
