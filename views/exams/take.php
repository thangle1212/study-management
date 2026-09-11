<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Làm bài - <?php echo htmlspecialchars($exam['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --brand: #0d6efd; --ink: #17233c; --soft: #f4f8ff; }
        body { background: linear-gradient(135deg, #eaf4ff 0%, #f8fbff 45%, #fff 100%); color: var(--ink); }
        .topbar { background: rgba(255,255,255,.9); border-bottom: 1px solid #e5edf8; backdrop-filter: blur(12px); }
        .brand { color: var(--brand); font-size: 1.35rem; }
        .exam-shell { max-width: 980px; }
        .exam-header { border: 0; border-radius: 22px; background: linear-gradient(135deg, #0d6efd, #438df4); color: #fff; box-shadow: 0 16px 36px rgba(13,110,253,.18); }
        .question-card { border: 1px solid #e5edf8; border-radius: 16px; box-shadow: 0 8px 24px rgba(31,74,125,.06); transition: transform .2s ease, box-shadow .2s ease; }
        .question-card:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(31,74,125,.1); }
        .answer-option { cursor: pointer; background: #fff; border: 1px solid #e2eaf5 !important; transition: .2s ease; }
        .answer-option:hover { border-color: var(--brand) !important; background: #f5f9ff; }
        .answer-option input { accent-color: var(--brand); }
        .submit-bar { position: sticky; bottom: 18px; z-index: 5; }
    </style>
</head>
<body>
<nav class="navbar topbar sticky-top">
    <div class="container exam-shell">
        <a class="navbar-brand brand fw-bold" href="index.php?action=dashboard"><i class="fa-solid fa-graduation-cap me-2"></i>EduTest</a>
        <a href="index.php?action=dashboard" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-left me-1"></i>Trang tổng quan</a>
    </div>
</nav>
<div class="container exam-shell py-4 py-md-5">
    <div class="exam-header p-4 p-md-5 mb-4">
        <div class="small opacity-75 mb-2"><i class="fa-solid fa-pen-to-square me-1"></i> BÀI KIỂM TRA TRỰC TUYẾN</div>
        <h1 class="h2 fw-bold mb-3"><?php echo htmlspecialchars($exam['title']); ?></h1>
        <div class="d-flex flex-wrap gap-3 small">
            <span><i class="fa-solid fa-list-ol me-1"></i><?php echo (int) $exam['question_count']; ?> câu hỏi</span>
            <span><i class="fa-regular fa-clock me-1"></i><?php echo (int) $exam['duration_minutes']; ?> phút</span>
        </div>
    </div>

    <form method="post" action="index.php?action=submit_exam">
        <input type="hidden" name="exam_id" value="<?php echo (int) $exam['exam_id']; ?>">
        <?php $number = 1; foreach ($questions as $question): ?>
            <section class="card question-card mb-3">
                <div class="card-body">
                    <div class="d-flex gap-3 align-items-start">
                        <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">Câu <?php echo $number++; ?></span>
                        <h5 class="fw-bold mb-0 lh-base"><?php echo htmlspecialchars($question['content']); ?></h5>
                    </div>
                    <?php foreach ($question['answers'] as $answer): ?>
                        <label class="answer-option d-block rounded-3 p-3 mt-3">
                            <input type="<?php echo $question['question_type'] === 'multiple_choice' ? 'checkbox' : 'radio'; ?>" name="answers[<?php echo (int) $question['question_id']; ?>]<?php echo $question['question_type'] === 'multiple_choice' ? '[]' : ''; ?>" value="<?php echo (int) $answer['answer_id']; ?>" class="me-2">
                            <?php echo htmlspecialchars($answer['content']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
        <div class="submit-bar text-end">
            <button type="submit" class="btn btn-primary btn-lg rounded-3 px-4 shadow"><i class="fa-solid fa-paper-plane me-2"></i>Nộp bài</button>
        </div>
    </form>
</div>
</body>
</html>
