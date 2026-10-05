<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản lý câu hỏi bài thi - EduTest</title>
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
                    <div class="topbar-title">Quản lý câu hỏi bài thi</div>
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
                        <div class="text-muted">3 câu hỏi • 60 phút</div>
                    </div>
                    <div class="page-actions">
                        <a href="show.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Quay lại</a>
                        <a href="#" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Thêm câu hỏi</a>
                    </div>
                </div>

                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div>
                            <span class="status-badge status-active px-3 py-2">
                                <i class="fa-solid fa-list me-2"></i>Tổng số câu hỏi: 3
                            </span>
                        </div>
                        <div class="page-actions">
                            <button class="btn btn-outline-primary"><i class="fa-solid fa-file-import me-2"></i>Thêm từ Ngân hàng câu hỏi</button>
                        </div>
                    </div>

                    <div class="question-list">
                        <div class="question-item">
                            <div class="question-item-left">
                                <div class="question-no">1</div>
                                <div>
                                    <div class="question-content">Cú pháp đúng để khai báo biến trong PHP là gì?</div>
                                    <div class="question-meta">Loại: Trắc nghiệm • Điểm: 5 • Thứ tự: 1</div>
                                </div>
                            </div>
                            <div class="action-group">
                                <span class="question-type">Trắc nghiệm</span>
                                <button class="btn btn-sm btn-light"><i class="fa-solid fa-arrows-up-down"></i></button>
                                <a class="action-link danger" href="#"><i class="fa-solid fa-trash"></i> Xóa</a>
                            </div>
                        </div>

                        <div class="question-item">
                            <div class="question-item-left">
                                <div class="question-no">2</div>
                                <div>
                                    <div class="question-content">Vòng lặp nào thường được dùng để duyệt một khoảng cố định?</div>
                                    <div class="question-meta">Loại: Trắc nghiệm • Điểm: 5 • Thứ tự: 2</div>
                                </div>
                            </div>
                            <div class="action-group">
                                <span class="question-type">Trắc nghiệm</span>
                                <button class="btn btn-sm btn-light"><i class="fa-solid fa-arrows-up-down"></i></button>
                                <a class="action-link danger" href="#"><i class="fa-solid fa-trash"></i> Xóa</a>
                            </div>
                        </div>

                        <div class="question-item">
                            <div class="question-item-left">
                                <div class="question-no">3</div>
                                <div>
                                    <div class="question-content">Câu lệnh điều kiện đánh giá điều gì?</div>
                                    <div class="question-meta">Loại: Đúng/Sai • Điểm: 5 • Thứ tự: 3</div>
                                </div>
                            </div>
                            <div class="action-group">
                                <span class="question-type">Đúng/Sai</span>
                                <button class="btn btn-sm btn-light"><i class="fa-solid fa-arrows-up-down"></i></button>
                                <a class="action-link danger" href="#"><i class="fa-solid fa-trash"></i> Xóa</a>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-xmark me-2"></i>Hủy</a>
                        <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Lưu thay đổi</button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>