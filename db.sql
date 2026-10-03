-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th9 20, 2026 lúc 03:24 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `answers`
--

CREATE TABLE `answers` (
  `answer_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `order_index` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `answers`
--

INSERT INTO `answers` (`answer_id`, `question_id`, `content`, `is_correct`, `order_index`) VALUES
(1, 1, '3', 0, 1),
(2, 1, '4', 1, 2),
(3, 1, '5', 0, 3),
(4, 2, '2', 1, 1),
(5, 2, '3', 1, 2),
(6, 2, '4', 0, 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `attempt_answers`
--

CREATE TABLE `attempt_answers` (
  `id` int(11) NOT NULL,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_id` int(11) DEFAULT NULL,
  `answer_text` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `attempt_answers`
--

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

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `class_code` varchar(10) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `class_students`
--

CREATE TABLE `class_students` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `joined_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `exams`
--

CREATE TABLE `exams` (
  `exam_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `shuffle_questions` tinyint(1) DEFAULT 1,
  `shuffle_answers` tinyint(1) DEFAULT 1,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'draft',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `exams`
--

INSERT INTO `exams` (`exam_id`, `teacher_id`, `class_id`, `title`, `duration_minutes`, `shuffle_questions`, `shuffle_answers`, `start_time`, `end_time`, `status`, `created_at`) VALUES
(1, 2, NULL, 'Đề kiểm tra mẫu', 30, 1, 1, NULL, NULL, 'published', '2026-09-11 14:20:16');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `exam_attempts`
--

CREATE TABLE `exam_attempts` (
  `attempt_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `start_time` datetime DEFAULT current_timestamp(),
  `end_time` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'in_progress',
  `total_score` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `exam_attempts`
--

INSERT INTO `exam_attempts` (`attempt_id`, `exam_id`, `student_id`, `start_time`, `end_time`, `status`, `total_score`) VALUES
(1, 1, 1, '2026-09-11 14:21:13', '2026-09-11 14:21:13', 'completed', 2.00),
(2, 1, 1, '2026-09-11 14:21:34', '2026-09-11 14:21:34', 'completed', 1.00),
(3, 1, 1, '2026-09-11 14:31:23', '2026-09-11 14:31:23', 'completed', 2.00),
(4, 1, 1, '2026-09-11 15:10:07', '2026-09-11 15:10:07', 'completed', 1.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `exam_questions`
--

CREATE TABLE `exam_questions` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `score_weight` decimal(5,2) DEFAULT 1.00,
  `order_index` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `exam_questions`
--

INSERT INTO `exam_questions` (`id`, `exam_id`, `question_id`, `score_weight`, `order_index`) VALUES
(1, 1, 1, 1.00, 1),
(2, 1, 2, 1.00, 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `forum_comments`
--

CREATE TABLE `forum_comments` (
  `comment_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `forum_posts`
--

CREATE TABLE `forum_posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `exam_id` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lessons`
--

CREATE TABLE `lessons` (
  `lesson_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `questions`
--

CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `question_type` varchar(30) NOT NULL DEFAULT 'single_choice',
  `difficulty_level` varchar(20) DEFAULT 'medium',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `questions`
--

INSERT INTO `questions` (`question_id`, `bank_id`, `content`, `image_url`, `question_type`, `difficulty_level`, `created_at`) VALUES
(1, 1, '2 + 2 bằng bao nhiêu?', NULL, 'single_choice', 'easy', '2026-09-11 14:20:15'),
(2, 1, 'Số nào là số nguyên tố?', NULL, 'multiple_choice', 'medium', '2026-09-11 14:20:16');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `question_banks`
--

CREATE TABLE `question_banks` (
  `bank_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `bank_name` varchar(150) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `question_banks`
--

INSERT INTO `question_banks` (`bank_id`, `teacher_id`, `bank_name`, `subject`, `created_at`) VALUES
(1, 2, 'Ngân hàng Toán mẫu', 'Toán', '2026-09-11 14:20:15');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'student',
  `school_name` varchar(150) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `phone`, `role`, `school_name`, `avatar`, `status`, `created_at`) VALUES
(1, 'Mai Văn Hợp', 'hop@gmail.com', '$2y$10$eoZW5.31SEhsPCvEV83ZZ.akXhE7NznCVrPCKtgz5QsKbTRtykLSm', NULL, 'student', 'THPT Trần Văn Lan', NULL, 'active', '2026-09-11 13:40:40'),
(2, 'Mai Văn Hợp GV', 'vanhopmai2004@gmail.com', '$2y$10$EclrTgiYcWzf9B/PtlDfXutArGTlwQIhgV7i0YyEoVcQJBkvFJ/3.', NULL, 'teacher', 'THPT Trần Văn Lan', NULL, 'active', '2026-09-11 13:41:47');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Chỉ mục cho bảng `attempt_answers`
--
ALTER TABLE `attempt_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attempt_id` (`attempt_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `answer_id` (`answer_id`);

--
-- Chỉ mục cho bảng `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `class_code` (`class_code`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Chỉ mục cho bảng `class_students`
--
ALTER TABLE `class_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Chỉ mục cho bảng `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`exam_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Chỉ mục cho bảng `exam_attempts`
--
ALTER TABLE `exam_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Chỉ mục cho bảng `exam_questions`
--
ALTER TABLE `exam_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Chỉ mục cho bảng `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Chỉ mục cho bảng `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`lesson_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Chỉ mục cho bảng `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `bank_id` (`bank_id`);

--
-- Chỉ mục cho bảng `question_banks`
--
ALTER TABLE `question_banks`
  ADD PRIMARY KEY (`bank_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `answers`
--
ALTER TABLE `answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `attempt_answers`
--
ALTER TABLE `attempt_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `class_students`
--
ALTER TABLE `class_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `exams`
--
ALTER TABLE `exams`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `exam_attempts`
--
ALTER TABLE `exam_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `exam_questions`
--
ALTER TABLE `exam_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `forum_comments`
--
ALTER TABLE `forum_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `lessons`
--
ALTER TABLE `lessons`
  MODIFY `lesson_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `question_banks`
--
ALTER TABLE `question_banks`
  MODIFY `bank_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `answers`
--
ALTER TABLE `answers`
  ADD CONSTRAINT `answers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `attempt_answers`
--
ALTER TABLE `attempt_answers`
  ADD CONSTRAINT `attempt_answers_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `exam_attempts` (`attempt_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attempt_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attempt_answers_ibfk_3` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`answer_id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `class_students`
--
ALTER TABLE `class_students`
  ADD CONSTRAINT `class_students_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_students_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exams_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `exam_attempts`
--
ALTER TABLE `exam_attempts`
  ADD CONSTRAINT `exam_attempts_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_attempts_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `exam_questions`
--
ALTER TABLE `exam_questions`
  ADD CONSTRAINT `exam_questions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_questions_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD CONSTRAINT `forum_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `forum_posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_posts_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `forum_posts_ibfk_3` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`bank_id`) REFERENCES `question_banks` (`bank_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `question_banks`
--
ALTER TABLE `question_banks`
  ADD CONSTRAINT `question_banks_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
ALTER TABLE users
ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL,
ADD COLUMN reset_token_expires DATETIME DEFAULT NULL