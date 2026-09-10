-- 1. BẢNG NGƯỜI DÙNG
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    role VARCHAR(20) NOT NULL DEFAULT 'student', -- 'student', 'teacher', 'admin'
    school_name VARCHAR(150), -- Bổ sung thông tin trường học
    avatar VARCHAR(255),
    status VARCHAR(20) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. BẢNG LỚP HỌC
CREATE TABLE classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL,
    class_code VARCHAR(10) NOT NULL UNIQUE, -- Mã tham gia lớp (VD: A7X9K2)
    teacher_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 3. BẢNG HỌC SINH TRONG LỚP
CREATE TABLE class_students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    student_id INT NOT NULL,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 4. BẢNG BÀI HỌC / TÀI LIỆU (Mới bổ sung cho chức năng HỌC)
CREATE TABLE lessons (
    lesson_id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT, -- Nội dung bài giảng
    file_url VARCHAR(255), -- Link tài liệu đính kèm (PDF, Word, video...)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE
);

-- 5. BẢNG NGÂN HÀNG CÂU HỎI
CREATE TABLE question_banks (
    bank_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    bank_name VARCHAR(150) NOT NULL,
    subject VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 6. BẢNG CÂU HỎI
CREATE TABLE questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    bank_id INT NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    question_type VARCHAR(30) NOT NULL DEFAULT 'single_choice', -- 'single_choice', 'multiple_choice'
    difficulty_level VARCHAR(20) DEFAULT 'medium',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bank_id) REFERENCES question_banks(bank_id) ON DELETE CASCADE
);

-- 7. BẢNG ĐÁP ÁN
CREATE TABLE answers (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    content TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    order_index INT DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE
);

-- 8. BẢNG ĐỀ THI
CREATE TABLE exams (
    exam_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    class_id INT, -- Có thể NULL nếu là đề thi tự do
    title VARCHAR(200) NOT NULL,
    duration_minutes INT NOT NULL, -- Thời gian làm bài (phút)
    shuffle_questions BOOLEAN DEFAULT TRUE,
    shuffle_answers BOOLEAN DEFAULT TRUE,
    start_time DATETIME,
    end_time DATETIME,
    status VARCHAR(20) DEFAULT 'draft', -- 'draft', 'published'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE SET NULL
);

-- 9. BẢNG CHI TIẾT CÂU HỎI TRONG ĐỀ THI
CREATE TABLE exam_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    question_id INT NOT NULL,
    score_weight DECIMAL(5,2) DEFAULT 1.0, -- Điểm số của câu hỏi trong đề
    order_index INT DEFAULT 0,
    FOREIGN KEY (exam_id) REFERENCES exams(exam_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE
);

-- 10. BẢNG LƯỢT LÀM BÀI CỦA HỌC SINH
CREATE TABLE exam_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    student_id INT NOT NULL,
    start_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_time DATETIME,
    status VARCHAR(20) DEFAULT 'in_progress', -- 'in_progress', 'completed'
    total_score DECIMAL(5,2) DEFAULT 0,
    FOREIGN KEY (exam_id) REFERENCES exams(exam_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 11. BẢNG CHI TIẾT ĐÁP ÁN HỌC SINH CHỌN (Phục vụ chấm điểm tự động)
CREATE TABLE attempt_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_id INT, -- Đáp án học sinh chọn (dành cho trắc nghiệm)
    answer_text TEXT, -- Câu trả lời chữ (dành cho tự luận nếu mở rộng)
    is_correct BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (attempt_id) REFERENCES exam_attempts(attempt_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE,
    FOREIGN KEY (answer_id) REFERENCES answers(answer_id) ON DELETE SET NULL
);