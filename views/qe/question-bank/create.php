<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tạo ngân hàng câu hỏi - EduTest</title>
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
                    <div class="topbar-title">Tạo ngân hàng câu hỏi</div>
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
                        <h1>Tạo ngân hàng câu hỏi</h1>
                        <div class="text-muted">Tạo bộ sưu tập câu hỏi đánh giá mới</div>
                    </div>
                    <div class="page-actions">
                        <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Quay lại</a>
                    </div>
                </div>

                <div class="form-wrap">
                    <form>
                        <div class="form-section">
                            <div class="form-title mb-4">Thông tin ngân hàng câu hỏi</div>
                            <div class="grid-form">
                                <div class="mb-3">
                                    <label class="form-label">Tên ngân hàng câu hỏi <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" placeholder="Ví dụ: Toán học" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Danh mục / Môn học</label>
                                    <select class="form-select">
                                        <option>Toán học</option>
                                        <option>Tiếng Anh</option>
                                        <option>Lập trình cơ bản</option>
                                        <option>Cơ sở dữ liệu</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select class="form-select">
                                        <option>Hoạt động</option>
                                        <option>Nháp</option>
                                        <option>Lưu trữ</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ngày tạo</label>
                                    <input type="date" class="form-control" value="2026-09-10">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea class="form-control" placeholder="Mô tả ngắn về ngân hàng câu hỏi"></textarea>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-title mb-4">Cài đặt câu hỏi</div>
                            <div class="grid-form">
                                <div class="mb-3">
                                    <label class="form-label">Độ khó</label>
                                    <select class="form-select">
                                        <option>Cơ bản</option>
                                        <option>Trung bình</option>
                                        <option>Nâng cao</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Giới hạn số câu hỏi</label>
                                    <input type="number" class="form-control" value="30">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-xmark me-2"></i>Hủy</a>
                            <button type="button" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Tạo mới</button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>