CREATE TABLE IF NOT EXISTS `login_attempt_limits` (
  `subject_type` varchar(10) NOT NULL,
  `subject_hash` char(64) NOT NULL,
  `failed_attempts` int unsigned NOT NULL DEFAULT 0,
  `window_started_at` datetime NOT NULL,
  `locked_until` datetime DEFAULT NULL,
  PRIMARY KEY (`subject_type`, `subject_hash`),
  KEY `idx_login_attempt_locked_until` (`locked_until`),
  KEY `idx_login_attempt_window_started_at` (`window_started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
