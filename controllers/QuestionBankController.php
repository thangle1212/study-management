<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/QuestionBank.php';

class QuestionBankController extends BaseController {
    private $questionBank;

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->questionBank = new QuestionBank($pdo);
    }

    // BEGIN QUESTION BANK MANAGEMENT
    public function index() {
        $this->requireTeacher();
        $banks = $this->questionBank->getBanksForOwner($this->currentUserId());
        $success = $_GET['success'] ?? '';
        $error = $_GET['error'] ?? '';
        require __DIR__ . '/../views/qe/question-bank/index.php';
    }

    public function createBank() {
        $this->requireTeacher();
        $bank = ['bank_name' => '', 'subject' => ''];
        $this->handleBankForm($bank);
    }

    public function editBank() {
        $this->requireTeacher();
        $bankId = $this->requestId('id');
        $bank = $this->questionBank->getBank($bankId, $this->currentUserId());
        if (!$bank) {
            $this->notFound();
        }
        $this->handleBankForm($bank, $bankId);
    }

    public function showBank() {
        $this->requireTeacher();
        $bankId = $this->requestId('id');
        $bank = $this->questionBank->getBank($bankId, $this->currentUserId());
        if (!$bank) {
            $this->notFound();
        }
        $questions = $this->questionBank->getQuestionsForBank($bankId, $this->currentUserId());
        $success = $_GET['success'] ?? '';
        $error = $_GET['error'] ?? '';
        require __DIR__ . '/../views/qe/question-bank/show.php';
    }

    public function deleteBank() {
        $this->requireTeacher();
        $this->requirePostWithCsrf();
        $bankId = $this->requestId('id');
        try {
            $this->questionBank->deleteBank($bankId, $this->currentUserId());
            $this->redirect('qe_banks', ['success' => 'bank_deleted']);
        } catch (DomainException $exception) {
            $this->redirect('qe_banks', ['error' => $exception->getMessage()]);
        } catch (PDOException $exception) {
            error_log('Question bank deletion failed: ' . $exception->getMessage());
            $this->redirect('qe_banks', ['error' => 'Không thể xóa ngân hàng do dữ liệu đang được sử dụng.']);
        }
    }

    public function createQuestion() {
        $this->requireTeacher();
        $question = $this->emptyQuestion();
        $this->handleQuestionForm($question);
    }

    public function editQuestion() {
        $this->requireTeacher();
        $questionId = $this->requestId('id');
        $question = $this->questionBank->getQuestion($questionId, $this->currentUserId());
        if (!$question) {
            $this->notFound();
        }
        $this->handleQuestionForm($question, $questionId);
    }

    public function deleteQuestion() {
        $this->requireTeacher();
        $this->requirePostWithCsrf();
        $questionId = $this->requestId('id');
        $bankId = $this->requestId('bank_id');
        try {
            $this->questionBank->deleteQuestion($questionId, $this->currentUserId());
            $this->redirect('qe_bank_show', ['id' => $bankId, 'success' => 'question_deleted']);
        } catch (DomainException $exception) {
            $this->redirect('qe_bank_show', ['id' => $bankId, 'error' => $exception->getMessage()]);
        } catch (PDOException $exception) {
            error_log('Question deletion failed: ' . $exception->getMessage());
            $this->redirect('qe_bank_show', ['id' => $bankId, 'error' => 'Không thể xóa câu hỏi do dữ liệu đang được sử dụng.']);
        }
    }
    // END QUESTION BANK MANAGEMENT

    // BEGIN QUESTION BANK AND QUESTION FORM HANDLING
    private function handleBankForm($bank, $bankId = null) {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostWithCsrf();
            try {
                if (!is_string($_POST['bank_name'] ?? null) || !is_string($_POST['subject'] ?? '')) {
                    throw new InvalidArgumentException('Thông tin ngân hàng câu hỏi không hợp lệ.');
                }
                $bank['bank_name'] = trim($_POST['bank_name']);
                $bank['subject'] = trim($_POST['subject'] ?? '');
                if ($bank['bank_name'] === '' || mb_strlen($bank['bank_name'], 'UTF-8') > 150) {
                    throw new InvalidArgumentException('Tên ngân hàng là bắt buộc và tối đa 150 ký tự.');
                }
                if (mb_strlen($bank['subject'], 'UTF-8') > 100) {
                    throw new InvalidArgumentException('Tên môn học không được dài quá 100 ký tự.');
                }
                if ($bankId === null) {
                    $bankId = $this->questionBank->createBank(
                        $this->currentUserId(),
                        $bank['bank_name'],
                        $bank['subject'] === '' ? null : $bank['subject']
                    );
                    $this->redirect('qe_bank_show', ['id' => $bankId, 'success' => 'bank_created']);
                }
                $this->questionBank->updateBank(
                    $bankId,
                    $this->currentUserId(),
                    $bank['bank_name'],
                    $bank['subject'] === '' ? null : $bank['subject']
                );
                $this->redirect('qe_bank_show', ['id' => $bankId, 'success' => 'bank_updated']);
            } catch (InvalidArgumentException $exception) {
                $errors[] = $exception->getMessage();
            } catch (PDOException $exception) {
                error_log('Question bank save failed: ' . $exception->getMessage());
                $errors[] = 'Không thể lưu ngân hàng câu hỏi lúc này. Vui lòng thử lại.';
            }
        }
        $isEditing = $bankId !== null;
        $csrfToken = $_SESSION['csrf_token'];
        require __DIR__ . '/../views/qe/question-bank/form.php';
    }

    private function handleQuestionForm($question, $questionId = null) {
        $teacherId = $this->currentUserId();
        $banks = $this->questionBank->getBanksForOwner($teacherId);
        $errors = [];
        $wasEditing = $questionId !== null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostWithCsrf();
            $question = array_merge($question, [
                'bank_id' => $_POST['bank_id'] ?? '',
                'content' => $_POST['content'] ?? '',
                'image_url' => $_POST['image_url'] ?? '',
                'question_type' => $_POST['question_type'] ?? 'single_choice',
                'difficulty_level' => $_POST['difficulty_level'] ?? 'medium'
            ]);
            $question['answers'] = [];
            $postedAnswers = $_POST['answers'] ?? [];
            if (is_array($postedAnswers)) {
                foreach ($postedAnswers as $answer) {
                    $question['answers'][] = [
                        'content' => is_string($answer) ? $answer : '',
                        'is_correct' => false
                    ];
                }
            }
            try {
                $questionData = $this->validateQuestionInput($_POST);
                if ($questionId === null) {
                    $questionId = $this->questionBank->createQuestion($teacherId, $questionData);
                } else {
                    $this->questionBank->updateQuestion($questionId, $teacherId, $questionData);
                }
                $this->redirect('qe_bank_show', [
                    'id' => $questionData['bank_id'],
                    'success' => $wasEditing ? 'question_saved' : 'question_created'
                ]);
            } catch (InvalidArgumentException | DomainException $exception) {
                $errors[] = $exception->getMessage();
            } catch (PDOException $exception) {
                error_log('Question save failed: ' . $exception->getMessage());
                $errors[] = 'Không thể lưu câu hỏi lúc này. Vui lòng thử lại.';
            }
        }
        $isEditing = $questionId !== null;
        $csrfToken = $_SESSION['csrf_token'];
        require __DIR__ . '/../views/qe/question-bank/question-form.php';
    }
    // END QUESTION BANK AND QUESTION FORM HANDLING

    // BEGIN QUESTION INPUT VALIDATION
    private function validateQuestionInput($input) {
        $bankId = filter_var($input['bank_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$bankId || !$this->questionBank->getBank($bankId, $this->currentUserId())) {
            throw new InvalidArgumentException('Vui lòng chọn ngân hàng câu hỏi thuộc quyền quản lý của bạn.');
        }
        $content = $input['content'] ?? null;
        if (!is_string($content) || trim($content) === '' || mb_strlen(trim($content), 'UTF-8') > 5000) {
            throw new InvalidArgumentException('Nội dung câu hỏi là bắt buộc và tối đa 5.000 ký tự.');
        }
        $type = $input['question_type'] ?? '';
        if (!is_string($type) || !in_array($type, ['single_choice', 'multiple_choice'], true)) {
            throw new InvalidArgumentException('Loại câu hỏi không hợp lệ.');
        }
        $difficulty = $input['difficulty_level'] ?? '';
        if (!is_string($difficulty) || !in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            throw new InvalidArgumentException('Độ khó không hợp lệ.');
        }
        $imageUrl = $input['image_url'] ?? '';
        if (!is_string($imageUrl) || mb_strlen($imageUrl, 'UTF-8') > 255
            || ($imageUrl !== '' && filter_var($imageUrl, FILTER_VALIDATE_URL) === false)
            || ($imageUrl !== '' && !preg_match('/^https?:\/\//i', $imageUrl))) {
            throw new InvalidArgumentException('Đường dẫn hình ảnh phải là URL HTTP/HTTPS hợp lệ (tối đa 255 ký tự).');
        }
        $answersInput = $input['answers'] ?? null;
        if (!is_array($answersInput) || count($answersInput) < 2 || count($answersInput) > 10) {
            throw new InvalidArgumentException('Câu hỏi cần có từ 2 đến 10 đáp án.');
        }
        $correctInput = $input['correct_answers'] ?? [];
        if (!is_array($correctInput)) {
            throw new InvalidArgumentException('Danh sách đáp án đúng không hợp lệ.');
        }
        $correctIndexes = [];
        foreach ($correctInput as $correctIndex) {
            $parsedIndex = filter_var($correctIndex, FILTER_VALIDATE_INT);
            if ($parsedIndex === false || $parsedIndex < 0 || $parsedIndex >= count($answersInput)) {
                throw new InvalidArgumentException('Đáp án đúng không hợp lệ.');
            }
            $correctIndexes[$parsedIndex] = true;
        }
        if (($type === 'single_choice' && count($correctIndexes) !== 1)
            || ($type === 'multiple_choice' && count($correctIndexes) < 1)) {
            throw new InvalidArgumentException('Hãy chọn đúng một đáp án cho câu một lựa chọn, hoặc ít nhất một đáp án cho câu nhiều lựa chọn.');
        }
        $answers = [];
        foreach (array_values($answersInput) as $index => $answer) {
            if (!is_string($answer) || trim($answer) === '' || mb_strlen(trim($answer), 'UTF-8') > 2000) {
                throw new InvalidArgumentException('Mỗi đáp án phải có nội dung (tối đa 2.000 ký tự).');
            }
            $answers[] = [
                'content' => trim($answer),
                'is_correct' => isset($correctIndexes[$index])
            ];
        }
        return [
            'bank_id' => (int) $bankId,
            'content' => trim($content),
            'image_url' => $imageUrl === '' ? null : $imageUrl,
            'question_type' => $type,
            'difficulty_level' => $difficulty,
            'answers' => $answers
        ];
    }

    private function emptyQuestion() {
        $bankId = filter_var($_GET['bank_id'] ?? null, FILTER_VALIDATE_INT);
        return [
            'bank_id' => $bankId && $bankId > 0 ? (int) $bankId : '',
            'content' => '',
            'image_url' => '',
            'question_type' => 'single_choice',
            'difficulty_level' => 'medium',
            'answers' => [
                ['content' => '', 'is_correct' => false],
                ['content' => '', 'is_correct' => false],
                ['content' => '', 'is_correct' => false],
                ['content' => '', 'is_correct' => false]
            ]
        ];
    }
    // END QUESTION INPUT VALIDATION

    // BEGIN REQUEST HELPERS
    private function currentUserId() {
        return (int) $_SESSION['user']['id'];
    }

    private function requestId($key) {
        $id = filter_var($_GET[$key] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            $this->notFound();
        }
        return (int) $id;
    }

    private function notFound() {
        http_response_code(404);
        exit('Không tìm thấy dữ liệu hoặc bạn không có quyền truy cập.');
    }

    private function redirect($action, $parameters = []) {
        $query = http_build_query(array_merge(['action' => $action], $parameters));
        header('Location: index.php?' . $query);
        exit;
    }
    // END REQUEST HELPERS
}
