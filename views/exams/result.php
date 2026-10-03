<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kết quả bài thi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #eaf4ff 0%, #f8fbff 50%, #fff 100%); color: #17233c; }
        .result-card { max-width: 680px; border: 0; border-radius: 24px; box-shadow: 0 18px 45px rgba(31,74,125,.12); overflow: hidden; }
        .result-head { background: linear-gradient(135deg, #0d6efd, #438df4); color: #fff; }
        .score { width: 150px; height: 150px; border: 8px solid #ddebff; background: #fff; color: #0d6efd; }
    </style>
</head>
<body>
<div class="container py-5 d-flex justify-content-center">
    <div class="card result-card text-center w-100">
        <div class="result-head p-4 p-md-5">
            <i class="fa-solid fa-circle-check fs-1 mb-3"></i>
            <div class="text-uppercase small opacity-75">Bài thi đã hoàn thành</div>
            <h1 class="h3 fw-bold mt-2 mb-0"><?php echo htmlspecialchars($attempt['title']); ?></h1>
        </div>
        <div class="p-4 p-md-5">
            <div class="score rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                <span class="display-5 fw-bold"><?php echo number_format((float) $attempt['total_score'], 2); ?></span>
            </div>
            <p class="mb-1">Kết quả của <strong><?php echo htmlspecialchars($attempt['full_name']); ?></strong></p>
            <p class="text-muted small mb-4"><i class="fa-regular fa-clock me-1"></i><?php echo htmlspecialchars($attempt['end_time']); ?></p>
            <a href="index.php?action=dashboard" class="btn btn-primary rounded-3 px-4"><i class="fa-solid fa-house me-2"></i>Về trang tổng quan</a>
        </div>
    </div>
</div>
</body>
</html>
