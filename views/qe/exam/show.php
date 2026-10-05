<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chi tiết bài thi - EduTest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../qe.css">
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
                <a href="#"><i class="fa-solid fa-gauge-high"></i><span>Bảng điều khiển</span></a>
                <a href="../question-bank/index.php"><i class="fa-solid fa-layer-group"></i><span>Ngân hàng câu hỏi</span></a>
                <a class="active" href="index.php"><i class="fa-solid fa-file-signature"></i><span>Bài thi</span></a>
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
                    <div class="topbar-title">Chi tiết bài thi</div>
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
                        <h1>Kiểm tra giữa kỳ - Lập trình</h1>
                        <div class="text-muted">Bài thi</div>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-secondary" href="index.php"><i class="fa-solid fa-arrow-left me-2"></i>Quay lại</a>
                        <a class="btn btn-outline-primary" href="edit.php"><i class="fa-solid fa-pencil me-2"></i>Chỉnh sửa</a>
                        <a class="btn btn-primary" href="questions.php"><i class="fa-solid fa-list-check me-2"></i>Quản lý câu hỏi</a>
                    </div>
                </div>

                <div class="content-card">
                    <div class="detail-grid">
                        <div class="detail-card">
                            <div class="detail-card-label">Tên bài thi</div>
                            <div class="detail-card-value">Kiểm tra giữa kỳ - Lập trình</div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-card-label">Thời gian</div>
                            <div class="detail-card-value">60 phút</div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-card-label">Số câu hỏi</div>
                            <div class="detail-card-value">30</div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-card-label">Trạng thái</div>
                            <div class="detail-card-value">Đã công bố</div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-8">
                            <p class="lead text-muted mb-2">Mô tả</p>
                            <p class="mb-0">Đánh giá kiến thức lập trình cơ bản bao gồm biến, vòng lặp, điều kiện và luồng điều khiển.</p>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-card-label">Ngày tạo</div>
                                <div class="detail-card-value">2026-09-01</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-card mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="mb-0">Tổng quan bài thi</h3>
                        </div>
                        <div class="page-actions">
                            <a class="btn btn-primary" href="questions.php"><i class="fa-solid fa-list-check me-2"></i>Quản lý câu hỏi</a>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-card-label">Ngân hàng câu hỏi</div>
                                <div class="detail-card-value">Lập trình cơ bản</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-card-label">Hình thức</div>
                                <div class="detail-card-value">Trực tuyến</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-card-label">Thời gian mở</div>
                                <div class="detail-card-value">2026-09-15</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>