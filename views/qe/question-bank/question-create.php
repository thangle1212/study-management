<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tạo câu hỏi - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../qe.css">
    <style>
        .toolbar-mini { display:flex; gap:4px; padding:6px 8px; background:#f8f9fa; border:1px solid #e9ecef; border-bottom:none; border-radius:12px 12px 0 0; }
        .toolbar-mini button { width:32px; height:32px; border:none; background:transparent; border-radius:6px; color:#495057; cursor:pointer; font-size:0.85rem; transition:all 0.2s; display:flex; align-items:center; justify-content:center; }
        .toolbar-mini button:hover { background:#e7f1ff; color:#0d6efd; }
        .toolbar-mini button:active { background:#0d6efd; color:#fff; }
        .auto-textarea { min-height:80px; max-height:400px; overflow-y:auto; resize:none; border-radius:0 0 12px 12px !important; font-family:inherit; line-height:1.6; }
        .answer-row { display:flex; flex-direction:column; gap:8px; padding:12px; border:1px solid #e9ecef; border-radius:12px; background:#fafbfc; margin-bottom:10px; transition:all 0.2s; }
        .answer-row:hover { border-color:#cfd8e3; background:#fff; }
        .answer-header { display:flex; align-items:center; gap:10px; }
        .answer-label { width:28px; height:28px; border-radius:50%; background:#e7f1ff; color:#0d6efd; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.8rem; flex-shrink:0; }
        .answer-header input[type="radio"], .answer-header input[type="checkbox"] { width:20px; height:20px; cursor:pointer; }
        .answer-header .btn-remove-answer { margin-left:auto; width:28px; height:28px; padding:0; border-radius:6px; font-size:0.75rem; }
        .preview-box { background:#f8f9fa; border:1px solid #e9ecef; border-radius:12px; padding:20px; min-height:150px; }
        .preview-box .preview-label { font-size:0.7rem; font-weight:600; color:#6c757d; text-transform:uppercase; margin-bottom:10px; letter-spacing:0.05em; }
        .preview-box .question-render { font-weight:600; font-size:1rem; margin-bottom:16px; line-height:1.6; padding-bottom:16px; border-bottom:1px dashed #cfd8e3; }
        .preview-box .answer-render { padding:8px 12px; margin-bottom:6px; border-radius:8px; font-size:0.9rem; display:flex; align-items:center; gap:10px; }
        .preview-box .answer-render.correct { background:#e7f1ff; border-left:3px solid #0d6efd; font-weight:600; }
    </style>
</head>

<body>
    <?php
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
                    <div class="user-chip"><i class="fa-solid fa-user"></i><span>Thành viên 2</span></div>
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
                                <div class="toolbar-mini">
                                    <button type="button" class="format-btn" data-before="**" data-after="**" title="In đậm">[B]</button>
                                    <button type="button" class="format-btn" data-before="*" data-after="*" title="In nghiêng">[I]</button>
                                    <button type="button" class="format-btn" data-before="__" data-after="__" title="Gạch chân">[U]</button>
                                    <button type="button" class="format-btn" data-before="~" data-after="~" title="Chỉ số dưới">[x2]</button>
                                    <button type="button" class="format-btn" data-before="^" data-after="^" title="Chỉ số trên">[x²]</button>
                                </div>
                                <textarea class="form-control auto-textarea" id="question_content" name="content" rows="4" placeholder="Nhập câu hỏi..." required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Giải thích</label>
                                <textarea class="form-control" name="explanation" rows="3" placeholder="Giải thích đáp án"></textarea>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-title mb-4">Đáp án và đáp án đúng</div>

                            <div id="answers-wrapper">
                                <div class="answer-row" data-answer-index="0">
                                    <div class="answer-header">
                                        <span class="answer-label">A</span>
                                        <input type="radio" class="answer-choice single-choice" name="is_correct[]" value="0">
                                        <button type="button" class="btn btn-outline-danger btn-remove-answer remove-answer"><i class="fa-solid fa-xmark"></i></button>
                                    </div>
                                    <div class="toolbar-mini answer-toolbar">
                                        <button type="button" class="format-btn" data-before="**" data-after="**" title="In đậm">[B]</button>
                                        <button type="button" class="format-btn" data-before="*" data-after="*" title="In nghiêng">[I]</button>
                                        <button type="button" class="format-btn" data-before="__" data-after="__" title="Gạch chân">[U]</button>
                                    </div>
                                    <textarea class="form-control auto-textarea" name="answers[]" rows="2" placeholder="Nội dung đáp án..."></textarea>
                                </div>
                                <div class="answer-row" data-answer-index="1">
                                    <div class="answer-header">
                                        <span class="answer-label">B</span>
                                        <input type="radio" class="answer-choice single-choice" name="is_correct[]" value="1">
                                        <button type="button" class="btn btn-outline-danger btn-remove-answer remove-answer"><i class="fa-solid fa-xmark"></i></button>
                                    </div>
                                    <div class="toolbar-mini answer-toolbar">
                                        <button type="button" class="format-btn" data-before="**" data-after="**" title="In đậm">[B]</button>
                                        <button type="button" class="format-btn" data-before="*" data-after="*" title="In nghiêng">[I]</button>
                                        <button type="button" class="format-btn" data-before="__" data-after="__" title="Gạch chân">[U]</button>
                                    </div>
                                    <textarea class="form-control auto-textarea" name="answers[]" rows="2" placeholder="Nội dung đáp án..."></textarea>
                                </div>
                                <div class="answer-row" data-answer-index="2">
                                    <div class="answer-header">
                                        <span class="answer-label">C</span>
                                        <input type="radio" class="answer-choice single-choice" name="is_correct[]" value="2">
                                        <button type="button" class="btn btn-outline-danger btn-remove-answer remove-answer"><i class="fa-solid fa-xmark"></i></button>
                                    </div>
                                    <div class="toolbar-mini answer-toolbar">
                                        <button type="button" class="format-btn" data-before="**" data-after="**" title="In đậm">[B]</button>
                                        <button type="button" class="format-btn" data-before="*" data-after="*" title="In nghiêng">[I]</button>
                                        <button type="button" class="format-btn" data-before="__" data-after="__" title="Gạch chân">[U]</button>
                                    </div>
                                    <textarea class="form-control auto-textarea" name="answers[]" rows="2" placeholder="Nội dung đáp án..."></textarea>
                                </div>
                                <div class="answer-row" data-answer-index="3">
                                    <div class="answer-header">
                                        <span class="answer-label">D</span>
                                        <input type="radio" class="answer-choice single-choice" name="is_correct[]" value="3">
                                        <button type="button" class="btn btn-outline-danger btn-remove-answer remove-answer"><i class="fa-solid fa-xmark"></i></button>
                                    </div>
                                    <div class="toolbar-mini answer-toolbar">
                                        <button type="button" class="format-btn" data-before="**" data-after="**" title="In đậm">[B]</button>
                                        <button type="button" class="format-btn" data-before="*" data-after="*" title="In nghiêng">[I]</button>
                                        <button type="button" class="format-btn" data-before="__" data-after="__" title="Gạch chân">[U]</button>
                                    </div>
                                    <textarea class="form-control auto-textarea" name="answers[]" rows="2" placeholder="Nội dung đáp án..."></textarea>
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

                            <div class="preview-box">
                                <div class="preview-label">Xem trước</div>
                                <div id="preview-content" class="preview-content"></div>
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
        function autoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 400) + 'px';
        }

        function wrapSelection(textarea, before, after) {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selected = textarea.value.substring(start, end);
            const replacement = before + (selected || '') + after;
            textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
            textarea.focus();
            if (selected) {
                textarea.setSelectionRange(start + before.length, start + before.length + selected.length);
            } else {
                textarea.setSelectionRange(start + before.length, start + before.length);
            }
            autoResize(textarea);
            updatePreview();
        }

        function parseMarkdown(text) {
            return text
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.+?)\*/g, '<em>$1</em>')
                .replace(/__(.+?)__/g, '<u>$1</u>')
                .replace(/~(.+?)~/g, '<sub>$1</sub>')
                .replace(/\^(.+?)\^/g, '<sup>$1</sup>')
                .replace(/\n/g, '<br>');
        }

        function updatePreview() {
            const content = document.getElementById('question_content').value || 'Nhập nội dung câu hỏi';
            const answers = Array.from(document.querySelectorAll('#answers-wrapper textarea[name="answers[]"]'));
            const lines = [`<div class="question-render">${parseMarkdown(content)}</div>`];
            answers.forEach((area, idx) => {
                const text = area.value.trim();
                if (!text) return;
                const isCorrect = idx === 0;
                lines.push(`<div class="answer-render ${isCorrect ? 'correct' : ''}"><span class="answer-label-small">${String.fromCharCode(65 + idx)}</span><span>${parseMarkdown(text)}</span></div>`);
            });
            document.getElementById('preview-content').innerHTML = lines.join('');
        }

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
        }

        typeSelect.addEventListener('change', updateQuestionTypeUI);

        document.getElementById('add-answer').addEventListener('click', function () {
            const rows = answerWrapper.querySelectorAll('.answer-row').length;
            const row = document.createElement('div');
            row.className = 'answer-row';
            row.dataset.answerIndex = rows;
            const letter = String.fromCharCode(65 + rows);
            const isMultiple = typeSelect.value === 'multiple_choice';
            row.innerHTML = `<div class="answer-header">
                <span class="answer-label">${letter}</span>
                <input type="${isMultiple ? 'checkbox' : 'radio'}" class="answer-choice ${isMultiple ? 'multiple-choice' : 'single-choice'}" name="is_correct[]" value="${rows}">
                <button type="button" class="btn btn-outline-danger btn-remove-answer remove-answer"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="toolbar-mini answer-toolbar">
                <button type="button" class="format-btn" data-before="**" data-after="**" title="In đậm">[B]</button>
                <button type="button" class="format-btn" data-before="*" data-after="*" title="In nghiêng">[I]</button>
                <button type="button" class="format-btn" data-before="__" data-after="__" title="Gạch chân">[U]</button>
            </div>
            <textarea class="form-control auto-textarea" name="answers[]" rows="2" placeholder="Nội dung đáp án..."></textarea>`;

            answerWrapper.appendChild(row);
            autoResize(row.querySelector('textarea[name="answers[]"]'));
            setupAnswerToolbar(row);
            updateAnswerLabels();
        });

        function setupAnswerToolbar(row) {
            const textarea = row.querySelector('textarea[name="answers[]"]');
            const toolbar = row.querySelector('.answer-toolbar');
            toolbar.querySelectorAll('.format-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    wrapSelection(textarea, btn.dataset.before, btn.dataset.after);
                });
            });
        }

        function updateAnswerLabels() {
            Array.from(answerWrapper.querySelectorAll('.answer-row')).forEach((row, index) => {
                row.dataset.answerIndex = index;
                const label = row.querySelector('.answer-label');
                if (label) label.textContent = String.fromCharCode(65 + index);
            });
        }

        document.querySelectorAll('#answers-wrapper .answer-row').forEach(row => {
            setupAnswerToolbar(row);
            autoResize(row.querySelector('textarea[name="answers[]"]'));
        });

        const mainQuestion = document.getElementById('question_content');
        if (mainQuestion) {
            autoResize(mainQuestion);
            document.querySelectorAll('#answers-wrapper .format-btn, .toolbar-mini .format-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const textarea = btn.closest('.toolbar-mini').classList.contains('answer-toolbar')
                        ? btn.closest('.answer-row').querySelector('textarea[name="answers[]"]')
                        : mainQuestion;
                    wrapSelection(textarea, btn.dataset.before, btn.dataset.after);
                });
            });
            mainQuestion.addEventListener('input', function () {
                autoResize(mainQuestion);
                updatePreview();
            });
        }

        document.querySelectorAll('#answers-wrapper textarea[name="answers[]"]').forEach(textarea => {
            textarea.addEventListener('input', function () {
                autoResize(textarea);
                updatePreview();
            });
        });

        document.addEventListener('click', function (event) {
            if (event.target.closest('.remove-answer')) {
                const row = event.target.closest('.answer-row');
                const rows = answerWrapper.querySelectorAll('.answer-row');
                if (row && rows.length > 2) {
                    row.remove();
                    updateAnswerLabels();
                }
            }
        });

        updateQuestionTypeUI();
        updateAnswerLabels();
        updatePreview();
    </script>
</body>
</html>