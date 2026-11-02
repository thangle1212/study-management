<?php
class AdminClass {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getClasses() {
        $stmt = $this->pdo->query(
            "SELECT c.class_id, c.class_name, c.class_code, c.teacher_id, c.created_at,
                    u.full_name AS teacher_name, COUNT(cs.student_id) AS student_count
             FROM classes c
             JOIN users u ON u.user_id = c.teacher_id
             LEFT JOIN class_students cs ON cs.class_id = c.class_id
             GROUP BY c.class_id, c.class_name, c.class_code, c.teacher_id, c.created_at, u.full_name
             ORDER BY c.created_at DESC, c.class_id DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeachers() {
        $stmt = $this->pdo->query(
            "SELECT user_id, full_name, email
             FROM users
             WHERE role = 'teacher' AND status = 'active'
             ORDER BY full_name, user_id"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveStudents() {
        $stmt = $this->pdo->query(
            "SELECT user_id, full_name, email
             FROM users
             WHERE role = 'student' AND status = 'active'
             ORDER BY full_name, user_id"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClassMemberships() {
        $stmt = $this->pdo->query(
            "SELECT cs.class_id, cs.student_id, cs.joined_at, u.full_name, u.email, u.status
             FROM class_students cs
             JOIN users u ON u.user_id = cs.student_id
             ORDER BY cs.class_id, u.full_name, u.user_id"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function classExists($classId) {
        $stmt = $this->pdo->prepare("SELECT 1 FROM classes WHERE class_id = ? LIMIT 1");
        $stmt->execute([$classId]);
        return (bool) $stmt->fetchColumn();
    }

    public function activeTeacherExists($teacherId) {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM users
             WHERE user_id = ? AND role = 'teacher' AND status = 'active'
             LIMIT 1"
        );
        $stmt->execute([$teacherId]);
        return (bool) $stmt->fetchColumn();
    }

    public function activeStudentExists($studentId) {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM users
             WHERE user_id = ? AND role = 'student' AND status = 'active'
             LIMIT 1"
        );
        $stmt->execute([$studentId]);
        return (bool) $stmt->fetchColumn();
    }

    public function classCodeExists($classCode, $excludeClassId = null) {
        $sql = "SELECT 1 FROM classes WHERE class_code = ?";
        $params = [$classCode];
        if ($excludeClassId !== null) {
            $sql .= " AND class_id <> ?";
            $params[] = $excludeClassId;
        }
        $stmt = $this->pdo->prepare($sql . " LIMIT 1");
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public function createClass($className, $classCode, $teacherId) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO classes (class_name, class_code, teacher_id)
             VALUES (?, ?, ?)"
        );
        return $stmt->execute([$className, $classCode, $teacherId]);
    }

    public function updateClass($classId, $className, $teacherId) {
        $stmt = $this->pdo->prepare(
            "UPDATE classes SET class_name = ?, teacher_id = ? WHERE class_id = ?"
        );
        return $stmt->execute([$className, $teacherId, $classId]);
    }

    public function studentBelongsToClass($classId, $studentId) {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM class_students WHERE class_id = ? AND student_id = ? LIMIT 1"
        );
        $stmt->execute([$classId, $studentId]);
        return (bool) $stmt->fetchColumn();
    }

    public function assignStudent($classId, $studentId) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO class_students (class_id, student_id)
             SELECT c.class_id, u.user_id
             FROM classes c
             JOIN users u ON u.user_id = ?
             WHERE c.class_id = ? AND u.role = 'student' AND u.status = 'active'"
        );
        $stmt->execute([$studentId, $classId]);
        return $stmt->rowCount() === 1;
    }

    public function removeStudent($classId, $studentId) {
        $stmt = $this->pdo->prepare(
            "DELETE FROM class_students WHERE class_id = ? AND student_id = ?"
        );
        return $stmt->execute([$classId, $studentId]);
    }
}
