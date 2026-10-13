<?php
$questionsForForm = $formData['questions'] ?? [];
$initialAnswerSelections = [];
if (is_array($questionsForForm)) {
    foreach (array_values($questionsForForm) as $question) {
        $selectedAnswers = [];
        $questionAnswers = is_array($question) && is_array($question['answers'] ?? null)
            ? array_values($question['answers'])
            : [];
        foreach ($questionAnswers as $answer) {
            $selectedAnswers[] = is_array($answer) && ($answer['manual_correct'] ?? '') === '1';
        }
        $initialAnswerSelections[] = $selectedAnswers;
    }
}
$initialAnswerSelectionsJson = json_encode(
    $initialAnswerSelections,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
);
$escape = function ($value) {
    return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES, 'UTF-8');
};
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tạo đề thi - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="views/qe/qe.css">
    <style>
        .exam-create-wrap { max-width: 1100px; }
        .exam-create-card { border: 1px solid #e5edf8; border-radius: 16px; background: #fff; padding: 24px; margin-bottom: 20px; }
        .paste-editor { min-height: 320px; max-height: 65vh; overflow-y: auto; white-space: pre-wrap; overflow-wrap: anywhere; border: 1px solid #ced4da; border-radius: 8px; padding: 16px; background: #fff; }
        .paste-editor:focus { outline: 0; border-color: #86b7fe; box-shadow: 0 0 0 .2rem rgba(13,110,253,.15); }
        .paste-editor:empty:before { content: attr(data-placeholder); color: #6c757d; pointer-events: none; }
        .parsed-question { border: 1px solid #dce7f5; border-radius: 12px; padding: 18px; margin-top: 14px; background: #f9fbff; }
        .parsed-answer { border: 1px solid #e1e9f3; border-radius: 9px; padding: 10px 12px; background: #fff; }
        .parsed-answer.correct { border-color: #75b798; background: #f1faf5; }
        .parse-guide { background: #f4f8ff; border-radius: 10px; padding: 14px 16px; }
    </style>
</head>
<body>
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
                <a href="index.php?action=dashboard"><i class="fa-solid fa-gauge-high"></i><span>Bảng điều khiển</span></a>
                <a href="index.php?action=dashboard"><i class="fa-solid fa-layer-group"></i><span>Ngân hàng câu hỏi</span></a>
                <a class="active" href="index.php?action=qe_exam_create"><i class="fa-solid fa-file-signature"></i><span>Bài thi</span></a>
            </nav>
            <div class="sidebar-user">
                <div class="sidebar-user-card">
                    <div class="sidebar-user-avatar"><i class="fa-solid fa-user"></i></div>
                    <div>
                        <div class="sidebar-user-name"><?= $escape($_SESSION['user']['full_name'] ?? '') ?></div>
                        <div class="sidebar-user-role"><?= $escape($_SESSION['user']['role'] ?? '') ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="member2-main">
            <nav class="member2-topbar">
                <div class="d-flex align-items-center">
                    <button type="button" class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('show')" aria-label="Mở menu">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="topbar-title">Tạo đề thi</div>
                </div>
                <div class="topbar-actions">
                    <div class="user-chip"><i class="fa-solid fa-user"></i><span><?= $escape($_SESSION['user']['full_name'] ?? '') ?></span></div>
                </div>
            </nav>

            <section class="member2-page exam-create-wrap">
                <div class="page-header">
                    <div class="page-title-wrap">
                        <h1>Tạo đề thi</h1>
                        <div class="text-muted">Dán toàn bộ nội dung, hệ thống sẽ quét câu hỏi và đáp án một lượt.</div>
                    </div>
                    <div class="page-actions">
                        <a href="index.php?action=dashboard" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Quay lại</a>
                    </div>
                </div>

                <?php if ($created): ?>
                    <div class="alert alert-success" role="status">
                        <i class="fa-solid fa-circle-check me-2"></i>Đã tạo đề thi cùng câu hỏi và đáp án.
                        <a href="index.php?action=dashboard" class="alert-link ms-1">Về bảng điều khiển</a>
                    </div>
                <?php endif; ?>
                <?php foreach ($formErrors as $error): ?>
                    <div class="alert alert-danger" role="alert"><?= $escape($error) ?></div>
                <?php endforeach; ?>

                <!-- BEGIN BULK EXAM CREATION: one rich-text paste is parsed into the existing question/answer model. -->
                <form method="post" action="index.php?action=qe_exam_create" id="exam-create-form">
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                    <textarea class="d-none" name="pasted_exam_html" id="pasted-exam-html"></textarea>
                    <section class="exam-create-card">
                        <h2 class="h5 fw-bold mb-4">Thông tin đề thi</h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="exam-title">Tên đề thi <span class="text-danger">*</span></label>
                                <input id="exam-title" name="title" class="form-control" maxlength="200" required
                                    value="<?= $escape($formData['title'] ?? '') ?>" placeholder="Ví dụ: Kiểm tra giữa kỳ">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="bank-id">Ngân hàng câu hỏi <span class="text-danger">*</span></label>
                                <select id="bank-id" name="bank_id" class="form-select" required>
                                    <option value="">Chọn ngân hàng câu hỏi của bạn</option>
                                    <?php foreach ($questionBanks as $bank): ?>
                                        <?php $bankId = (int) $bank['bank_id']; ?>
                                        <option value="<?= $bankId ?>" <?= (string) ($formData['bank_id'] ?? '') === (string) $bankId ? 'selected' : '' ?>>
                                            <?= $escape($bank['bank_name']) ?><?= $bank['subject'] ? ' — ' . $escape($bank['subject']) : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!$questionBanks): ?>
                                    <div class="form-text text-danger">Bạn cần có ít nhất một ngân hàng câu hỏi trước khi tạo đề.
                                        <a href="index.php?action=qe_bank_create">Tạo ngân hàng câu hỏi</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="duration">Thời gian làm bài (phút)</label>
                                <input id="duration" type="number" name="duration_minutes" class="form-control" min="1" max="1440" required
                                    value="<?= $escape($formData['duration_minutes'] ?? '60') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="exam-status">Trạng thái</label>
                                <?php $selectedStatus = $formData['status'] ?? 'draft'; ?>
                                <select id="exam-status" name="status" class="form-select">
                                    <option value="draft" <?= $selectedStatus === 'draft' ? 'selected' : '' ?>>Nháp</option>
                                    <option value="published" <?= $selectedStatus === 'published' ? 'selected' : '' ?>>Đã công bố</option>
                                    <option value="scheduled" <?= $selectedStatus === 'scheduled' ? 'selected' : '' ?>>Đã lên lịch</option>
                                    <option value="archived" <?= $selectedStatus === 'archived' ? 'selected' : '' ?>>Lưu trữ</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="start-time">Ngày, giờ bắt đầu</label>
                                <input id="start-time" type="datetime-local" name="start_time" class="form-control" value="<?= $escape($formData['start_time'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="end-time">Ngày, giờ kết thúc</label>
                                <input id="end-time" type="datetime-local" name="end_time" class="form-control" value="<?= $escape($formData['end_time'] ?? '') ?>">
                            </div>
                        </div>
                    </section>

                    <section class="exam-create-card">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <h2 class="h5 fw-bold mb-1">Dán nội dung đề</h2>
                                <p class="small text-muted mb-0">Câu hỏi đánh số 1., 2.; đáp án mỗi dòng A), B), C), D). Giữ nguyên định dạng khi dán.</p>
                            </div>
                            <button type="button" class="btn btn-outline-secondary" id="clear-paste"><i class="fa-regular fa-trash-can me-2"></i>Xóa nội dung</button>
                        </div>

                        <div class="parse-guide small mb-3">
                            <strong>Ví dụ:</strong><br>
                            1. Thủ đô của Việt Nam là gì?<br>
                            A) Hải Phòng<br>
                            B) <strong>Hà Nội</strong><br>
                            C) Đà Nẵng<br><br>
                            Đáp án in đậm, nghiêng, gạch chân hoặc tô màu sẽ tự nhận là đúng. Nếu chưa đánh dấu, hãy chọn đáp án đúng trong phần xem trước bên dưới.
                        </div>
                        <div id="paste-editor" class="paste-editor" contenteditable="true" role="textbox" aria-multiline="true"
                            data-placeholder="Dán toàn bộ đề thi vào đây (Ctrl+V)..."><?= $safePastedExamHtml ?></div>
                        <div class="form-text mt-2">Hỗ trợ tối đa 50 câu hỏi, mỗi câu từ 2 đến 8 đáp án.</div>
                    </section>

                    <section class="exam-create-card">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h2 class="h5 fw-bold mb-1">Xem trước câu hỏi đã quét</h2>
                                <p class="small text-muted mb-0">Kiểm tra đáp án đúng và loại câu hỏi trước khi tạo đề.</p>
                            </div>
                            <span class="badge text-bg-primary" id="parsed-count">0 câu</span>
                        </div>
                        <div id="parse-message" class="small text-muted mt-3" aria-live="polite">Dán đề vào ô phía trên để bắt đầu quét.</div>
                        <div id="parsed-preview"></div>
                        <div id="question-inputs"></div>
                    </section>

                    <div class="d-flex justify-content-end gap-2 mb-5">
                        <a href="index.php?action=dashboard" class="btn btn-secondary"><i class="fa-solid fa-xmark me-2"></i>Hủy</a>
                        <button type="submit" class="btn btn-primary" id="create-exam-button" <?= !$questionBanks ? 'disabled' : '' ?>><i class="fa-solid fa-plus me-2"></i>Tạo đề thi</button>
                    </div>
                </form>
                <!-- END BULK EXAM CREATION -->
            </section>
        </main>
    </div>
    <script>
        (function () {
            const form = document.getElementById('exam-create-form');
            const editor = document.getElementById('paste-editor');
            const preview = document.getElementById('parsed-preview');
            const message = document.getElementById('parse-message');
            const count = document.getElementById('parsed-count');
            const inputs = document.getElementById('question-inputs');
            const rawHtml = document.getElementById('pasted-exam-html');
            let questions = [];
            let parseError = '';
            const initialAnswerSelections = <?= $initialAnswerSelectionsJson ?: '[]' ?>;

            function cleanNode(node) {
                const allowed = ['B', 'STRONG', 'I', 'EM', 'U', 'SPAN', 'FONT', 'BR', 'P', 'DIV', 'UL', 'OL', 'LI'];
                const discarded = ['SCRIPT', 'STYLE', 'IFRAME', 'OBJECT', 'SVG', 'MATH', 'TEMPLATE'];
                Array.from(node.childNodes).forEach(function (child) {
                    if (child.nodeType !== Node.ELEMENT_NODE) return;
                    if (discarded.includes(child.tagName)) {
                        child.remove();
                        return;
                    }
                    cleanNode(child);
                    if (!allowed.includes(child.tagName)) {
                        child.replaceWith(...Array.from(child.childNodes));
                        return;
                    }
                    const tag = child.tagName;
                    let safeColor = '';
                    let safeBackground = '';
                    const formatting = [];
                    if (tag === 'SPAN' && child.hasAttribute('style')) {
                        child.getAttribute('style').split(';').forEach(function (declaration) {
                            const match = declaration.match(/^\s*(color|background-color)\s*:\s*(#[0-9a-f]{3}(?:[0-9a-f]{3})?|rgb\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*\))\s*$/i);
                            if (match) {
                                const value = match[2];
                                const rgb = value.match(/^rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)$/i);
                                let normalized = value.toLowerCase();
                                if (rgb) {
                                    const channels = rgb.slice(1).map(Number);
                                    if (channels.some(function (channel) { return channel > 255; })) return;
                                    normalized = '#' + channels.map(function (channel) {
                                        return channel.toString(16).padStart(2, '0');
                                    }).join('');
                                }
                                if (match[1].toLowerCase() === 'color') safeColor = normalized;
                                else safeBackground = normalized;
                            } else if (/^\s*font-weight\s*:\s*(bold|[7-9]00)\s*$/i.test(declaration)) {
                                formatting.push('strong');
                            } else if (/^\s*font-style\s*:\s*italic\s*$/i.test(declaration)) {
                                formatting.push('em');
                            } else if (/^\s*text-decoration(?:-line)?\s*:\s*[^;]*underline/i.test(declaration)) {
                                formatting.push('u');
                            }
                        });
                    } else if (tag === 'FONT' && child.hasAttribute('color')) {
                        safeColor = child.getAttribute('color');
                    }
                    Array.from(child.attributes).forEach(function (attribute) {
                        child.removeAttribute(attribute.name);
                    });
                    if (tag === 'FONT' && safeColor) child.setAttribute('color', safeColor);
                    if (tag === 'SPAN') {
                        const styles = [];
                        if (safeColor) styles.push('color: ' + safeColor);
                        if (safeBackground) styles.push('background-color: ' + safeBackground);
                        if (styles.length) child.setAttribute('style', styles.join('; '));
                    }
                    Array.from(new Set(formatting)).forEach(function (wrapperTag) {
                        const wrapper = document.createElement(wrapperTag);
                        while (child.firstChild) wrapper.appendChild(child.firstChild);
                        child.appendChild(wrapper);
                    });
                });
            }

            function segmentsFor(node) {
                if (node.nodeType === Node.TEXT_NODE) {
                    return node.data.split(/\r\n|\r|\n/).map(function (text) {
                        return text ? [document.createTextNode(text)] : [];
                    });
                }
                if (node.nodeType !== Node.ELEMENT_NODE) return [[]];
                if (node.tagName === 'BR') return [[], []];

                let segments = [[]];
                Array.from(node.childNodes).forEach(function (child) {
                    const childSegments = segmentsFor(child);
                    const isBlock = child.nodeType === Node.ELEMENT_NODE
                        && ['P', 'DIV', 'LI', 'UL', 'OL'].includes(child.tagName);
                    if (isBlock && segments[segments.length - 1].length) segments.push([]);
                    segments[segments.length - 1].push(...childSegments[0]);
                    for (let index = 1; index < childSegments.length; index++) {
                        segments.push(childSegments[index]);
                    }
                    if (isBlock && segments[segments.length - 1].length) segments.push([]);
                });

                if (!['P', 'DIV', 'LI', 'UL', 'OL'].includes(node.tagName)) {
                    segments = segments.map(function (segment) {
                        if (!segment.length) return [];
                        const wrapper = node.cloneNode(false);
                        wrapper.append(...segment);
                        return [wrapper];
                    });
                }
                return segments;
            }

            function extractLines(root) {
                const lines = [[]];
                Array.from(root.childNodes).forEach(function (node) {
                    if (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'OL') {
                        const start = Number(node.getAttribute('start')) || 1;
                        let itemNumber = start;
                        Array.from(node.children).filter(function (child) {
                            return child.tagName === 'LI';
                        }).forEach(function (item) {
                            const itemContent = item.cloneNode(true);
                            itemContent.querySelectorAll('ol, ul').forEach(function (nestedList) {
                                nestedList.remove();
                            });
                            const itemText = itemContent.textContent.trim();
                            if (!itemText) return;
                            const answerLabel = itemText.match(/^[A-H]\)\s*/i);
                            const questionNumber = itemText.match(/^\d+[.)]\s*/);
                            const prefix = answerLabel || questionNumber ? '' : itemNumber + '. ';
                            const line = document.createElement('div');
                            line.innerHTML = prefix + itemContent.innerHTML;
                            if (lines[lines.length - 1].length) lines.push([]);
                            lines[lines.length - 1].push(line);
                            lines.push([]);
                            itemNumber += 1;
                        });
                        return;
                    }
                    const segments = segmentsFor(node);
                    const isBlock = node.nodeType === Node.ELEMENT_NODE
                        && ['P', 'DIV', 'LI', 'UL', 'OL'].includes(node.tagName);
                    if (isBlock && lines[lines.length - 1].length) lines.push([]);
                    lines[lines.length - 1].push(...segments[0]);
                    for (let index = 1; index < segments.length; index++) lines.push(segments[index]);
                    if (isBlock && lines[lines.length - 1].length) lines.push([]);
                });
                return lines.map(function (nodes) {
                    const line = document.createElement('div');
                    line.append(...nodes);
                    return { html: line.innerHTML, text: line.textContent.trim() };
                }).filter(function (line) { return line.text !== ''; });
            }

            function expandOrderedLists(root) {
                Array.from(root.querySelectorAll('ol')).reverse().forEach(function (list) {
                    const replacement = document.createDocumentFragment();
                    let itemNumber = Number(list.getAttribute('start')) || 1;
                    Array.from(list.children).filter(function (child) {
                        return child.tagName === 'LI';
                    }).forEach(function (item) {
                        const itemContent = item.cloneNode(true);
                        itemContent.querySelectorAll('ol, ul').forEach(function (nestedList) {
                            nestedList.remove();
                        });
                        const itemText = itemContent.textContent.trim();
                        const itemValue = Number(item.getAttribute('value'));
                        if (Number.isInteger(itemValue) && itemValue > 0) itemNumber = itemValue;
                        const hasQuestionOrAnswerLabel = /^(?:(?:Câu|Question)\s*)?\d+[.)]\s*|^[A-H]\)\s*/i.test(itemText);
                        const line = document.createElement('div');
                        if (!hasQuestionOrAnswerLabel) {
                            line.appendChild(document.createTextNode(itemNumber + '. '));
                        }
                        while (itemContent.firstChild) line.appendChild(itemContent.firstChild);
                        replacement.appendChild(line);
                        itemNumber += 1;
                    });
                    list.replaceWith(replacement);
                });
            }

            function removeTextPrefix(html, length) {
                const container = document.createElement('div');
                container.innerHTML = html;
                const walker = document.createTreeWalker(container, NodeFilter.SHOW_TEXT);
                const textNodes = [];
                while (walker.nextNode()) textNodes.push(walker.currentNode);
                let remaining = length;
                textNodes.forEach(function (textNode) {
                    if (remaining <= 0) return;
                    const removeCount = Math.min(remaining, textNode.data.length);
                    textNode.data = textNode.data.slice(removeCount);
                    remaining -= removeCount;
                });
                return container.innerHTML.trim();
            }

            function appendLine(current, html, text) {
                if (current.kind === 'answer') {
                    current.answers[current.answers.length - 1].html += '<br>' + html;
                    current.answers[current.answers.length - 1].text += ' ' + text;
                } else if (current.kind === 'question') {
                    current.content += ' ' + text;
                }
            }

            function parseExam() {
                const previousSelections = questions.map(function (question) {
                    return question.answers.map(function (answer) {
                        return { text: answer.text, manual: answer.manual };
                    });
                });
                questions = [];
                parseError = '';
                const safeRoot = document.createElement('div');
                safeRoot.innerHTML = editor.innerHTML;
                cleanNode(safeRoot);
                expandOrderedLists(safeRoot);
                const lines = extractLines(safeRoot);
                let current = { kind: '' };

                lines.forEach(function (line) {
                    const questionMatch = line.text.match(/^(?:(?:Câu|Question)\s*)?(\d+)[.)]\s*/i);
                    if (questionMatch) {
                        const questionText = line.text.slice(questionMatch[0].length).trim();
                        if (!questionText) {
                            parseError = 'Câu ' + questionMatch[1] + ' đang để trống nội dung.';
                            current = { kind: '' };
                            return;
                        }
                        questions.push({
                            content: questionText,
                            question_type: 'single_choice',
                            score_weight: '1',
                            answers: []
                        });
                        current = { kind: 'question' };
                        return;
                    }

                    const answerMatch = line.text.match(/^\s*([A-H])\)\s*/i);
                    if (answerMatch && questions.length) {
                        const answerText = line.text.slice(answerMatch[0].length).trim();
                        const answerHtml = removeTextPrefix(line.html, answerMatch[0].length);
                        if (!answerText) {
                            parseError = 'Đáp án ' + answerMatch[1].toUpperCase() + ' đang để trống.';
                            return;
                        }
                        const marker = /<(?:b|strong|i|em|u)\b|<span\b[^>]*style="[^"]*(?:color|background-color)\s*:|<font\b[^>]*color=/i;
                        questions[questions.length - 1].answers.push({
                            html: answerHtml,
                            text: answerText,
                            correct: marker.test(answerHtml),
                            manual: Boolean(
                                (initialAnswerSelections[questions.length - 1]
                                    && initialAnswerSelections[questions.length - 1][questions[questions.length - 1].answers.length])
                                || (previousSelections[questions.length - 1]
                                    && previousSelections[questions.length - 1][questions[questions.length - 1].answers.length]
                                    && previousSelections[questions.length - 1][questions[questions.length - 1].answers.length].text === answerText
                                    && previousSelections[questions.length - 1][questions[questions.length - 1].answers.length].manual)
                            )
                        });
                        current = { kind: 'answer', answers: questions[questions.length - 1].answers };
                        return;
                    }

                    if (questions.length) appendLine(current, line.html, line.text);
                });

                if (!questions.length && editor.textContent.trim()) {
                    parseError = 'Không tìm thấy câu hỏi. Hãy bắt đầu mỗi câu bằng số thứ tự, ví dụ “1.”';
                }
                if (questions.length > 50) parseError = 'Tối đa 50 câu hỏi trong một đề thi.';
                questions.forEach(function (question, index) {
                    if (question.answers.length < 2 || question.answers.length > 8) {
                        parseError = 'Câu ' + (index + 1) + ' cần có từ 2 đến 8 đáp án dạng A), B), C)...';
                    }
                    const correctCount = question.answers.filter(function (answer) {
                        return answer.correct || answer.manual;
                    }).length;
                    if (correctCount > 1) question.question_type = 'multiple_choice';
                    if (correctCount === 0) {
                        parseError = 'Câu ' + (index + 1) + ' chưa có đáp án đúng. Hãy đánh dấu đáp án đúng trong phần xem trước.';
                    } else if (question.question_type === 'single_choice' && correctCount !== 1) {
                        parseError = 'Câu ' + (index + 1) + ' đang ở dạng một đáp án nhưng có nhiều đáp án đúng.';
                    }
                });
            }

            function addHidden(name, value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                inputs.appendChild(input);
            }

            function syncQuestionInputs() {
                inputs.replaceChildren();
                questions.forEach(function (question, questionIndex) {
                    const prefix = 'questions[' + questionIndex + ']';
                    addHidden(prefix + '[content]', question.content);
                    const type = document.createElement('input');
                    type.type = 'hidden';
                    type.name = prefix + '[question_type]';
                    type.value = question.question_type;
                    type.dataset.questionType = questionIndex;
                    inputs.appendChild(type);
                    addHidden(prefix + '[score_weight]', question.score_weight);
                    question.answers.forEach(function (answer, answerIndex) {
                        const answerPrefix = prefix + '[answers][' + answerIndex + ']';
                        addHidden(answerPrefix + '[content]', answer.html);
                        if (answer.correct || answer.manual) addHidden(answerPrefix + '[manual_correct]', '1');
                    });
                });
            }

            function renderPreview() {
                parseExam();
                preview.replaceChildren();
                count.textContent = questions.length + ' câu';
                if (!editor.textContent.trim()) {
                    message.textContent = 'Dán đề vào ô phía trên để bắt đầu quét.';
                    syncQuestionInputs();
                    return;
                }

                questions.forEach(function (question, questionIndex) {
                    const card = document.createElement('article');
                    card.className = 'parsed-question';
                    const header = document.createElement('div');
                    header.className = 'd-flex flex-wrap justify-content-between align-items-center gap-2 mb-3';
                    const title = document.createElement('h3');
                    title.className = 'h6 fw-bold mb-0';
                    title.textContent = 'Câu ' + (questionIndex + 1) + '. ' + question.content;
                    const options = document.createElement('div');
                    options.className = 'd-flex gap-2 align-items-center';
                    const typeLabel = document.createElement('label');
                    typeLabel.className = 'small text-muted';
                    typeLabel.textContent = 'Loại:';
                    const typeSelect = document.createElement('select');
                    typeSelect.className = 'form-select form-select-sm';
                    typeSelect.innerHTML = '<option value="single_choice">Một đáp án</option><option value="multiple_choice">Nhiều đáp án</option>';
                    typeSelect.value = question.question_type;
                    typeSelect.addEventListener('change', function () {
                        question.question_type = typeSelect.value;
                        renderPreview();
                    });
                    typeLabel.appendChild(typeSelect);
                    options.appendChild(typeLabel);
                    const score = document.createElement('input');
                    score.type = 'number';
                    score.className = 'form-control form-control-sm';
                    score.min = '0.01';
                    score.max = '999.99';
                    score.step = '0.01';
                    score.value = question.score_weight;
                    score.setAttribute('aria-label', 'Điểm câu hỏi ' + (questionIndex + 1));
                    score.style.width = '88px';
                    score.addEventListener('input', function () {
                        question.score_weight = score.value;
                        syncQuestionInputs();
                    });
                    options.appendChild(score);
                    header.append(title, options);
                    card.appendChild(header);

                    const answerList = document.createElement('div');
                    answerList.className = 'row g-2';
                    question.answers.forEach(function (answer, answerIndex) {
                        const column = document.createElement('div');
                        column.className = 'col-md-6';
                        const answerCard = document.createElement('div');
                        answerCard.className = 'parsed-answer' + (answer.correct || answer.manual ? ' correct' : '');
                        const label = document.createElement('label');
                        label.className = 'form-check mb-0';
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.className = 'form-check-input';
                        checkbox.checked = answer.correct || answer.manual;
                        checkbox.disabled = answer.correct;
                        checkbox.addEventListener('change', function () {
                            answer.manual = checkbox.checked;
                            answerCard.classList.toggle('correct', answer.correct || answer.manual);
                            if (question.answers.filter(function (item) { return item.correct || item.manual; }).length > 1) {
                                question.question_type = 'multiple_choice';
                                typeSelect.value = 'multiple_choice';
                            }
                            syncQuestionInputs();
                        });
                        const answerText = document.createElement('span');
                        answerText.className = 'form-check-label ms-1';
                        answerText.innerHTML = answer.html;
                        label.append(checkbox, answerText);
                        answerCard.appendChild(label);
                        const small = document.createElement('div');
                        small.className = 'small text-success mt-1';
                        small.textContent = answer.correct ? 'Được nhận diện là đáp án đúng theo định dạng.' : '';
                        answerCard.appendChild(small);
                        column.appendChild(answerCard);
                        answerList.appendChild(column);
                    });
                    card.appendChild(answerList);
                    preview.appendChild(card);
                });

                if (parseError) {
                    message.className = 'small text-danger mt-3';
                    message.textContent = parseError;
                } else {
                    message.className = 'small text-success mt-3';
                    message.textContent = 'Đã quét ' + questions.length + ' câu. Hãy kiểm tra đáp án đúng; các ô có dấu ✓ được dùng làm đáp án đúng.';
                }
                syncQuestionInputs();
            }

            editor.addEventListener('input', renderPreview);
            editor.addEventListener('paste', function (event) {
                const clipboard = event.clipboardData;
                if (!clipboard) return;
                event.preventDefault();

                const pastedHtml = clipboard.getData('text/html');
                if (!pastedHtml) {
                    document.execCommand('insertText', false, clipboard.getData('text/plain'));
                    renderPreview();
                    return;
                }

                const parsedPaste = new DOMParser().parseFromString(pastedHtml, 'text/html');
                cleanNode(parsedPaste.body);
                const cleanPaste = document.createElement('div');
                cleanPaste.innerHTML = parsedPaste.body.innerHTML;
                const selection = window.getSelection();
                if (selection && selection.rangeCount && editor.contains(selection.anchorNode)) {
                    const range = selection.getRangeAt(0);
                    range.deleteContents();
                    const fragment = range.createContextualFragment(cleanPaste.innerHTML);
                    const lastNode = fragment.lastChild;
                    range.insertNode(fragment);
                    if (lastNode) {
                        range.setStartAfter(lastNode);
                        range.collapse(true);
                        selection.removeAllRanges();
                        selection.addRange(range);
                    }
                } else {
                    editor.append(cleanPaste);
                }
                renderPreview();
            });
            document.getElementById('clear-paste').addEventListener('click', function () {
                editor.replaceChildren();
                renderPreview();
                editor.focus();
            });
            form.addEventListener('submit', function (event) {
                renderPreview();
                if (parseError || questions.length === 0) {
                    event.preventDefault();
                    message.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                rawHtml.value = editor.innerHTML;
                syncQuestionInputs();
            });
            renderPreview();
        })();
    </script>
</body>
</html>
