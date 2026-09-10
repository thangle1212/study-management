<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bài thi - EduTest</title>
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
                    <div class="topbar-title">Bài thi</div>
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
                        <h1>Bài thi</h1>
                        <div class="text-muted">Quản lý các bài kiểm tra đánh giá</div>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="create.php"><i class="fa-solid fa-plus me-2"></i>Tạo bài thi</a>
                    </div>
                </div>

                <!-- Thẻ thống kê -->
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-icon blue"><i class="fa-solid fa-file-signature"></i></div>
                        <div class="stat-info">
                            <h3>3</h3>
                            <p>Tổng số bài thi</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
                        <div class="stat-info">
                            <h3>2</h3>
                            <p>Đã công bố</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon yellow"><i class="fa-solid fa-pen"></i></div>
                        <div class="stat-info">
                            <h3>1</h3>
                            <p>Bản nháp</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon purple"><i class="fa-solid fa-question"></i></div>
                        <div class="stat-info">
                            <h3>75</h3>
                            <p>Tổng số câu hỏi</p>
                        </div>
                    </div>
                </div>

                <!-- Bộ lọc -->
                <div class="filters-bar">
                    <div class="filters-inner">
                        <div class="search-box">
                            <i class="fa-solid fa-search"></i>
                            <input type="text" class="form-control" placeholder="Tìm kiếm bài thi...">
                        </div>
                        <select class="form-select" style="width:180px;">
                            <option>Tất cả trạng thái</option>
                            <option>Đã công bố</option>
                            <option>Nháp</option>
                            <option>Đã lên lịch</option>
                        </select>
                    </div>
                    <div class="page-actions">
                        <button class="btn btn-secondary"><i class="fa-solid fa-filter me-2"></i>Lọc</button>
                    </div>
                </div>

                <!-- Bảng danh sách -->
                <div class="content-card">
                    <div class="table-responsive">
                        <table class="table-card table">
                            <thead>
                                <tr>
                                    <th>Tên bài thi</th>
                                    <th>Mô tả</th>
                                    <th>Số câu hỏi</th>
                                    <th>Thời gian</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="table-title">Kiểm tra giữa kỳ - Lập trình</span></td>
                                    <td>Đánh giá kiến thức lập trình cơ bản</td>
                                    <td>30</td>
                                    <td>60 phút</td>
                                    <td><span class="status-badge status-active">Đã công bố</span></td>
                                    <td>2026-09-01</td>
                                    <td>
                                        <div class="action-group">
                                            <a class="action-link" href="show.php"><i class="fa-solid fa-eye"></i> Xem</a>
                                            <a class="action-link" href="edit.php"><i class="fa-solid fa-pencil"></i> Sửa</a>
                                            <a class="action-link" href="questions.php"><i class="fa-solid fa-list-check"></i> Câu hỏi</a>
                                            <a class="action-link danger" href="#"><i class="fa-solid fa-trash"></i> Xóa</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="table-title">Thi cuối kỳ - Cơ sở dữ liệu</span></td>
                                    <td>Kiến thức về cơ sở dữ liệu và thiết kế mô hình</td>
                                    <td>25</td>
                                    <td>90 phút</td>
                                    <td><span class="status-badge status-draft">Nháp</span></td>
                                    <td>2026-09-05</td>
                                    <td>
                                        <div class="action-group">
                                            <a class="action-link" href="show.php"><i class="fa-solid fa-eye"></i> Xem</a>
                                            <a class="action-link" href="edit.php"><i class="fa-solid fa-pencil"></i> Sửa</a>
                                            <a class="action-link" href="questions.php"><i class="fa-solid fa-list-check"></i> Câu hỏi</a>
                                            <a class="action-link danger" href="#"><i class="fa-solid fa-trash"></i> Xóa</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="table-title">Kiểm tra - Phát triển Web</span></td>
                                    <td>Thực hành frontend và dự án web</td>
                                    <td>20</td>
                                    <td>45 phút</td>
                                    <td><span class="status-badge status-active">Đã công bố</span></td>
                                    <td>2026-09-09</td>
                                    <td>
                                        <div class="action-group">
                                            <a class="action-link" href="show.php"><i class="fa-solid fa-eye"></i> Xem</a>
                                            <a class="action-link" href="edit.php"><i class="fa-solid fa-pencil"></i> Sửa</a>
                                            <a class="action-link" href="questions.php"><i class="fa-solid fa-list-check"></i> Câu hỏi</a>
                                            <a class="action-link danger" href="#"><i class="fa-solid fa-trash"></i> Xóa</a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="empty-state d-none">
                    <i class="fa-solid fa-clipboard-question"></i>
                    <h3>Chưa có bài thi nào</h3>
                    <p class="text-muted">Hãy tạo bài thi đầu tiên để bắt đầu lập kế hoạch đánh giá.</p>
                    <a class="btn btn-primary" href="create.php">Tạo bài thi</a>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>