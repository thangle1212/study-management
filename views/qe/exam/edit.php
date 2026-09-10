<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chỉnh sửa bài thi - EduTest</title>
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
                    <div class="topbar-title">Chỉnh sửa bài thi</div>
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
                        <h1>Chỉnh sửa bài thi</h1>
                        <div class="text-muted">Cập nhật cấu hình bài đánh giá</div>
                    </div>
                    <div class="page-actions">
                        <a href="show.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Quay lại</a>
                    </div>
                </div>

                <div class="form-wrap">
                    <form>
                        <div class="form-section">
                            <div class="form-title mb-4">Thông tin bài thi</div>
                            <div class="grid-form">
                                <div class="mb-3">
                                    <label class="form-label">Tên bài thi <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" value="Kiểm tra giữa kỳ - Lập trình" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ngân hàng câu hỏi</label>
                                    <select class="form-select">
                                        <option selected>Lập trình cơ bản</option>
                                        <option>Toán học</option>
                                        <option>Tiếng Anh cơ bản</option>
                                        <option>Cơ sở dữ liệu</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Thời gian làm bài</label>
                                    <input type="text" class="form-control" value="60 phút">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Số câu hỏi</label>
                                    <input type="number" class="form-control" value="30">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select class="form-select">
                                        <option selected>Đã công bố</option>
                                        <option>Nháp</option>
                                        <option>Đã lên lịch</option>
                                        <option>Lưu trữ</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ngày bắt đầu</label>
                                    <input type="date" class="form-control" value="2026-09-15">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ngày kết thúc</label>
                                    <input type="date" class="form-control" value="2026-09-16">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea class="form-control">Đánh giá kiến thức lập trình cơ bản</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-xmark me-2"></i>Hủy</a>
                            <button type="button" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>