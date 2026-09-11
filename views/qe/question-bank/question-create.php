<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tạo câu hỏi - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../qe.css">
</head>

<body>
    <?php
    // GIẢ LẬP DATA
    $banks = [
        ['bank_id' => 1, 'name' => 'Lập trình cơ bản'],
        ['bank_id' => 2, 'name' => 'Toán học'],
        ['bank_id' => 3, 'name' => 'Tiếng Anh cơ bản'],
        ['bank_id' => 4, 'name' => 'Cơ sở dữ liệu'],
    ];
    ?>

    <div class="member2-app">
        <aside class="member2-sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                <div>
                    <div class="sidebar-brand-title">EduTest</div>
                    <div class="sidebar-brand-subtitle">Quản lý học tập</div>
                </div>
            </div>

            <div class="sidebar-section-label">Không gian làm việc</div>
            <nav class="sidebar-menu">
                <a href="#"><i class="fa-solid fa-gauge-high"></i><span>Bảng điều khiển</span></a>
                <a class="active" href="index.php"><i class="fa-solid fa-layer-group"></i><span>Ngân hàng câu hỏi</span></a>
                <a href="../exam/index.php"><i class="fa-solid fa-file-signature"></i><span>Bài thi</span></a>
            </nav>

            <div class="sidebar-user">
                <div class="sidebar-user-card">
                    <div class="sidebar-user-avatar">M2</div>
                    <div>
                        <div class="sidebar-user-name">Thành viên 2</div>
                        <div class="sidebar-user-role">Quản trị đánh giá</div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="member2-main">
            <nav class="member2-topbar">
                <div class="d-flex align-items-center">
                    <button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('show')">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="topbar-title">Tạo câu hỏi</div>
                </div>
                <div class="topbar-actions">
                    <button class="icon-button"><i class="fa-solid fa-bell"></i></button>
                    <div class="user-chip">
                        <i class="fa-solid fa-user"></i>
                        <span>Thành viên 2</span>
                    </div>
                </div>
            </nav>

            <section class="member2-page">
                <div class="page-header">
                    <div class="page-title-wrap">
                        <h1>Tạo câu hỏi</h1>
                        <div class="text-muted">Thêm mới câu hỏi vào ngân hàng</div>
                    </div>
                    <div class="page-actions">
                        <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Quay lại</a>
                    </div>
                </div>

                <div class="form-wrap">
                    <form method="POST" action="../../controllers/QuestionBankController.php?action=storeQuestion">
                        <div class="form-section">
                            <div class="form-title mb-4">Thông tin câu hỏi</div>
                            <div class="grid-form">
                                <div class="mb-3">
                                    <label class="form-label">Ngân hàng câu hỏi <span class="text-danger">*</span></label>
                                    <select class="form-select" name="bank_id" required>
                                        <?php foreach ($banks as $bank): ?>
                                            <option value="<?= $bank['bank_id'] ?>"><?= htmlspecialchars($bank['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Loại câu hỏi <span class="text-danger">*</span></label>
                                    <select class="form-select" name="question_type" id="question_type" required>
                                        <option value="single_choice">Trắc nghiệm một đáp án</option>
                                        <option value="multiple_choice">Trắc nghiệm nhiều đáp án</option>
                                        <option value="fill_blank">Điền khuyết</option>
                                        <option value="essay">Tự luận</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Độ khó</label>
                                    <select class="form-select" name="difficulty">
                                        <option>Cơ bản</option>
                                        <option>Trung bình</option>
                                        <option>Nâng cao</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select class="form-select" name="status">
                                        <option>Hoạt động</option>
                                        <option>Nháp</option>
                                        <option>Lưu trữ</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nội dung câu hỏi <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="question_text" rows="4" placeholder="Nhập nội dung câu hỏi" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Giải thích</label>
                                <textarea class="form-control" name="explanation" rows="3" placeholder="Giải thích đáp án"></textarea>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-title mb-4">Đáp án và đáp án đúng</div>

                            <div id="answers-wrapper">
                                <div class="row mb-3 answer-row">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="answers[]" placeholder="Nội dung đáp án">
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select" name="is_correct[]">
                                            <option value="0">Sai</option>
                                            <option value="1">Đúng</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger remove-answer"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </div>
                                <div class="row mb-3 answer-row">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="answers[]" placeholder="Nội dung đáp án">
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select" name="is_correct[]">
                                            <option value="0">Sai</option>
                                            <option value="1">Đúng</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger remove-answer"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </div>
                                <div class="row mb-3 answer-row">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="answers[]" placeholder="Nội dung đáp án">
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select" name="is_correct[]">
                                            <option value="0">Sai</option>
                                            <option value="1">Đúng</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger remove-answer"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </div>
                                <div class="row mb-3 answer-row">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="answers[]" placeholder="Nội dung đáp án">
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select" name="is_correct[]">
                                            <option value="0">Sai</option>
                                            <option value="1">Đúng</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger remove-answer"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-primary" id="add-answer"><i class="fa-solid fa-plus me-2"></i>Thêm đáp án</button>
                            </div>

                            <div class="mb-3" id="fill-blank-wrap" style="display: none;">
                                <label class="form-label">Đáp án điền khuyết</label>
                                <input type="text" class="form-control" name="fill_blank_answer" placeholder="Ví dụ: 5">
                            </div>

                            <div class="mb-3" id="essay-wrap" style="display: none;">
                                <label class="form-label">Mẫu đáp án tự luận</label>
                                <textarea class="form-control" name="essay_answer" rows="4" placeholder="Mô tả mẫu lời giải hoặc gợi ý chấm"></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-xmark me-2"></i>Hủy</a>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Tạo câu hỏi</button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const typeSelect = document.getElementById('question_type');
        const fillBlankWrap = document.getElementById('fill-blank-wrap');
        const essayWrap = document.getElementById('essay-wrap');
        const answerWrapper = document.getElementById('answers-wrapper');

        function updateQuestionTypeUI() {
            const type = typeSelect.value;
            const isChoice = type === 'single_choice' || type === 'multiple_choice';
            const isFillBlank = type === 'fill_blank';
            const isEssay = type === 'essay';

            answerWrapper.style.display = isChoice ? 'block' : 'none';
            fillBlankWrap.style.display = isFillBlank ? 'block' : 'none';
            essayWrap.style.display = isEssay ? 'block' : 'none';

            if (type === 'single_choice') {
                document.querySelectorAll('select[name="is_correct[]"]').forEach(select => {
                    select.innerHTML = '<option value="0">Sai</option><option value="1">Đúng</option>';
                });
            }

            if (type === 'multiple_choice') {
                document.querySelectorAll('select[name="is_correct[]"]').forEach(select => {
                    select.innerHTML = '<option value="0">Sai</option><option value="1">Đúng</option>';
                });
            }

            if (type === 'fill_blank') {
                document.querySelectorAll('select[name="is_correct[]"]').forEach(select => {
                    select.innerHTML = '<option value="1">Đáp án chính xác</option>';
                });
            }

            if (type === 'essay') {
                answerWrapper.style.display = 'none';
                fillBlankWrap.style.display = 'none';
            }
        }

        typeSelect.addEventListener('change', updateQuestionTypeUI);

        document.getElementById('add-answer').addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'row mb-3 answer-row';
            row.innerHTML = `<div class="col-md-8">
                                <input type="text" class="form-control" name="answers[]" placeholder="Nội dung đáp án">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="is_correct[]">
                                    <option value="0">Sai</option>
                                    <option value="1">Đúng</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger remove-answer"><i class="fa-solid fa-trash"></i></button>
                            </div>`;
            answerWrapper.appendChild(row);
        });

        document.addEventListener('click', function(event) {
            if (event.target.closest('.remove-answer')) {
                const row = event.target.closest('.answer-row');
                if (row && answerWrapper.querySelectorAll('.answer-row').length > 1) {
                    row.remove();
                }
            }
        });

        updateQuestionTypeUI();
    </script>
</body>

</html>