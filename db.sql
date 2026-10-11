-- Cơ sở dữ liệu quản lý học tập

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `study_management`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `study_management`;

-- Định nghĩa bảng

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'student',
  `school_name` varchar(150) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL AUTO_INCREMENT,
  `class_name` varchar(100) NOT NULL,
  `class_code` varchar(10) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`class_id`),
  UNIQUE KEY `class_code` (`class_code`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`teacher_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `class_students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `joined_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_class` (`class_id`, `student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `class_students_ibfk_1` FOREIGN KEY (`class_id`)
    REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `class_students_ibfk_2` FOREIGN KEY (`student_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `question_banks` (
  `bank_id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `bank_name` varchar(150) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`bank_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `question_banks_ibfk_1` FOREIGN KEY (`teacher_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `question_type` varchar(30) NOT NULL DEFAULT 'single_choice',
  `difficulty_level` varchar(20) DEFAULT 'medium',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`question_id`),
  KEY `bank_id` (`bank_id`),
  CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`bank_id`)
    REFERENCES `question_banks` (`bank_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `answers` (
  `answer_id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `order_index` int(11) DEFAULT 0,
  PRIMARY KEY (`answer_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `answers_ibfk_1` FOREIGN KEY (`question_id`)
    REFERENCES `questions` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `exams` (
  `exam_id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `anti_cheat_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `violation_limit` int(11) NOT NULL DEFAULT 3,
  `violation_action` varchar(20) NOT NULL DEFAULT 'auto_submit',
  `shuffle_questions` tinyint(1) DEFAULT 1,
  `shuffle_answers` tinyint(1) DEFAULT 1,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'draft',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`exam_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`teacher_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `exams_ibfk_2` FOREIGN KEY (`class_id`)
    REFERENCES `classes` (`class_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `exam_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `score_weight` decimal(5,2) DEFAULT 1.00,
  `order_index` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_questions_exam_question_unique` (`exam_id`, `question_id`),
  KEY `exam_id` (`exam_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `exam_questions_ibfk_1` FOREIGN KEY (`exam_id`)
    REFERENCES `exams` (`exam_id`) ON DELETE CASCADE,
  CONSTRAINT `exam_questions_ibfk_2` FOREIGN KEY (`question_id`)
    REFERENCES `questions` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `exam_attempts` (
  `attempt_id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `start_time` datetime DEFAULT current_timestamp(),
  `end_time` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'in_progress',
  `total_score` decimal(5,2) DEFAULT 0.00,
  `duration_minutes_snapshot` int(11) DEFAULT NULL,
  `anti_cheat_enabled_snapshot` tinyint(1) DEFAULT NULL,
  `violation_limit_snapshot` int(11) DEFAULT NULL,
  `violation_action_snapshot` varchar(20) DEFAULT NULL,
  `submit_reason` varchar(30) NOT NULL DEFAULT 'manual',
  `review_status` varchar(20) NOT NULL DEFAULT 'not_required',
  `review_note` varchar(500) DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`attempt_id`),
  KEY `exam_id` (`exam_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `exam_attempts_ibfk_1` FOREIGN KEY (`exam_id`)
    REFERENCES `exams` (`exam_id`) ON DELETE CASCADE,
  CONSTRAINT `exam_attempts_ibfk_2` FOREIGN KEY (`student_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `exam_attempts_reviewed_by_ibfk` FOREIGN KEY (`reviewed_by`)
    REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `attempt_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_id` int(11) DEFAULT NULL,
  `answer_text` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `attempt_id` (`attempt_id`),
  KEY `question_id` (`question_id`),
  KEY `answer_id` (`answer_id`),
  CONSTRAINT `attempt_answers_ibfk_1` FOREIGN KEY (`attempt_id`)
    REFERENCES `exam_attempts` (`attempt_id`) ON DELETE CASCADE,
  CONSTRAINT `attempt_answers_ibfk_2` FOREIGN KEY (`question_id`)
    REFERENCES `questions` (`question_id`) ON DELETE CASCADE,
  CONSTRAINT `attempt_answers_ibfk_3` FOREIGN KEY (`answer_id`)
    REFERENCES `answers` (`answer_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Đáp án đúng được lưu tại thời điểm bắt đầu lượt thi.
CREATE TABLE `attempt_correct_answers` (
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_id` int(11) NOT NULL,
  PRIMARY KEY (`attempt_id`, `question_id`, `answer_id`),
  KEY `attempt_correct_answers_question_id` (`question_id`),
  KEY `attempt_correct_answers_attempt_id` (`attempt_id`),
  KEY `attempt_correct_answers_answer_id` (`answer_id`),
  CONSTRAINT `attempt_correct_answers_ibfk_1` FOREIGN KEY (`attempt_id`)
    REFERENCES `exam_attempts` (`attempt_id`) ON DELETE CASCADE,
  CONSTRAINT `attempt_correct_answers_ibfk_2` FOREIGN KEY (`question_id`)
    REFERENCES `questions` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ghi nhận sự kiện vi phạm trong lượt thi.
CREATE TABLE `violation_logs` (
  `violation_id` int(11) NOT NULL AUTO_INCREMENT,
  `attempt_id` int(11) NOT NULL,
  `violation_type` varchar(50) NOT NULL,
  `details` varchar(255) DEFAULT NULL,
  `occurred_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`violation_id`),
  KEY `violation_logs_attempt_id` (`attempt_id`),
  CONSTRAINT `violation_logs_ibfk_1` FOREIGN KEY (`attempt_id`)
    REFERENCES `exam_attempts` (`attempt_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `forum_posts` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `exam_id` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`post_id`),
  KEY `user_id` (`user_id`),
  KEY `exam_id` (`exam_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `forum_posts_ibfk_2` FOREIGN KEY (`exam_id`)
    REFERENCES `exams` (`exam_id`) ON DELETE SET NULL,
  CONSTRAINT `forum_posts_ibfk_3` FOREIGN KEY (`question_id`)
    REFERENCES `questions` (`question_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `forum_comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`comment_id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `forum_comments_ibfk_1` FOREIGN KEY (`post_id`)
    REFERENCES `forum_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `forum_comments_ibfk_2` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `lessons` (
  `lesson_id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`lesson_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`class_id`)
    REFERENCES `classes` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dữ liệu mẫu

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `phone`, `role`, `school_name`, `avatar`, `status`, `created_at`) VALUES
(1, 'Mai Văn Hợp', 'hop@gmail.com', '$2y$10$eoZW5.31SEhsPCvEV83ZZ.akXhE7NznCVrPCKtgz5QsKbTRtykLSm', NULL, 'student', 'THPT Trần Văn Lan', NULL, 'active', '2026-09-11 13:40:40'),
(2, 'Mai Văn Hợp GV', 'vanhopmai2004@gmail.com', '$2y$10$EclrTgiYcWzf9B/PtlDfXutArGTlwQIhgV7i0YyEoVcQJBkvFJ/3.', NULL, 'teacher', 'THPT Trần Văn Lan', NULL, 'active', '2026-09-11 13:41:47');

INSERT INTO `question_banks` (`bank_id`, `teacher_id`, `bank_name`, `subject`, `created_at`) VALUES
(1, 2, 'Ngân hàng Toán mẫu', 'Toán', '2026-09-11 14:20:15');

INSERT INTO `questions` (`question_id`, `bank_id`, `content`, `image_url`, `question_type`, `difficulty_level`, `created_at`) VALUES
(1, 1, '2 + 2 bằng bao nhiêu?', NULL, 'single_choice', 'easy', '2026-09-11 14:20:15'),
(2, 1, 'Số nào là số nguyên tố?', NULL, 'multiple_choice', 'medium', '2026-09-11 14:20:16');

INSERT INTO `answers` (`answer_id`, `question_id`, `content`, `is_correct`, `order_index`) VALUES
(1, 1, '3', 0, 1),
(2, 1, '4', 1, 2),
(3, 1, '5', 0, 3),
(4, 2, '2', 1, 1),
(5, 2, '3', 1, 2),
(6, 2, '4', 0, 3);

INSERT INTO `exams` (`exam_id`, `teacher_id`, `class_id`, `title`, `duration_minutes`, `shuffle_questions`, `shuffle_answers`, `start_time`, `end_time`, `status`, `created_at`) VALUES
(1, 2, NULL, 'Đề kiểm tra mẫu', 30, 1, 1, NULL, NULL, 'published', '2026-09-11 14:20:16');

INSERT INTO `exam_questions` (`id`, `exam_id`, `question_id`, `score_weight`, `order_index`) VALUES
(1, 1, 1, 1.00, 1),
(2, 1, 2, 1.00, 2);

INSERT INTO `exam_attempts` (`attempt_id`, `exam_id`, `student_id`, `start_time`, `end_time`, `status`, `total_score`) VALUES
(1, 1, 1, '2026-09-11 14:21:13', '2026-09-11 14:21:13', 'completed', 2.00),
(2, 1, 1, '2026-09-11 14:21:34', '2026-09-11 14:21:34', 'completed', 1.00),
(3, 1, 1, '2026-09-11 14:31:23', '2026-09-11 14:31:23', 'completed', 2.00),
(4, 1, 1, '2026-09-11 15:10:07', '2026-09-11 15:10:07', 'completed', 1.00);

INSERT INTO `attempt_answers` (`id`, `attempt_id`, `question_id`, `answer_id`, `answer_text`, `is_correct`) VALUES
(1, 1, 1, 2, NULL, 1),
(2, 1, 2, 4, NULL, 1),
(3, 1, 2, 5, NULL, 1),
(4, 2, 1, 3, NULL, 0),
(5, 2, 2, 4, NULL, 1),
(6, 2, 2, 5, NULL, 1),
(7, 3, 1, 2, NULL, 1),
(8, 3, 2, 4, NULL, 1),
(9, 3, 2, 5, NULL, 1),
(10, 4, 1, 2, NULL, 1),
(11, 4, 2, 5, NULL, 0);
