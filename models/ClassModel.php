<?php
class ClassModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getClassesByTeacher($teacherId) {
        $stmt = $this->pdo->prepare(
            "SELECT c.class_id, c.class_name, c.class_code, c.teacher_id, c.created_at,
                    COUNT(cs.student_id) AS student_count
             FROM classes c
             INNER JOIN users teacher
                ON teacher.user_id = c.teacher_id
               AND teacher.role = 'teacher'
               AND teacher.status = 'active'
             LEFT JOIN class_students cs ON cs.class_id = c.class_id
             WHERE c.teacher_id = ?
             GROUP BY c.class_id, c.class_name, c.class_code, c.teacher_id, c.created_at
             ORDER BY c.created_at DESC, c.class_id DESC"
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClassesByStudent($studentId) {
        $stmt = $this->pdo->prepare(
            "SELECT c.class_id, c.class_name, c.class_code, c.created_at,
                    teacher.full_name AS teacher_name
             FROM class_students cs
             INNER JOIN classes c ON c.class_id = cs.class_id
             INNER JOIN users student
                ON student.user_id = cs.student_id
               AND student.role = 'student'
               AND student.status = 'active'
             INNER JOIN users teacher
                ON teacher.user_id = c.teacher_id
               AND teacher.role = 'teacher'
               AND teacher.status = 'active'
             WHERE cs.student_id = ?
             ORDER BY c.class_name, c.class_id"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublishedExamsByStudent($studentId) {
        $stmt = $this->pdo->prepare(
            "SELECT e.exam_id, e.title, e.start_time, e.end_time, e.duration_minutes,
                    c.class_id, c.class_name
             FROM exams e
             INNER JOIN class_students cs ON cs.class_id = e.class_id
             INNER JOIN classes c ON c.class_id = e.class_id
             WHERE cs.student_id = ?
               AND e.status = 'published'
             ORDER BY e.created_at DESC, e.exam_id DESC"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClassDetail($classId, $teacherId) {
        $stmt = $this->pdo->prepare(
            "SELECT c.class_id, c.class_name, c.class_code, c.teacher_id, c.created_at,
                    COUNT(cs.student_id) AS student_count
             FROM classes c
             INNER JOIN users teacher
                ON teacher.user_id = c.teacher_id
               AND teacher.role = 'teacher'
               AND teacher.status = 'active'
             LEFT JOIN class_students cs ON cs.class_id = c.class_id
             WHERE c.class_id = ? AND c.teacher_id = ?
             GROUP BY c.class_id, c.class_name, c.class_code, c.teacher_id, c.created_at
             LIMIT 1"
        );
        $stmt->execute([$classId, $teacherId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStudentsInClass($classId, $teacherId) {
        $stmt = $this->pdo->prepare(
            "SELECT u.user_id, u.full_name, u.email, u.school_name, u.status, cs.joined_at
             FROM class_students cs
             INNER JOIN classes c ON c.class_id = cs.class_id
             INNER JOIN users teacher
                ON teacher.user_id = c.teacher_id
               AND teacher.role = 'teacher'
               AND teacher.status = 'active'
             INNER JOIN users u
                ON u.user_id = cs.student_id
               AND u.role = 'student'
             WHERE c.class_id = ? AND c.teacher_id = ?
             ORDER BY u.full_name, u.user_id"
        );
        $stmt->execute([$classId, $teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableStudents($classId, $teacherId) {
        $stmt = $this->pdo->prepare(
            "SELECT u.user_id, u.full_name, u.email
             FROM users u
             WHERE u.role = 'student'
               AND u.status = 'active'
               AND NOT EXISTS (
                   SELECT 1
                   FROM class_students cs
                   INNER JOIN classes c ON c.class_id = cs.class_id
                   WHERE cs.student_id = u.user_id
                     AND c.class_id = ?
                     AND c.teacher_id = ?
               )
             ORDER BY u.full_name, u.user_id"
        );
        $stmt->execute([$classId, $teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isActiveStudent($studentId) {
        $stmt = $this->pdo->prepare(
            "SELECT 1
             FROM users
             WHERE user_id = ? AND role = 'student' AND status = 'active'
             LIMIT 1"
        );
        $stmt->execute([$studentId]);
        return (bool) $stmt->fetchColumn();
    }

    public function studentIsInClass($classId, $studentId, $teacherId) {
        $stmt = $this->pdo->prepare(
            "SELECT 1
             FROM class_students cs
             INNER JOIN classes c ON c.class_id = cs.class_id
             WHERE c.class_id = ? AND c.teacher_id = ? AND cs.student_id = ?
             LIMIT 1"
        );
        $stmt->execute([$classId, $teacherId, $studentId]);
        return (bool) $stmt->fetchColumn();
    }

    public function addStudentToClass($classId, $studentId, $teacherId) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO class_students (class_id, student_id)
             SELECT c.class_id, student.user_id
             FROM classes c
             INNER JOIN users teacher
                ON teacher.user_id = c.teacher_id
               AND teacher.role = 'teacher'
               AND teacher.status = 'active'
             INNER JOIN users student
                ON student.user_id = ?
               AND student.role = 'student'
               AND student.status = 'active'
             WHERE c.class_id = ? AND c.teacher_id = ?
             ON DUPLICATE KEY UPDATE id = class_students.id"
        );
        $stmt->execute([$studentId, $classId, $teacherId]);
        return $stmt->rowCount() === 1;
    }

    public function removeStudentFromClass($classId, $studentId, $teacherId) {
        $stmt = $this->pdo->prepare(
            "DELETE cs
             FROM class_students cs
             INNER JOIN classes c ON c.class_id = cs.class_id
             INNER JOIN users teacher
                ON teacher.user_id = c.teacher_id
               AND teacher.role = 'teacher'
               AND teacher.status = 'active'
             WHERE c.class_id = ?
               AND c.teacher_id = ?
               AND cs.student_id = ?"
        );
        $stmt->execute([$classId, $teacherId, $studentId]);
        return $stmt->rowCount() === 1;
    }

    public function getExamsByTeacher($teacherId) {
        $stmt = $this->pdo->prepare(
            "SELECT e.exam_id, e.title, e.status, e.class_id,
                    (SELECT COUNT(DISTINCT eq.question_id)
                     FROM exam_questions eq
                     WHERE eq.exam_id = e.exam_id) AS question_count
             FROM exams e
             WHERE e.teacher_id = ?
             ORDER BY e.created_at DESC, e.exam_id DESC"
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExamForTeacher($examId, $teacherId) {
        $stmt = $this->pdo->prepare(
            "SELECT e.exam_id, e.title, e.class_id,
                    (SELECT COUNT(DISTINCT eq.question_id)
                     FROM exam_questions eq
                     WHERE eq.exam_id = e.exam_id) AS question_count
             FROM exams e
             WHERE e.exam_id = ? AND e.teacher_id = ?
             LIMIT 1"
        );
        $stmt->execute([$examId, $teacherId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function assignExamToClass($examId, $classId, $teacherId) {
        $stmt = $this->pdo->prepare(
            "UPDATE exams e
             INNER JOIN classes c ON c.class_id = ?
             INNER JOIN users teacher
                ON teacher.user_id = c.teacher_id
               AND teacher.role = 'teacher'
               AND teacher.status = 'active'
             SET e.class_id = c.class_id
             WHERE e.exam_id = ?
               AND e.teacher_id = ?
               AND c.teacher_id = ?
               AND EXISTS (
                   SELECT 1
                   FROM exam_questions eq
                   WHERE eq.exam_id = e.exam_id
               )"
        );
        $stmt->execute([$classId, $examId, $teacherId, $teacherId]);
        return $stmt->rowCount() === 1;
    }
}
?>
