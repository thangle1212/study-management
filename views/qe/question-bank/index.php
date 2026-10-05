<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ngân hàng câu hỏi - EduTest</title>
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
                    <div class="topbar-title">Ngân hàng câu hỏi</div>
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
                        <h1>Ngân hàng câu hỏi</h1>
                        <div class="text-muted">Quản lý các bộ đề và câu hỏi</div>
                    </div>
                    <div class="page-actions">
                        <a class="btn btn-primary" href="create.php"><i class="fa-solid fa-plus me-2"></i>Tạo ngân hàng câu hỏi</a>
                    </div>
                </div>

                <!-- Thẻ thống kê (kiểu Azota) -->
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-icon blue"><i class="fa-solid fa-layer-group"></i></div>
                        <div class="stat-info">
                            <h3>3</h3>
                            <p>Tổng số ngân hàng</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
                        <div class="stat-info">
                            <h3>2</h3>
                            <p>Đang hoạt động</p>
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
                            <h3>74</h3>
                            <p>Tổng số câu hỏi</p>
                        </div>
                    </div>
                </div>

                <!-- Bộ lọc -->
                <div class="filters-bar">
                    <div class="filters-inner">
                        <div class="search-box">
                            <i class="fa-solid fa-search"></i>
                            <input type="text" class="form-control" placeholder="Tìm kiếm ngân hàng câu hỏi...">
                        </div>
                        <select class="form-select" style="width:180px;">
                            <option>Tất cả danh mục</option>
                            <option>Toán học</option>
                            <option>Tiếng Anh</option>
                            <option>Lập trình</option>
                        </select>
                        <select class="form-select" style="width:160px;">
                            <option>Tất cả trạng thái</option>
                            <option>Hoạt động</option>
                            <option>Nháp</option>
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
                                    <th>Tên ngân hàng</th>
                                    <th>Mô tả</th>
                                    <th>Danh mục</th>
                                    <th>Số câu hỏi</th>
                                    <th>Ngày tạo</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="table-title">Toán học</span></td>
                                    <td>Các chủ đề toán học cốt lõi và bài tập</td>
                                    <td>Toán học</td>
                                    <td>24</td>
                                    <td>2026-09-01</td>
                                    <td><span class="status-badge status-active">Hoạt động</span></td>
                                    <td>
                                        <div class="action-group">
                                            <a class="action-link" href="show.php"><i class="fa-solid fa-eye"></i> Xem</a>
                                            <a class="action-link" href="edit.php"><i class="fa-solid fa-pencil"></i> Sửa</a>
                                            <a class="action-link danger" href="#"><i class="fa-solid fa-trash"></i> Xóa</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="table-title">Tiếng Anh cơ bản</span></td>
                                    <td>Bài tập đọc hiểu, ngữ pháp và từ vựng</td>
                                    <td>Tiếng Anh</td>
                                    <td>18</td>
                                    <td>2026-09-03</td>
                                    <td><span class="status-badge status-active">Hoạt động</span></td>
                                    <td>
                                        <div class="action-group">
                                            <a class="action-link" href="show.php"><i class="fa-solid fa-eye"></i> Xem</a>
                                            <a class="action-link" href="edit.php"><i class="fa-solid fa-pencil"></i> Sửa</a>
                                            <a class="action-link danger" href="#"><i class="fa-solid fa-trash"></i> Xóa</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="table-title">Lập trình cơ bản</span></td>
                                    <td>Biến, điều kiện và thuật toán cơ bản</td>
                                    <td>Lập trình</td>
                                    <td>32</td>
                                    <td>2026-09-08</td>
                                    <td><span class="status-badge status-draft">Nháp</span></td>
                                    <td>
                                        <div class="action-group">
                                            <a class="action-link" href="show.php"><i class="fa-solid fa-eye"></i> Xem</a>
                                            <a class="action-link" href="edit.php"><i class="fa-solid fa-pencil"></i> Sửa</a>
                                            <a class="action-link danger" href="#"><i class="fa-solid fa-trash"></i> Xóa</a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="empty-state d-none">
                    <i class="fa-solid fa-box-open"></i>
                    <h3>Chưa có ngân hàng câu hỏi nào</h3>
                    <p class="text-muted">Hãy tạo ngân hàng câu hỏi mới để bắt đầu xây dựng nội dung đánh giá.</p>
                    <a class="btn btn-primary" href="create.php">Tạo ngân hàng câu hỏi</a>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>