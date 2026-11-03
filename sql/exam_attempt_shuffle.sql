ALTER TABLE `exam_attempts`
  ADD COLUMN `shuffle_questions_snapshot` tinyint(1) DEFAULT NULL AFTER `violation_action_snapshot`,
  ADD COLUMN `shuffle_answers_snapshot` tinyint(1) DEFAULT NULL AFTER `shuffle_questions_snapshot`,
  ADD COLUMN `shuffle_seed` char(64) DEFAULT NULL AFTER `shuffle_answers_snapshot`;
