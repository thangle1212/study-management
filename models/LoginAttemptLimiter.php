<?php
class LoginAttemptLimiter {
    private $pdo;
    private const WINDOW_MINUTES = 15;
    private const LOCKOUT_MINUTES = 15;
    private const EMAIL_FAILURE_LIMIT = 5;
    // Higher threshold avoids locking users who share a school or office network.
    private const IP_FAILURE_LIMIT = 20;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getLockoutSeconds($email, $ipAddress) {
        [$emailHash, $ipHash] = $this->getHashes($email, $ipAddress);
        $stmt = $this->pdo->prepare(
            "SELECT subject_type, locked_until
             FROM login_attempt_limits
             WHERE (subject_type = 'email' AND subject_hash = ?)
                OR (subject_type = 'ip' AND subject_hash = ?)"
        );
        $stmt->execute([$emailHash, $ipHash]);

        $secondsRemaining = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $limit) {
            if (empty($limit['locked_until'])) {
                continue;
            }

            $remaining = strtotime($limit['locked_until']) - time();
            if ($remaining > $secondsRemaining) {
                $secondsRemaining = $remaining;
            }
        }

        return max(0, $secondsRemaining);
    }

    public function getLockoutMessage($secondsRemaining) {
        $minutesRemaining = max(1, (int) ceil($secondsRemaining / 60));
        return "Bạn đã thử đăng nhập quá nhiều lần. Vui lòng thử lại sau khoảng {$minutesRemaining} phút.";
    }

    public function recordFailure($email, $ipAddress) {
        [$emailHash, $ipHash] = $this->getHashes($email, $ipAddress);
        $this->pdo->beginTransaction();

        try {
            $this->incrementLimit('email', $emailHash, self::EMAIL_FAILURE_LIMIT);
            $this->incrementLimit('ip', $ipHash, self::IP_FAILURE_LIMIT);
            $this->pdo->exec(
                'DELETE FROM login_attempt_limits
                 WHERE window_started_at <= DATE_SUB(NOW(), INTERVAL 1 DAY)'
            );
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }

        return $this->getLockoutSeconds($email, $ipAddress);
    }

    public function clearEmailFailures($email) {
        $emailHash = hash('sha256', strtolower(trim($email)));
        $stmt = $this->pdo->prepare(
            "DELETE FROM login_attempt_limits
             WHERE subject_type = 'email' AND subject_hash = ?"
        );
        $stmt->execute([$emailHash]);
    }

    private function incrementLimit($subjectType, $subjectHash, $failureLimit) {
        $sql = "INSERT INTO login_attempt_limits
                    (subject_type, subject_hash, failed_attempts, window_started_at)
                VALUES (?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE
                    locked_until = IF(
                        window_started_at <= DATE_SUB(NOW(), INTERVAL " . self::WINDOW_MINUTES . " MINUTE),
                        NULL,
                        IF(failed_attempts + 1 >= " . (int) $failureLimit . ",
                            DATE_ADD(NOW(), INTERVAL " . self::LOCKOUT_MINUTES . " MINUTE),
                            locked_until)
                    ),
                    failed_attempts = IF(
                        window_started_at <= DATE_SUB(NOW(), INTERVAL " . self::WINDOW_MINUTES . " MINUTE),
                        1,
                        failed_attempts + 1
                    ),
                    window_started_at = IF(
                        window_started_at <= DATE_SUB(NOW(), INTERVAL " . self::WINDOW_MINUTES . " MINUTE),
                        NOW(),
                        window_started_at
                    )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$subjectType, $subjectHash]);
    }

    private function getHashes($email, $ipAddress) {
        return [
            hash('sha256', strtolower(trim($email))),
            hash('sha256', trim($ipAddress) !== '' ? trim($ipAddress) : 'unknown')
        ];
    }
}
