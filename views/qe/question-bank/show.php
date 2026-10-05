<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chi tiết ngân hàng câu hỏi - EduTest</title>
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
                    <div class="topbar-title">Chi tiết ngân hàng câu hỏi</div>
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
                        <h1>Lập trình cơ bản</h1>
                        <div class="text-muted">Ngân hàng câu hỏi</div>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-secondary" href="index.php"><i class="fa-solid fa-arrow-left me-2"></i>Quay lại</a>
                        <a class="btn btn-outline-primary" href="edit.php"><i class="fa-solid fa-pencil me-2"></i>Chỉnh sửa</a>
                        <a class="btn btn-primary" href="create.php"><i class="fa-solid fa-plus me-2"></i>Thêm câu hỏi</a>
                    </div>
                </div>

                <div class="content-card">
                    <div class="detail-grid">
                        <div class="detail-card">
                            <div class="detail-card-label">Ngân hàng câu hỏi</div>
                            <div class="detail-card-value">Lập trình cơ bản</div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-card-label">Danh mục</div>
                            <div class="detail-card-value">Lập trình</div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-card-label">Ngày tạo</div>
                            <div class="detail-card-value">2026-09-08</div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-card-label">Ngày cập nhật</div>
                            <div class="detail-card-value">2026-09-10</div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="row">
                            <div class="col-md-8">
                                <p class="lead text-muted mb-2">Mô tả</p>
                                <p class="mb-0">Biến, điều kiện và thuật toán cơ bản dành cho người mới bắt đầu.</p>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-card">
                                    <div class="detail-card-label">Tổng số câu hỏi</div>
                                    <div class="detail-card-value">32</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-card mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="mb-0">Danh sách câu hỏi</h3>
                        </div>
                        <div class="page-actions">
                            <a class="btn btn-primary" href="#"><i class="fa-solid fa-plus me-2"></i>Thêm câu hỏi</a>
                        </div>
                    </div>

                    <div class="question-list">
                        <div class="question-item">
                            <div class="question-item-left">
                                <div class="question-no">1</div>
                                <div>
                                    <div class="question-content">Cú pháp đúng để khai báo biến trong PHP là gì?</div>
                                    <div class="question-meta">Loại: Trắc nghiệm • Số đáp án: 4 • Trạng thái: Hoạt động</div>
                                </div>
                            </div>
                            <div class="action-group">
                                <span class="question-type">Trắc nghiệm</span>
                                <a class="action-link" href="#"><i class="fa-solid fa-pencil"></i> Sửa câu hỏi</a>
                                <a class="action-link danger" href="#"><i class="fa-solid fa-trash"></i> Xóa</a>
                            </div>
                        </div>

                        <div class="question-item">
                            <div class="question-item-left">
                                <div class="question-no">2</div>
                                <div>
                                    <div class="question-content">Vòng lặp nào thường được dùng để duyệt một khoảng cố định?</div>
                                    <div class="question-meta">Loại: Trắc nghiệm • Số đáp án: 4 • Trạng thái: Hoạt động</div>
                                </div>
                            </div>
                            <div class="action-group">
                                <span class="question-type">Trắc nghiệm</span>
                                <a class="action-link" href="#"><i class="fa-solid fa-pencil"></i> Sửa câu hỏi</a>
                                <a class="action-link danger" href="#"><i class="fa-solid fa-trash"></i> Xóa</a>
                            </div>
                        </div>

                        <div class="question-item">
                            <div class="question-item-left">
                                <div class="question-no">3</div>
                                <div>
                                    <div class="question-content">Câu lệnh điều kiện đánh giá điều gì?</div>
                                    <div class="question-meta">Loại: Đúng/Sai • Số đáp án: 2 • Trạng thái: Nháp</div>
                                </div>
                            </div>
                            <div class="action-group">
                                <span class="question-type">Đúng/Sai</span>
                                <a class="action-link" href="#"><i class="fa-solid fa-pencil"></i> Sửa câu hỏi</a>
                                <a class="action-link danger" href="#"><i class="fa-solid fa-trash"></i> Xóa</a>
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