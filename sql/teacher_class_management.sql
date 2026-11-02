-- Teacher class-management schema migration and local test data.
-- Run after importing db.sql into the study_management database.
-- Sample accounts are for development/testing only; do not run the seed section in production.

USE `study_management`;

-- Create the class table when it does not exist.
CREATE TABLE IF NOT EXISTS `classes` (
  `class_id` int(11) NOT NULL AUTO_INCREMENT,
  `class_name` varchar(100) NOT NULL,
  `class_code` varchar(10) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`class_id`),
  UNIQUE KEY `class_code` (`class_code`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `fk_classes_teacher` FOREIGN KEY (`teacher_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create the class membership table with unique membership and foreign keys.
CREATE TABLE IF NOT EXISTS `class_students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `joined_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_class` (`class_id`, `student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fk_class_students_class` FOREIGN KEY (`class_id`)
    REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_class_students_student` FOREIGN KEY (`student_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create exams with the optional class assignment when the table does not exist.
CREATE TABLE IF NOT EXISTS `exams` (
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
  CONSTRAINT `fk_exams_teacher` FOREIGN KEY (`teacher_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_exams_class` FOREIGN KEY (`class_id`)
    REFERENCES `classes` (`class_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add exams.class_id to databases created from an older schema.
SET @migration_sql = IF(
  (SELECT COUNT(*)
   FROM information_schema.columns
   WHERE table_schema = DATABASE()
     AND table_name = 'exams'
     AND column_name = 'class_id') = 0,
  'ALTER TABLE `exams` ADD COLUMN `class_id` int(11) DEFAULT NULL AFTER `teacher_id`',
  'SELECT 1'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

-- Ensure supporting indexes exist.
SET @migration_sql = IF(
  (SELECT COUNT(*)
   FROM information_schema.statistics
   WHERE table_schema = DATABASE()
     AND table_name = 'classes'
     AND column_name = 'teacher_id') = 0,
  'ALTER TABLE `classes` ADD KEY `idx_classes_teacher_id` (`teacher_id`)',
  'SELECT 1'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @migration_sql = IF(
  (SELECT COUNT(*)
   FROM information_schema.statistics
   WHERE table_schema = DATABASE()
     AND table_name = 'class_students'
     AND index_name = 'unique_student_class') = 0
  AND
  (SELECT COUNT(*)
   FROM (
     SELECT index_name
     FROM information_schema.statistics
     WHERE table_schema = DATABASE()
       AND table_name = 'class_students'
       AND non_unique = 0
     GROUP BY index_name
     HAVING GROUP_CONCAT(column_name ORDER BY seq_in_index SEPARATOR ',') = 'class_id,student_id'
   ) AS membership_indexes) = 0,
  'ALTER TABLE `class_students` ADD UNIQUE KEY `unique_student_class` (`class_id`, `student_id`)',
  'SELECT 1'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @migration_sql = IF(
  (SELECT COUNT(*)
   FROM information_schema.statistics
   WHERE table_schema = DATABASE()
     AND table_name = 'class_students'
     AND column_name = 'student_id') = 0,
  'ALTER TABLE `class_students` ADD KEY `idx_class_students_student_id` (`student_id`)',
  'SELECT 1'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @migration_sql = IF(
  (SELECT COUNT(*)
   FROM information_schema.statistics
   WHERE table_schema = DATABASE()
     AND table_name = 'exams'
     AND column_name = 'class_id') = 0,
  'ALTER TABLE `exams` ADD KEY `idx_exams_class_id` (`class_id`)',
  'SELECT 1'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

-- Ensure foreign keys exist, regardless of the constraint names in the imported schema.
SET @migration_sql = IF(
  (SELECT COUNT(*)
   FROM information_schema.key_column_usage
   WHERE constraint_schema = DATABASE()
     AND table_name = 'classes'
     AND column_name = 'teacher_id'
     AND referenced_table_name = 'users'
     AND referenced_column_name = 'user_id') = 0,
  'ALTER TABLE `classes` ADD CONSTRAINT `fk_classes_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
  'SELECT 1'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @migration_sql = IF(
  (SELECT COUNT(*)
   FROM information_schema.key_column_usage
   WHERE constraint_schema = DATABASE()
     AND table_name = 'class_students'
     AND column_name = 'class_id'
     AND referenced_table_name = 'classes'
     AND referenced_column_name = 'class_id') = 0,
  'ALTER TABLE `class_students` ADD CONSTRAINT `fk_class_students_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE',
  'SELECT 1'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @migration_sql = IF(
  (SELECT COUNT(*)
   FROM information_schema.key_column_usage
   WHERE constraint_schema = DATABASE()
     AND table_name = 'class_students'
     AND column_name = 'student_id'
     AND referenced_table_name = 'users'
     AND referenced_column_name = 'user_id') = 0,
  'ALTER TABLE `class_students` ADD CONSTRAINT `fk_class_students_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
  'SELECT 1'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @migration_sql = IF(
  (SELECT COUNT(*)
   FROM information_schema.key_column_usage
   WHERE constraint_schema = DATABASE()
     AND table_name = 'exams'
     AND column_name = 'teacher_id'
     AND referenced_table_name = 'users'
     AND referenced_column_name = 'user_id') = 0,
  'ALTER TABLE `exams` ADD CONSTRAINT `fk_exams_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
  'SELECT 1'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @migration_sql = IF(
  (SELECT COUNT(*)
   FROM information_schema.key_column_usage
   WHERE constraint_schema = DATABASE()
     AND table_name = 'exams'
     AND column_name = 'class_id'
     AND referenced_table_name = 'classes'
     AND referenced_column_name = 'class_id') = 0,
  'ALTER TABLE `exams` ADD CONSTRAINT `fk_exams_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

-- ---------------------------------------------------------------------------
-- Development/test seed only. Password for all six accounts:
-- EduTestDemo2026!
-- These reserved .test email addresses and predictable credentials must not
-- be used for production accounts.
-- ---------------------------------------------------------------------------

START TRANSACTION;

INSERT INTO `users`
  (`full_name`, `email`, `password`, `role`, `school_name`, `status`)
VALUES
  ('Giáo viên mẫu 1', 'teacher.class.demo1@example.test', '$2y$10$G35UmgE7RVm.JJPYGJ7h2ORMSliu7HQSPI0hVt9hVQAOdy7oKl0uK', 'teacher', 'EduTest Demo', 'active'),
  ('Giáo viên mẫu 2', 'teacher.class.demo2@example.test', '$2y$10$G35UmgE7RVm.JJPYGJ7h2ORMSliu7HQSPI0hVt9hVQAOdy7oKl0uK', 'teacher', 'EduTest Demo', 'active'),
  ('Học sinh mẫu 1', 'student.class.demo1@example.test', '$2y$10$G35UmgE7RVm.JJPYGJ7h2ORMSliu7HQSPI0hVt9hVQAOdy7oKl0uK', 'student', 'EduTest Demo', 'active'),
  ('Học sinh mẫu 2', 'student.class.demo2@example.test', '$2y$10$G35UmgE7RVm.JJPYGJ7h2ORMSliu7HQSPI0hVt9hVQAOdy7oKl0uK', 'student', 'EduTest Demo', 'active'),
  ('Học sinh mẫu 3', 'student.class.demo3@example.test', '$2y$10$G35UmgE7RVm.JJPYGJ7h2ORMSliu7HQSPI0hVt9hVQAOdy7oKl0uK', 'student', 'EduTest Demo', 'active'),
  ('Học sinh mẫu 4', 'student.class.demo4@example.test', '$2y$10$G35UmgE7RVm.JJPYGJ7h2ORMSliu7HQSPI0hVt9hVQAOdy7oKl0uK', 'student', 'EduTest Demo', 'active')
ON DUPLICATE KEY UPDATE
  `full_name` = VALUES(`full_name`),
  `password` = VALUES(`password`),
  `role` = VALUES(`role`),
  `school_name` = VALUES(`school_name`),
  `status` = VALUES(`status`);

INSERT INTO `classes` (`class_name`, `class_code`, `teacher_id`)
SELECT 'Lớp Demo 10A', 'DEMO10A', `user_id`
FROM `users`
WHERE `email` = 'teacher.class.demo1@example.test'
  AND `role` = 'teacher'
ON DUPLICATE KEY UPDATE
  `class_name` = VALUES(`class_name`),
  `teacher_id` = VALUES(`teacher_id`);

INSERT INTO `classes` (`class_name`, `class_code`, `teacher_id`)
SELECT 'Lớp Demo 10B', 'DEMO10B', `user_id`
FROM `users`
WHERE `email` = 'teacher.class.demo2@example.test'
  AND `role` = 'teacher'
ON DUPLICATE KEY UPDATE
  `class_name` = VALUES(`class_name`),
  `teacher_id` = VALUES(`teacher_id`);

INSERT INTO `class_students` (`class_id`, `student_id`)
SELECT c.`class_id`, u.`user_id`
FROM `classes` c
JOIN `users` u
WHERE c.`class_code` = 'DEMO10A'
  AND u.`email` IN (
    'student.class.demo1@example.test',
    'student.class.demo2@example.test',
    'student.class.demo3@example.test'
  )
  AND u.`role` = 'student'
  AND u.`status` = 'active'
ON DUPLICATE KEY UPDATE
  `joined_at` = `class_students`.`joined_at`;

INSERT INTO `class_students` (`class_id`, `student_id`)
SELECT c.`class_id`, u.`user_id`
FROM `classes` c
JOIN `users` u
WHERE c.`class_code` = 'DEMO10B'
  AND u.`email` IN (
    'student.class.demo3@example.test',
    'student.class.demo4@example.test'
  )
  AND u.`role` = 'student'
  AND u.`status` = 'active'
ON DUPLICATE KEY UPDATE
  `joined_at` = `class_students`.`joined_at`;

COMMIT;

-- Verify seeded classes, teachers, and student counts.
SELECT
  c.`class_code`,
  c.`class_name`,
  teacher.`email` AS `teacher_email`,
  COUNT(cs.`student_id`) AS `student_count`
FROM `classes` c
JOIN `users` teacher ON teacher.`user_id` = c.`teacher_id`
LEFT JOIN `class_students` cs ON cs.`class_id` = c.`class_id`
WHERE c.`class_code` IN ('DEMO10A', 'DEMO10B')
GROUP BY c.`class_id`, c.`class_code`, c.`class_name`, teacher.`email`
ORDER BY c.`class_code`;
