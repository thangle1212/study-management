<?php
require_once __DIR__ . '/../models/Exam.php';
require_once __DIR__ . '/../models/AutomaticGrading.php';

class ExamController {
    private $pdo;
    private $grading;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->grading = new AutomaticGrading($pdo);
    }

    private function requireLogin() {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $userId = filter_var($_SESSION['user']['id'] ?? null, FILTER_VALIDATE_INT);
        if ($userId === false || $userId < 1) {
            unset($_SESSION['user'], $_SESSION['csrf_token']);
            header('Location: index.php?action=login&error=session_expired');
            exit;
        }

        $stmt = $this->pdo->prepare(
            "SELECT status, role FROM users WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || $user['status'] !== 'active') {
            unset($_SESSION['user'], $_SESSION['csrf_token']);
            header('Location: index.php?action=login&error=session_expired');
            exit;
        }

        $_SESSION['user']['role'] = $user['role'];
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    private function requireStudent() {
        $this->requireLogin();
        if (($_SESSION['user']['role'] ?? '') !== 'student') {
            http_response_code(403);
            exit('Chỉ học sinh mới được làm bài thi.');
        }
    }

    private function requirePostWithCsrf() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ. Hãy tải lại trang và thử lại.');
        }
    }

    // Loại bỏ thẻ/thuộc tính không an toàn và giữ lại định dạng được phép trong nội dung dán.
    private function cleanRichTextNode($node) {
        $allowedTags = ['b', 'strong', 'i', 'em', 'u', 'span', 'font', 'br', 'p', 'div', 'ul', 'ol', 'li'];
        $discardTags = ['script', 'style', 'iframe', 'object', 'svg', 'math', 'template'];
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $tag = strtolower($child->nodeName);
            if (in_array($tag, $discardTags, true)) {
                $node->removeChild($child);
                continue;
            }

            if (!in_array($tag, $allowedTags, true)) {
                $this->cleanRichTextNode($child);
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            $styles = [];
            $formatting = [];
            if ($tag === 'span' && $child->hasAttribute('style')) {
                foreach (explode(';', $child->getAttribute('style')) as $declaration) {
                    if (preg_match('/^\s*(color|background-color)\s*:\s*(.*?)\s*$/i', $declaration, $matches)) {
                        $normalizedColor = $this->normalizeRichTextColor($matches[2]);
                        if ($normalizedColor !== null) {
                            $styles[strtolower($matches[1])] = $normalizedColor;
                        }
                    } elseif (preg_match('/^\s*font-weight\s*:\s*(bold|[7-9]00)\s*$/i', $declaration)) {
                        $formatting[] = 'strong';
                    } elseif (preg_match('/^\s*font-style\s*:\s*italic\s*$/i', $declaration)) {
                        $formatting[] = 'em';
                    } elseif (preg_match('/^\s*text-decoration(?:-line)?\s*:\s*[^;]*underline/i', $declaration)) {
                        $formatting[] = 'u';
                    }
                }
            } elseif ($tag === 'font' && $child->hasAttribute('color')) {
                $normalizedColor = $this->normalizeRichTextColor($child->getAttribute('color'));
                if ($normalizedColor !== null) {
                    $styles['color'] = $normalizedColor;
                }
            }

            while ($child->attributes->length > 0) {
                $child->removeAttributeNode($child->attributes->item(0));
            }

            if (isset($styles['color'])) {
                if ($tag === 'font') {
                    $child->setAttribute('color', $styles['color']);
                }
            }
            if ($tag === 'span' && $styles) {
                $safeStyles = [];
                foreach (['color', 'background-color'] as $styleName) {
                    if (isset($styles[$styleName])) {
                        $safeStyles[] = $styleName . ': ' . $styles[$styleName];
                    }
                }
                $child->setAttribute('style', implode('; ', $safeStyles));
            }

            foreach (array_unique($formatting) as $formatTag) {
                $wrapper = $child->ownerDocument->createElement($formatTag);
                while ($child->firstChild) {
                    $wrapper->appendChild($child->firstChild);
                }
                $child->appendChild($wrapper);
            }

            $this->cleanRichTextNode($child);
        }
    }

    // Chuẩn hóa màu HTML về mã hex an toàn để lưu và hiển thị nhất quán.
    private function normalizeRichTextColor($color) {
        $color = trim($color);
        if (preg_match('/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i', $color)) {
            return strtolower($color);
        }
        if (preg_match('/^rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)$/i', $color, $matches)) {
            $channels = [(int) $matches[1], (int) $matches[2], (int) $matches[3]];
            if (max($channels) <= 255) {
                return sprintf('#%02x%02x%02x', $channels[0], $channels[1], $channels[2]);
            }
        }
        return null;
    }

    // Làm sạch HTML rich-text trước khi lưu hoặc đưa nội dung đáp án ra màn hình làm bài.
    private function sanitizeRichText($html) {
        if (!class_exists('DOMDocument')) {
            throw new RuntimeException('PHP DOM extension is required to safely format answer content.');
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousLibxmlState = libxml_use_internal_errors(true);
        try {
            $loaded = $document->loadHTML(
                '<?xml encoding="utf-8" ?><div id="answer-root">' . $html . '</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxmlState);
        }

        if (!$loaded) {
            throw new RuntimeException('Không thể phân tích nội dung định dạng của đáp án.');
        }
        $root = $document->getElementById('answer-root');
        if (!$root) {
            throw new RuntimeException('Không thể đọc nội dung định dạng của đáp án.');
        }
        $this->cleanRichTextNode($root);

        $safeHtml = '';
        foreach ($root->childNodes as $child) {
            $safeHtml .= $document->saveHTML($child);
        }
        return trim($safeHtml);
    }

    // Kiểm tra datetime-local từ biểu mẫu và chuyển sang định dạng DATETIME của MySQL.
    private function parseExamDateTime($value, $fieldLabel) {
        if ($value === '') {
            return null;
        }

        $date = DateTime::createFromFormat('!Y-m-d\TH:i', $value);
        $dateErrors = DateTime::getLastErrors();
        if (!$date || ($dateErrors && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
            || $date->format('Y-m-d\TH:i') !== $value) {
            throw new InvalidArgumentException($fieldLabel . ' không hợp lệ.');
        }
        return $date->format('Y-m-d H:i:s');
    }

    // Xử lý tạo đề: kiểm tra quyền/dữ liệu, nhận đáp án đúng từ định dạng hoặc đánh dấu tay,
    // rồi chuyển dữ liệu hợp lệ sang model để lưu trong một transaction.
    // BEGIN EXAM CREATION
    public function createExam() {
        $this->requireLogin();
        $role = $_SESSION['user']['role'] ?? '';
        if (!in_array($role, ['teacher', 'admin'], true)) {
            http_response_code(403);
            exit('Chỉ giáo viên hoặc quản trị viên mới được tạo đề thi.');
        }

        $userId = (int) $_SESSION['user']['id'];
        $bankStmt = $this->pdo->prepare(
            "SELECT bank_id, bank_name, subject
             FROM question_banks
             WHERE teacher_id = ?
             ORDER BY bank_name"
        );
        $bankStmt->execute([$userId]);
        $questionBanks = $bankStmt->fetchAll(PDO::FETCH_ASSOC);

        $initialBankId = filter_var($_GET['bank_id'] ?? null, FILTER_VALIDATE_INT);
        $formData = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : [
            'title' => '',
            'bank_id' => $initialBankId && $initialBankId > 0 ? (string) $initialBankId : '',
            'duration_minutes' => '60',
            'status' => 'draft',
            'start_time' => '',
            'end_time' => '',
            'pasted_exam_html' => ''
        ];
        $safePastedExamHtml = '';
        $formErrors = [];
        $created = ($_GET['created'] ?? '') === '1';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostWithCsrf();
            try {
                $pastedExamHtml = $formData['pasted_exam_html'] ?? '';
                if (!is_string($pastedExamHtml) || strlen($pastedExamHtml) > 4194304) {
                    throw new InvalidArgumentException('Nội dung đề không hợp lệ hoặc vượt quá giới hạn 4 MB.');
                }
                $safePastedExamHtml = $pastedExamHtml === '' ? '' : $this->sanitizeRichText($pastedExamHtml);

                $titleValue = $formData['title'] ?? '';
                if (!is_string($titleValue)) {
                    throw new InvalidArgumentException('Tên đề thi không hợp lệ.');
                }
                $title = trim($titleValue);
                if ($title === '' || mb_strlen($title, 'UTF-8') > 200) {
                    throw new InvalidArgumentException('Tên đề thi là bắt buộc và không được dài quá 200 ký tự.');
                }

                $bankValue = $formData['bank_id'] ?? null;
                $bankId = is_scalar($bankValue) ? filter_var($bankValue, FILTER_VALIDATE_INT) : false;
                $ownedBankIds = array_map('intval', array_column($questionBanks, 'bank_id'));
                if (!$bankId || !in_array((int) $bankId, $ownedBankIds, true)) {
                    throw new InvalidArgumentException('Vui lòng chọn ngân hàng câu hỏi của bạn.');
                }

                $durationValue = $formData['duration_minutes'] ?? null;
                $duration = is_scalar($durationValue) ? filter_var($durationValue, FILTER_VALIDATE_INT) : false;
                if ($duration === false || $duration < 1 || $duration > 1440) {
                    throw new InvalidArgumentException('Thời gian làm bài phải từ 1 đến 1440 phút.');
                }

                $allowedStatuses = ['draft', 'published', 'scheduled', 'archived'];
                $statusValue = $formData['status'] ?? 'draft';
                $status = is_string($statusValue) ? $statusValue : '';
                if (!in_array($status, $allowedStatuses, true)) {
                    throw new InvalidArgumentException('Trạng thái đề thi không hợp lệ.');
                }

                $startValue = $formData['start_time'] ?? '';
                $endValue = $formData['end_time'] ?? '';
                if (!is_string($startValue) || !is_string($endValue)) {
                    throw new InvalidArgumentException('Thời gian đề thi không hợp lệ.');
                }
                $startTime = $this->parseExamDateTime($startValue, 'Ngày bắt đầu');
                $endTime = $this->parseExamDateTime($endValue, 'Ngày kết thúc');
                if ($startTime !== null && $endTime !== null && strtotime($endTime) < strtotime($startTime)) {
                    throw new InvalidArgumentException('Ngày kết thúc phải sau ngày bắt đầu.');
                }
                if ($status === 'scheduled' && ($startTime === null || $endTime === null)) {
                    throw new InvalidArgumentException('Đề thi lên lịch cần có ngày bắt đầu và ngày kết thúc.');
                }

                $questionsInput = $formData['questions'] ?? null;
                if (!is_array($questionsInput) || count($questionsInput) < 1 || count($questionsInput) > 50) {
                    throw new InvalidArgumentException('Đề thi cần có từ 1 đến 50 câu hỏi.');
                }

                $questions = [];
                foreach (array_values($questionsInput) as $questionIndex => $questionInput) {
                    if (!is_array($questionInput)) {
                        throw new InvalidArgumentException('Dữ liệu câu hỏi ' . ($questionIndex + 1) . ' không hợp lệ.');
                    }

                    $contentValue = $questionInput['content'] ?? '';
                    if (!is_string($contentValue)) {
                        throw new InvalidArgumentException('Nội dung câu hỏi ' . ($questionIndex + 1) . ' không hợp lệ.');
                    }
                    $content = trim($contentValue);
                    if ($content === '' || mb_strlen($content, 'UTF-8') > 2000) {
                        throw new InvalidArgumentException('Câu ' . ($questionIndex + 1) . ' cần có nội dung (tối đa 2000 ký tự).');
                    }

                    $questionTypeValue = $questionInput['question_type'] ?? '';
                    $questionType = is_string($questionTypeValue) ? $questionTypeValue : '';
                    if (!in_array($questionType, ['single_choice', 'multiple_choice'], true)) {
                        throw new InvalidArgumentException('Loại câu hỏi ' . ($questionIndex + 1) . ' không hợp lệ.');
                    }

                    $scoreWeight = $questionInput['score_weight'] ?? '';
                    if (!is_numeric($scoreWeight) || (float) $scoreWeight < 0.01 || (float) $scoreWeight > 999.99) {
                        throw new InvalidArgumentException('Điểm câu ' . ($questionIndex + 1) . ' phải từ 0,01 đến 999,99.');
                    }

                    $answersInput = $questionInput['answers'] ?? null;
                    if (!is_array($answersInput) || count($answersInput) < 2 || count($answersInput) > 8) {
                        throw new InvalidArgumentException('Câu ' . ($questionIndex + 1) . ' cần có từ 2 đến 8 đáp án.');
                    }

                    $answers = [];
                    foreach (array_values($answersInput) as $answerIndex => $answerInput) {
                        if (!is_array($answerInput)) {
                            throw new InvalidArgumentException('Đáp án ' . ($answerIndex + 1) . ' của câu ' . ($questionIndex + 1) . ' không hợp lệ.');
                        }

                        $rawAnswer = $answerInput['content'] ?? '';
                        if (!is_string($rawAnswer)) {
                            throw new InvalidArgumentException('Đáp án ' . ($answerIndex + 1) . ' của câu ' . ($questionIndex + 1) . ' không hợp lệ.');
                        }
                        if (mb_strlen($rawAnswer, 'UTF-8') > 5000) {
                            throw new InvalidArgumentException('Mỗi đáp án không được dài quá 5000 ký tự.');
                        }
                        $safeAnswer = $this->sanitizeRichText($rawAnswer);
                        $answerText = trim(html_entity_decode(strip_tags($safeAnswer), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                        if ($answerText === '') {
                            throw new InvalidArgumentException('Đáp án ' . ($answerIndex + 1) . ' của câu ' . ($questionIndex + 1) . ' không được để trống.');
                        }

                        $hasFormatting = preg_match('/<(?:b|strong|i|em|u)\b|<span\b[^>]*style="[^"]*(?:color|background-color)\s*:|<font\b[^>]*color=/i', $safeAnswer) === 1;
                        $isCorrect = $hasFormatting || (($answerInput['manual_correct'] ?? '') === '1');
                        $answers[] = [
                            'content' => $safeAnswer,
                            'is_correct' => $isCorrect
                        ];
                    }

                    $correctCount = count(array_filter($answers, function ($answer) {
                        return $answer['is_correct'];
                    }));
                    if ($correctCount === 0 || ($questionType === 'single_choice' && $correctCount !== 1)) {
                        throw new InvalidArgumentException(
                            $questionType === 'single_choice'
                                ? 'Câu ' . ($questionIndex + 1) . ' phải có đúng một đáp án đúng. Hãy định dạng đáp án hoặc chọn ô đánh dấu.'
                                : 'Câu ' . ($questionIndex + 1) . ' cần ít nhất một đáp án đúng.'
                        );
                    }

                    $questions[] = [
                        'content' => $content,
                        'question_type' => $questionType,
                        'score_weight' => number_format((float) $scoreWeight, 2, '.', ''),
                        'answers' => $answers
                    ];
                }

                $examModel = new Exam($this->pdo);
                $examId = $examModel->createExamAndQuestions([
                    'teacher_id' => $userId,
                    'bank_id' => (int) $bankId,
                    'title' => $title,
                    'duration_minutes' => (int) $duration,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => $status
                ], $questions);

                header('Location: index.php?action=qe_exam_create&created=1&exam_id=' . $examId);
                exit;
            } catch (InvalidArgumentException $exception) {
                $formErrors[] = $exception->getMessage();
            }

            if (isset($formData['questions']) && is_array($formData['questions'])) {
                foreach ($formData['questions'] as &$questionInput) {
                    if (is_array($questionInput) && isset($questionInput['answers']) && is_array($questionInput['answers'])) {
                        foreach ($questionInput['answers'] as &$answerInput) {
                            if (is_array($answerInput) && isset($answerInput['content']) && is_string($answerInput['content'])) {
                                $answerInput['content'] = $this->sanitizeRichText($answerInput['content']);
                            }
                        }
                        unset($answerInput);
                    }
                }
                unset($questionInput);
            }
        }

        $csrfToken = $_SESSION['csrf_token'];
        require __DIR__ . '/../views/qe/exam/create.php';
    }
    // END EXAM CREATION

    private function sendJson($payload, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function ensureAutomaticallyGradableExam($examId) {
        $stmt = $this->pdo->prepare(
            "SELECT q.question_id, q.question_type,
                    COUNT(a.answer_id) AS answer_count,
                    COALESCE(SUM(CASE WHEN a.is_correct = TRUE THEN 1 ELSE 0 END), 0) AS correct_count
             FROM exam_questions eq
             JOIN questions q ON q.question_id = eq.question_id
             LEFT JOIN answers a ON a.question_id = q.question_id
             WHERE eq.exam_id = ?
             GROUP BY q.question_id, q.question_type"
        );
        $stmt->execute([$examId]);
        $questions = $stmt->fetchAll();
        if (!$questions) {
            return 'Đề thi chưa có câu hỏi.';
        }

        foreach ($questions as $question) {
            if (!in_array($question['question_type'], ['single_choice', 'multiple_choice'], true)) {
                return 'Đề thi có câu hỏi chưa được hỗ trợ chấm tự động. Chỉ hỗ trợ câu hỏi một đáp án và nhiều đáp án.';
            }
            if ((int) $question['answer_count'] === 0 || (int) $question['correct_count'] === 0) {
                return 'Đề thi có câu hỏi chưa được cấu hình đáp án đúng.';
            }
            if ($question['question_type'] === 'single_choice' && (int) $question['correct_count'] !== 1) {
                return 'Câu hỏi một đáp án phải có đúng một đáp án đúng.';
            }
        }

        return null;
    }

    private function saveAttemptAnswers($attemptId, $examId, $answers) {
        $answers = is_array($answers) ? $answers : [];
        $stmt = $this->pdo->prepare(
            "SELECT eq.question_id, a.answer_id
             FROM exam_questions eq
             LEFT JOIN answers a ON a.question_id = eq.question_id
             WHERE eq.exam_id = ?"
        );
        $stmt->execute([$examId]);
        $validAnswers = [];
        foreach ($stmt->fetchAll() as $row) {
            $questionId = (int) $row['question_id'];
            $validAnswers[$questionId] = $validAnswers[$questionId] ?? [];
            if ($row['answer_id'] !== null) {
                $validAnswers[$questionId][] = (int) $row['answer_id'];
            }
        }

        $delete = $this->pdo->prepare("DELETE FROM attempt_answers WHERE attempt_id = ?");
        $delete->execute([$attemptId]);
        $insert = $this->pdo->prepare(
            "INSERT INTO attempt_answers (attempt_id, question_id, answer_id, is_correct)
             VALUES (?, ?, ?, 0)"
        );
        foreach ($validAnswers as $questionId => $validIds) {
            $selectedIds = $answers[$questionId] ?? [];
            $selectedIds = is_array($selectedIds) ? $selectedIds : [$selectedIds];
            $selectedIds = array_values(array_unique(array_filter(array_map('intval', $selectedIds))));
            $selectedIds = array_values(array_intersect($selectedIds, $validIds));
            if (!$selectedIds) {
                $insert->execute([$attemptId, $questionId, null]);
                continue;
            }
            foreach ($selectedIds as $answerId) {
                $insert->execute([$attemptId, $questionId, $answerId]);
            }
        }
    }

    private function orderAttemptQuestions(array $questions, $seed, $shuffleQuestions, $shuffleAnswers) {
        foreach ($questions as &$question) {
            if (!$shuffleAnswers) {
                continue;
            }
            $questionId = (int) $question['question_id'];
            usort($question['answers'], function ($left, $right) use ($seed, $questionId) {
                $leftHash = hash_hmac('sha256', 'answer:' . $questionId . ':' . $left['answer_id'], $seed);
                $rightHash = hash_hmac('sha256', 'answer:' . $questionId . ':' . $right['answer_id'], $seed);
                $comparison = strcmp($leftHash, $rightHash);
                return $comparison !== 0 ? $comparison : ((int) $left['answer_id'] <=> (int) $right['answer_id']);
            });
        }
        unset($question);

        $questions = array_values($questions);
        if ($shuffleQuestions) {
            usort($questions, function ($left, $right) use ($seed) {
                $leftId = (int) $left['question_id'];
                $rightId = (int) $right['question_id'];
                $leftHash = hash_hmac('sha256', 'question:' . $leftId, $seed);
                $rightHash = hash_hmac('sha256', 'question:' . $rightId, $seed);
                $comparison = strcmp($leftHash, $rightHash);
                return $comparison !== 0 ? $comparison : ($leftId <=> $rightId);
            });
        }

        return $questions;
    }

    private function ensureUnansweredQuestions($attemptId, $examId) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO attempt_answers (attempt_id, question_id, answer_id, is_correct)
             SELECT ?, DISTINCT_QUESTIONS.question_id, NULL, 0
             FROM (
                 SELECT DISTINCT question_id
                 FROM exam_questions
                 WHERE exam_id = ?
             ) AS DISTINCT_QUESTIONS
             WHERE NOT EXISTS (
                   SELECT 1
                   FROM attempt_answers aa
                   WHERE aa.attempt_id = ? AND aa.question_id = DISTINCT_QUESTIONS.question_id
               )"
        );
        $stmt->execute([$attemptId, $examId, $attemptId]);
    }

    private function snapshotCorrectAnswers($attemptId, $examId) {
        $stmt = $this->pdo->prepare(
            "INSERT IGNORE INTO attempt_correct_answers (attempt_id, question_id, answer_id)
             SELECT ?, eq.question_id, a.answer_id
             FROM exam_questions eq
             JOIN answers a ON a.question_id = eq.question_id
             WHERE eq.exam_id = ? AND a.is_correct = TRUE"
        );
        $stmt->execute([$attemptId, $examId]);
    }

    private function finalizeAttempt($attemptId, $studentId, $answers, $reason) {
        $stmt = $this->pdo->prepare(
                "SELECT ea.attempt_id, ea.exam_id, ea.start_time,
                    COALESCE(ea.duration_minutes_snapshot, e.duration_minutes) AS duration_minutes
             FROM exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             WHERE ea.attempt_id = ? AND ea.student_id = ? AND ea.status = 'in_progress'
             FOR UPDATE"
        );
        $stmt->execute([$attemptId, $studentId]);
        $attempt = $stmt->fetch();
        if (!$attempt) {
            return false;
        }

        if (time() >= strtotime($attempt['start_time']) + ((int) $attempt['duration_minutes'] * 60)) {
            $reason = 'time_expired';
        }
        if ($answers !== null) {
            $this->saveAttemptAnswers($attemptId, (int) $attempt['exam_id'], $answers);
        } else {
            $this->ensureUnansweredQuestions($attemptId, (int) $attempt['exam_id']);
        }
        $this->snapshotCorrectAnswers($attemptId, (int) $attempt['exam_id']);

        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT question_id
             FROM exam_questions
             WHERE exam_id = ?"
        );
        $stmt->execute([(int) $attempt['exam_id']]);
        $examQuestions = $stmt->fetchAll();
        if (!$examQuestions) {
            throw new RuntimeException('Đề thi chưa có câu hỏi.');
        }
        $gradingError = $this->grading->validateExam((int) $attempt['exam_id']);
        if ($gradingError !== null) {
            throw new RuntimeException($gradingError);
        }

        $selectedStmt = $this->pdo->prepare(
            "SELECT answer_id FROM attempt_answers
             WHERE attempt_id = ? AND question_id = ? AND answer_id IS NOT NULL"
        );
        $correctStmt = $this->pdo->prepare(
            "SELECT answer_id FROM attempt_correct_answers
             WHERE attempt_id = ? AND question_id = ?"
        );
        $markAnswers = $this->pdo->prepare(
            "UPDATE attempt_answers SET is_correct = ?
             WHERE attempt_id = ? AND question_id = ?"
        );
        $score = 0;
        $correctQuestionCount = 0;
        foreach ($examQuestions as $examQuestion) {
            $questionId = (int) $examQuestion['question_id'];
            $selectedStmt->execute([$attemptId, $questionId]);
            $selectedIds = array_map('intval', $selectedStmt->fetchAll(PDO::FETCH_COLUMN));
            $correctStmt->execute([$attemptId, $questionId]);
            $correctIds = array_map('intval', $correctStmt->fetchAll(PDO::FETCH_COLUMN));
            sort($selectedIds);
            sort($correctIds);
            $isCorrect = $selectedIds === $correctIds && !empty($correctIds);
            $markAnswers->execute([$isCorrect ? 1 : 0, $attemptId, $questionId]);
            if ($isCorrect) {
                $correctQuestionCount++;
            }
        }
        $score = round(($correctQuestionCount / count($examQuestions)) * 10, 2);

        $reviewStatus = $reason === 'violation_limit' ? 'pending' : 'not_required';
        $update = $this->pdo->prepare(
            "UPDATE exam_attempts
             SET total_score = ?, end_time = NOW(), status = 'completed',
                 submit_reason = ?, review_status = ?
             WHERE attempt_id = ? AND status = 'in_progress'"
        );
        $update->execute([$score, $reason, $reviewStatus, $attemptId]);
        return true;
    }

    public function prepare() {
        $this->requireStudent();
        $examId = (int) ($_GET['exam_id'] ?? 0);
        $stmt = $this->pdo->prepare(
            "SELECT e.*, COUNT(eq.id) AS question_count
             FROM exams e
             LEFT JOIN exam_questions eq ON eq.exam_id = e.exam_id
             WHERE e.exam_id = ? AND e.status = 'published'
             GROUP BY e.exam_id"
        );
        $stmt->execute([$examId]);
        $exam = $stmt->fetch();

        if (!$exam) {
            die('Đề thi không tồn tại hoặc chưa được công khai.');
        }
        $gradingError = $this->grading->validateExam($examId);
        if ($gradingError !== null) {
            die($gradingError);
        }

        $studentId = (int) $_SESSION['user']['id'];
        $historyStmt = $this->pdo->prepare(
            "SELECT ea.attempt_id, ea.total_score, ea.start_time, ea.end_time,
                    ea.submit_reason, ea.review_status,
                    COUNT(DISTINCT CASE WHEN aa.is_correct = TRUE THEN aa.question_id END) AS correct_count,
                    COUNT(DISTINCT eq.question_id) AS question_count
             FROM exam_attempts ea
             LEFT JOIN attempt_answers aa ON aa.attempt_id = ea.attempt_id
             JOIN exam_questions eq ON eq.exam_id = ea.exam_id
             WHERE ea.exam_id = ? AND ea.student_id = ?
               AND ea.status IN ('completed', 'voided')
             GROUP BY ea.attempt_id, ea.total_score, ea.start_time, ea.end_time,
                      ea.submit_reason, ea.review_status
             ORDER BY ea.end_time DESC, ea.attempt_id DESC"
        );
        $historyStmt->execute([$examId, $studentId]);
        $attemptHistory = $historyStmt->fetchAll();
        $completedAttemptCount = count($attemptHistory);
        $activeAttemptStmt = $this->pdo->prepare(
            "SELECT attempt_id
             FROM exam_attempts
             WHERE exam_id = ? AND student_id = ? AND status = 'in_progress'
             ORDER BY attempt_id DESC LIMIT 1"
        );
        $activeAttemptStmt->execute([$examId, $studentId]);
        $activeAttemptId = $activeAttemptStmt->fetchColumn();

        $csrfToken = $_SESSION['csrf_token'];
        require __DIR__ . '/../views/exams/prepare.php';
    }

    public function start() {
        $this->requireStudent();
        $this->requirePostWithCsrf();
        $examId = (int) ($_POST['exam_id'] ?? 0);
        $studentId = (int) $_SESSION['user']['id'];
        $stmt = $this->pdo->prepare(
            "SELECT exam_id, duration_minutes, anti_cheat_enabled,
                violation_limit, violation_action, shuffle_questions, shuffle_answers
             FROM exams WHERE exam_id = ? AND status = 'published'"
        );
        $stmt->execute([$examId]);
        $exam = $stmt->fetch();
        if (!$exam) {
            http_response_code(404);
            exit('Đề thi không tồn tại hoặc chưa được công khai.');
        }
        $gradingError = $this->grading->validateExam($examId);
        if ($gradingError !== null) {
            http_response_code(422);
            exit($gradingError);
        }

        $attemptStmt = $this->pdo->prepare(
            "SELECT attempt_id, shuffle_seed FROM exam_attempts
             WHERE exam_id = ? AND student_id = ? AND status = 'in_progress'
             ORDER BY attempt_id DESC LIMIT 1"
        );
        $attemptStmt->execute([$examId, $studentId]);
        $attempt = $attemptStmt->fetch();
        $shuffleSeed = bin2hex(random_bytes(32));
        if (!$attempt) {
            $attemptStmt = $this->pdo->prepare(
                "INSERT INTO exam_attempts
                    (exam_id, student_id, status, duration_minutes_snapshot,
                     anti_cheat_enabled_snapshot, violation_limit_snapshot,
                     violation_action_snapshot, shuffle_questions_snapshot,
                     shuffle_answers_snapshot, shuffle_seed)
                 VALUES (?, ?, 'in_progress', ?, ?, ?, ?, ?, ?, ?)"
            );
            $attemptStmt->execute([
                $examId,
                $studentId,
                (int) $exam['duration_minutes'],
                (int) $exam['anti_cheat_enabled'],
                (int) $exam['violation_limit'],
                $exam['violation_action'],
                (int) $exam['shuffle_questions'],
                (int) $exam['shuffle_answers'],
                $shuffleSeed
            ]);
            $attemptId = $this->pdo->lastInsertId();
        } else {
            $attemptId = (int) $attempt['attempt_id'];
            if (empty($attempt['shuffle_seed'])) {
                $stmt = $this->pdo->prepare(
                    "UPDATE exam_attempts
                     SET shuffle_seed = ?,
                         shuffle_questions_snapshot = COALESCE(shuffle_questions_snapshot, ?),
                         shuffle_answers_snapshot = COALESCE(shuffle_answers_snapshot, ?)
                     WHERE attempt_id = ? AND student_id = ? AND status = 'in_progress'"
                );
                $stmt->execute([
                    $shuffleSeed,
                    (int) $exam['shuffle_questions'],
                    (int) $exam['shuffle_answers'],
                    $attemptId,
                    $studentId
                ]);
            }
        }
        $this->grading->snapshotCorrectAnswers((int) $attemptId, $examId);
        header('Location: index.php?action=take_exam&attempt_id=' . (int) $attemptId);
        exit;
    }

    public function take() {
        $this->requireStudent();
        $attemptId = (int) ($_GET['attempt_id'] ?? 0);
        $studentId = (int) $_SESSION['user']['id'];
        $stmt = $this->pdo->prepare(
            "SELECT ea.attempt_id, ea.exam_id, ea.start_time, ea.status,
                    e.title,
                    COALESCE(ea.duration_minutes_snapshot, e.duration_minutes) AS duration_minutes,
                    COALESCE(ea.anti_cheat_enabled_snapshot, e.anti_cheat_enabled) AS anti_cheat_enabled,
                    COALESCE(ea.violation_limit_snapshot, e.violation_limit) AS violation_limit,
                    COALESCE(ea.violation_action_snapshot, e.violation_action) AS violation_action,
                    COALESCE(ea.shuffle_questions_snapshot, e.shuffle_questions) AS shuffle_questions,
                    COALESCE(ea.shuffle_answers_snapshot, e.shuffle_answers) AS shuffle_answers,
                    ea.shuffle_seed,
                    COUNT(eq.id) AS question_count
             FROM exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             LEFT JOIN exam_questions eq ON eq.exam_id = e.exam_id
             WHERE ea.attempt_id = ? AND ea.student_id = ?
             GROUP BY ea.attempt_id"
        );
        $stmt->execute([$attemptId, $studentId]);
        $exam = $stmt->fetch();
        if (!$exam || $exam['status'] !== 'in_progress') {
            http_response_code(403);
            exit('Lượt thi không hợp lệ hoặc đã được nộp.');
        }
        if (empty($exam['shuffle_seed'])) {
            $shuffleSeed = bin2hex(random_bytes(32));
            $seedStmt = $this->pdo->prepare(
                "UPDATE exam_attempts
                 SET shuffle_seed = ?,
                     shuffle_questions_snapshot = COALESCE(shuffle_questions_snapshot, ?),
                     shuffle_answers_snapshot = COALESCE(shuffle_answers_snapshot, ?)
                 WHERE attempt_id = ? AND student_id = ? AND status = 'in_progress'"
            );
            $seedStmt->execute([
                $shuffleSeed,
                (int) $exam['shuffle_questions'],
                (int) $exam['shuffle_answers'],
                $attemptId,
                $studentId
            ]);
            $exam['shuffle_seed'] = $shuffleSeed;
        }
        $gradingError = $this->grading->validateExam((int) $exam['exam_id']);
        if ($gradingError !== null) {
            http_response_code(422);
            exit($gradingError);
        }

        if (time() >= strtotime($exam['start_time']) + ((int) $exam['duration_minutes'] * 60)) {
            $this->grading->finalize($attemptId, $studentId, null, 'time_expired');
            header('Location: index.php?action=exam_result&attempt_id=' . $attemptId);
            exit;
        }

        $stmt = $this->pdo->prepare(
            "SELECT q.question_id, q.content, q.question_type, a.answer_id, a.content AS answer_content
             FROM exam_questions eq
             JOIN questions q ON q.question_id = eq.question_id
             JOIN answers a ON a.question_id = q.question_id
             WHERE eq.exam_id = ?
             ORDER BY eq.order_index, q.question_id, a.order_index, a.answer_id"
        );
        $stmt->execute([(int) $exam['exam_id']]);
        $questions = [];
        foreach ($stmt->fetchAll() as $row) {
            $questionId = $row['question_id'];
            if (!isset($questions[$questionId])) {
                $questions[$questionId] = [
                    'question_id' => $questionId,
                    'content' => $row['content'],
                    'question_type' => $row['question_type'],
                    'answers' => []
                ];
            }
            $questions[$questionId]['answers'][] = [
                'answer_id' => $row['answer_id'],
                'content' => strip_tags(
                    $this->sanitizeRichText($row['answer_content']),
                    '<br><p><div><ul><ol><li>'
                )
            ];
        }
        $questions = $this->grading->orderQuestions(
            $questions,
            $exam['shuffle_seed'],
            (bool) $exam['shuffle_questions'],
            (bool) $exam['shuffle_answers']
        );

        $savedAnswers = [];
        $stmt = $this->pdo->prepare(
            "SELECT question_id, answer_id FROM attempt_answers
             WHERE attempt_id = ? AND answer_id IS NOT NULL"
        );
        $stmt->execute([$attemptId]);
        foreach ($stmt->fetchAll() as $savedAnswer) {
            $questionId = (int) $savedAnswer['question_id'];
            $savedAnswers[$questionId][] = (int) $savedAnswer['answer_id'];
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM violation_logs WHERE attempt_id = ?");
        $stmt->execute([$attemptId]);
        $violationCount = (int) $stmt->fetchColumn();
        $csrfToken = $_SESSION['csrf_token'];

        require __DIR__ . '/../views/exams/take.php';
    }

    public function updateSettings() {
        $this->requireLogin();
        $this->requirePostWithCsrf();
        $examId = (int) ($_POST['exam_id'] ?? 0);
        $teacherId = (int) $_SESSION['user']['id'];
        if (($_SESSION['user']['role'] ?? '') !== 'teacher') {
            http_response_code(403);
            exit('Chỉ giáo viên sở hữu đề thi mới được cấu hình.');
        }

        $duration = filter_var($_POST['duration_minutes'] ?? null, FILTER_VALIDATE_INT);
        $limit = filter_var($_POST['violation_limit'] ?? null, FILTER_VALIDATE_INT);
        $action = $_POST['violation_action'] ?? '';
        if (!$duration || $duration < 1 || $duration > 360 || !$limit || $limit < 1 || $limit > 20
            || !in_array($action, ['auto_submit', 'log_only'], true)) {
            http_response_code(400);
            exit('Thông số cấu hình không hợp lệ.');
        }

        $stmt = $this->pdo->prepare(
            "UPDATE exams SET duration_minutes = ?, anti_cheat_enabled = ?,
                violation_limit = ?, violation_action = ?,
                shuffle_questions = ?, shuffle_answers = ?
             WHERE exam_id = ? AND teacher_id = ?"
        );
        $stmt->execute([
            $duration,
            isset($_POST['anti_cheat_enabled']) ? 1 : 0,
            $limit,
            $action,
            isset($_POST['shuffle_questions']) ? 1 : 0,
            isset($_POST['shuffle_answers']) ? 1 : 0,
            $examId,
            $teacherId
        ]);
        header('Location: index.php?action=exam_statistics&exam_id=' . $examId . '&settings=saved');
        exit;
    }

    private function fetchMonitoringAttempts($examId, $teacherId) {
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
        return $stmt->fetchAll();
    }

    public function monitoring() {
        $this->requireLogin();
        $examId = (int) ($_GET['exam_id'] ?? 0);
        $teacherId = (int) $_SESSION['user']['id'];
        $stmt = $this->pdo->prepare(
            "SELECT exam_id, title FROM exams WHERE exam_id = ? AND teacher_id = ?"
        );
        $stmt->execute([$examId, $teacherId]);
        $exam = $stmt->fetch();
        if (!$exam || ($_SESSION['user']['role'] ?? '') !== 'teacher') {
            http_response_code(403);
            exit('Bạn không có quyền giám sát đề thi này.');
        }
        $attempts = $this->fetchMonitoringAttempts($examId, $teacherId);
        $csrfToken = $_SESSION['csrf_token'];
        require __DIR__ . '/../views/exams/monitoring.php';
    }

    public function monitoringData() {
        $this->requireLogin();
        $examId = (int) ($_GET['exam_id'] ?? 0);
        $teacherId = (int) $_SESSION['user']['id'];
        if (($_SESSION['user']['role'] ?? '') !== 'teacher') {
            $this->sendJson(['error' => 'Không có quyền xem dữ liệu giám sát.'], 403);
        }
        $attempts = $this->fetchMonitoringAttempts($examId, $teacherId);
        if (!$attempts) {
            $stmt = $this->pdo->prepare("SELECT 1 FROM exams WHERE exam_id = ? AND teacher_id = ?");
            $stmt->execute([$examId, $teacherId]);
            if (!$stmt->fetch()) {
                $this->sendJson(['error' => 'Không tìm thấy đề thi.'], 404);
            }
        }
        $this->sendJson(['attempts' => $attempts]);
    }

    public function reviewAttempt() {
        $this->requireLogin();
        $this->requirePostWithCsrf();
        $attemptId = (int) ($_POST['attempt_id'] ?? 0);
        $decision = $_POST['decision'] ?? '';
        $note = trim((string) ($_POST['review_note'] ?? ''));
        $note = substr($note, 0, 500);
        $teacherId = (int) $_SESSION['user']['id'];
        if (($_SESSION['user']['role'] ?? '') !== 'teacher'
            || !in_array($decision, ['void', 'allow_retry'], true)) {
            http_response_code(403);
            exit('Yêu cầu phúc khảo không hợp lệ.');
        }

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
        if (!$stmt->rowCount()) {
            http_response_code(404);
            exit('Không tìm thấy lượt thi đang chờ phúc khảo.');
        }
        header('Location: index.php?action=attempt_detail&attempt_id=' . $attemptId . '&review=saved');
        exit;
    }

    public function result() {
        $this->requireLogin();
        $attemptId = (int) ($_GET['attempt_id'] ?? 0);
        $stmt = $this->pdo->prepare(
            "SELECT ea.*, e.title, u.full_name
             FROM exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             JOIN users u ON u.user_id = ea.student_id
             WHERE ea.attempt_id = ? AND (ea.student_id = ? OR e.teacher_id = ?)"
        );
        $userId = (int) $_SESSION['user']['id'];
        $stmt->execute([$attemptId, $userId, $userId]);
        $attempt = $stmt->fetch();
        if (!$attempt) {
            die('Không tìm thấy kết quả bài làm.');
        }
        require __DIR__ . '/../views/exams/result.php';
    }

    public function statistics() {
        $this->requireLogin();
        $examId = (int) ($_GET['exam_id'] ?? 0);
        $userId = (int) $_SESSION['user']['id'];
        $stmt = $this->pdo->prepare(
                "SELECT e.exam_id, e.title, e.class_id
                    , e.duration_minutes, e.anti_cheat_enabled
                    , e.shuffle_questions, e.shuffle_answers
                    , e.violation_limit, e.violation_action
             FROM exams e
             WHERE e.exam_id = ? AND e.teacher_id = ?"
        );
        $stmt->execute([$examId, $userId]);
        $exam = $stmt->fetch();
        if (!$exam) {
            die('Bạn không có quyền xem thống kê đề thi này.');
        }
        $csrfToken = $_SESSION['csrf_token'];

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
            "SELECT COUNT(DISTINCT question_id)
             FROM exam_questions
             WHERE exam_id = ?"
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

        $passRate = $participantCount ? ($passCount / $participantCount) * 100 : null;

        $violationStmt = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM exam_attempts
             WHERE exam_id = ? AND status = 'completed' AND review_status = 'pending'"
        );
        $violationStmt->execute([$examId]);
        $violationWarningCount = (int) $violationStmt->fetchColumn();

        $distributionMode = $_GET['distribution_mode'] ?? 'highest';
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
                    || ($score['end_time'] === $current['end_time'] && (int) $score['attempt_id'] > (int) $current['attempt_id']);
                if (($distributionMode === 'highest' && ($isHigher || ((float) $score['total_score'] === (float) $current['total_score'] && $isLater)))
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
               ON aa.attempt_id = ea.attempt_id
              AND aa.question_id = eq.question_id
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
        $correctPercentage = $totalAnsweredQuestions
            ? ($totalCorrectAnswers / $totalAnsweredQuestions) * 100
            : 0;
        $wrongPercentage = $totalAnsweredQuestions
            ? ($totalWrongAnswers / $totalAnsweredQuestions) * 100
            : 0;
        $topDifficultQuestions = $questionStats;
        usort($topDifficultQuestions, function ($left, $right) {
            return $right['wrong_rate'] <=> $left['wrong_rate'];
        });
        $topDifficultQuestions = array_slice($topDifficultQuestions, 0, 5);
        require __DIR__ . '/../views/exams/statistics.php';
    }

    public function attemptDetail() {
        $this->requireLogin();
        $attemptId = (int) ($_GET['attempt_id'] ?? 0);
        $userId = (int) $_SESSION['user']['id'];

        $stmt = $this->pdo->prepare(
            "SELECT ea.*, e.title, e.teacher_id, u.full_name
             FROM exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             JOIN users u ON u.user_id = ea.student_id
             WHERE ea.attempt_id = ? AND ea.status IN ('completed', 'voided')
             AND (e.teacher_id = ? OR ea.student_id = ?)"
        );
        $stmt->execute([$attemptId, $userId, $userId]);
        $attempt = $stmt->fetch();
        if (!$attempt) {
            die('Không tìm thấy lượt nộp hoặc bạn không có quyền xem lượt nộp này.');
        }

        $stmt = $this->pdo->prepare(
            "SELECT violation_type, details, occurred_at
             FROM violation_logs WHERE attempt_id = ? ORDER BY occurred_at"
        );
        $stmt->execute([$attemptId]);
        $violationLogs = $stmt->fetchAll();
        $csrfToken = $_SESSION['csrf_token'];

        $stmt = $this->pdo->prepare(
            "SELECT eq.question_id, eq.order_index, q.content, q.question_type,
                    a.answer_id, a.content AS answer_content,
                    CASE WHEN EXISTS (
                        SELECT 1 FROM attempt_correct_answers snapshot
                        WHERE snapshot.attempt_id = ?
                    ) THEN aca.answer_id IS NOT NULL ELSE a.is_correct END AS is_correct
             FROM exam_questions eq
             JOIN questions q ON q.question_id = eq.question_id
             LEFT JOIN answers a ON a.question_id = q.question_id
             LEFT JOIN attempt_correct_answers aca
               ON aca.attempt_id = ? AND aca.question_id = q.question_id
              AND aca.answer_id = a.answer_id
             WHERE eq.exam_id = ?
             ORDER BY eq.order_index, q.question_id, a.order_index, a.answer_id"
        );
        $stmt->execute([$attemptId, $attemptId, (int) $attempt['exam_id']]);
        $questions = [];
        foreach ($stmt->fetchAll() as $row) {
            $questionId = (int) $row['question_id'];
            if (!isset($questions[$questionId])) {
                $questions[$questionId] = [
                    'question_id' => $questionId,
                    'content' => $row['content'],
                    'question_type' => $row['question_type'],
                    'answers' => [],
                    'selected_ids' => [],
                    'correct' => false
                ];
            }
            if ($row['answer_id'] !== null) {
                $questions[$questionId]['answers'][] = [
                    'answer_id' => (int) $row['answer_id'],
                    'content' => $row['answer_content'],
                    'is_correct' => (bool) $row['is_correct']
                ];
            }
        }

        $stmt = $this->pdo->prepare(
            "SELECT question_id, answer_id
             FROM attempt_answers
             WHERE attempt_id = ? AND answer_id IS NOT NULL"
        );
        $stmt->execute([$attemptId]);
        foreach ($stmt->fetchAll() as $selected) {
            $questionId = (int) $selected['question_id'];
            if (isset($questions[$questionId])) {
                $questions[$questionId]['selected_ids'][] = (int) $selected['answer_id'];
            }
        }

        foreach ($questions as &$question) {
            $correctIds = [];
            foreach ($question['answers'] as $answer) {
                if ($answer['is_correct']) {
                    $correctIds[] = $answer['answer_id'];
                }
            }
            $selectedIds = $question['selected_ids'];
            sort($correctIds);
            sort($selectedIds);
            $question['correct'] = $selectedIds === $correctIds && !empty($correctIds);
        }
        unset($question);

        require __DIR__ . '/../views/exams/attempt-detail.php';
    }

    public function studentStatistics() {
        $this->requireLogin();
        $studentId = (int) $_SESSION['user']['id'];
        $selectedExamId = (int) ($_GET['exam_id'] ?? 0);
        $selectedExamType = is_string($_GET['exam_type'] ?? null) ? $_GET['exam_type'] : '';
        $examTypeConditions = [
            'auto' => "(LOWER(e.title) LIKE '%tự động%' OR LOWER(e.title) LIKE '%tu dong%' OR LOWER(e.title) LIKE '%auto%')",
            'sample' => "(LOWER(e.title) LIKE '%đề kiểm tra mẫu%' OR LOWER(e.title) LIKE '%đề mẫu%' OR LOWER(e.title) LIKE '%de kiem tra mau%' OR LOWER(e.title) LIKE '%de mau%')",
            'hop' => "(LOWER(e.title) LIKE '%kiểm tra hợp%' OR LOWER(e.title) LIKE '%kiem tra hop%')"
        ];
        if (!isset($examTypeConditions[$selectedExamType])) {
            $selectedExamType = '';
        }

        $stmt = $this->pdo->prepare(
            "SELECT ea.attempt_id, ea.exam_id, e.title, ea.total_score, ea.start_time, ea.end_time,
                    10 AS max_score,
                    ea.submit_reason, ea.review_status,
                    COUNT(DISTINCT eq.question_id) AS question_count,
                    COUNT(DISTINCT CASE WHEN aa.is_correct = TRUE THEN aa.question_id END) AS correct_count,
                    COUNT(DISTINCT CASE WHEN aa.is_correct = FALSE THEN aa.question_id END) AS wrong_count
             FROM exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             LEFT JOIN exam_questions eq ON eq.exam_id = ea.exam_id
             LEFT JOIN attempt_answers aa ON aa.attempt_id = ea.attempt_id
             WHERE ea.student_id = ? AND ea.status = 'completed'"
            . ($selectedExamId ? " AND ea.exam_id = ?" : "")
            . ($selectedExamType !== '' ? " AND " . $examTypeConditions[$selectedExamType] : "") . "
             GROUP BY ea.attempt_id, ea.exam_id, e.title, ea.total_score, ea.start_time,
                      ea.end_time, ea.submit_reason, ea.review_status
             ORDER BY ea.end_time ASC"
        );
        $params = [$studentId];
        if ($selectedExamId) {
            $params[] = $selectedExamId;
        }
        $stmt->execute($params);
        $attempts = $stmt->fetchAll();

        $examStmt = $this->pdo->prepare(
            "SELECT DISTINCT e.exam_id, e.title
             FROM exam_attempts ea
             JOIN exams e ON e.exam_id = ea.exam_id
             WHERE ea.student_id = ? AND ea.status = 'completed'
             ORDER BY e.title"
        );
        $examStmt->execute([$studentId]);
        $availableExams = $examStmt->fetchAll();

        $totalCorrect = 0;
        $totalWrong = 0;
        $totalDurationSeconds = 0;
        foreach ($attempts as $attempt) {
            $totalCorrect += (int) $attempt['correct_count'];
            $totalWrong += (int) $attempt['wrong_count'];
            $totalDurationSeconds += max(0, strtotime($attempt['end_time']) - strtotime($attempt['start_time']));
        }
        $attemptCount = count($attempts);
        $examCount = count(array_unique(array_column($attempts, 'exam_id')));
        $averageScore = $attemptCount
            ? array_sum(array_map(function ($attempt) {
                return (float) $attempt['total_score'];
            }, $attempts)) / $attemptCount
            : 0;
        $accuracy = ($totalCorrect + $totalWrong)
            ? ($totalCorrect / ($totalCorrect + $totalWrong)) * 100
            : 0;
        $averageDurationSeconds = $attemptCount ? (int) round($totalDurationSeconds / $attemptCount) : 0;

        require __DIR__ . '/../views/exams/student-statistics.php';
    }
}
?>
