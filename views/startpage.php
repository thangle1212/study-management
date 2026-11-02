<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f7fc">
    <title>EduTest - Học tập và kiểm tra trực tuyến</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/edutest.css?v=2">
</head>
<body class="landing-page">
    <header class="landing-header">
        <nav class="navbar navbar-expand-lg" aria-label="Điều hướng chính">
            <div class="container">
                <a class="landing-brand" href="index.php?action=home" aria-label="EduTest - Trang chủ">
                    <span class="landing-brand-mark" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
                    <span>EduTest</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#landingNavigation" aria-controls="landingNavigation" aria-expanded="false" aria-label="Mở menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="landingNavigation">
                    <ul class="navbar-nav ms-auto me-lg-4">
                        <li class="nav-item"><a class="nav-link" href="#about">Giới thiệu</a></li>
                        <li class="nav-item"><a class="nav-link" href="#features">Tính năng</a></li>
                        <li class="nav-item"><a class="nav-link" href="#target">Đối tượng</a></li>
                    </ul>
                    <div class="landing-header-actions">
                        <a href="index.php?action=login" class="btn-admin-outline">Đăng nhập</a>
                        <a href="index.php?action=register" class="btn-admin-primary">Tạo tài khoản</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="landing-hero" id="about">
            <div class="container">
                <div class="landing-hero-grid">
                    <div class="landing-hero-copy">
                        <p class="landing-eyebrow"><i class="fa-solid fa-book-open" aria-hidden="true"></i> Không gian học tập và kiểm tra</p>
                        <h1>Dạy học và kiểm tra trên cùng một nền tảng.</h1>
                        <p class="landing-hero-description">Tạo đề thi, tổ chức lớp học và theo dõi kết quả học tập trong một không gian rõ ràng, dễ sử dụng.</p>
                        <div class="landing-hero-actions">
                            <a href="index.php?action=register" class="btn-admin-primary landing-primary-action">
                                Bắt đầu sử dụng <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </a>
                            <a href="#features" class="landing-text-link">Khám phá tính năng</a>
                        </div>
                        <div class="landing-hero-note">
                            <span class="landing-note-icon" aria-hidden="true"><i class="fa-solid fa-shield-halved"></i></span>
                            <span>Dành cho giáo viên và học sinh</span>
                        </div>
                    </div>

                    <div class="landing-preview" aria-label="Minh họa không gian tạo đề thi">
                        <div class="landing-preview-topbar">
                            <span class="landing-preview-brand"><span class="landing-preview-mark"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></span> EduTest</span>
                            <span class="landing-preview-status"><i class="fa-solid fa-circle" aria-hidden="true"></i> Không gian học tập</span>
                        </div>
                        <div class="landing-preview-body">
                            <div class="landing-preview-heading">
                                <span class="landing-preview-label">NGÂN HÀNG CÂU HỎI</span>
                                <h2>Tạo đề kiểm tra</h2>
                                <p>Chuẩn bị nội dung cho lớp học của bạn.</p>
                            </div>
                            <div class="landing-preview-question">
                                <span class="landing-question-icon" aria-hidden="true"><i class="fa-regular fa-file-lines"></i></span>
                                <div>
                                    <strong>Câu hỏi trắc nghiệm</strong>
                                    <span>Quản lý câu hỏi theo từng chủ đề</span>
                                </div>
                                <i class="fa-solid fa-chevron-right landing-preview-chevron" aria-hidden="true"></i>
                            </div>
                            <div class="landing-preview-question">
                                <span class="landing-question-icon" aria-hidden="true"><i class="fa-solid fa-users"></i></span>
                                <div>
                                    <strong>Lớp học của bạn</strong>
                                    <span>Giao đề và theo dõi kết quả</span>
                                </div>
                                <i class="fa-solid fa-chevron-right landing-preview-chevron" aria-hidden="true"></i>
                            </div>
                            <div class="landing-preview-footer">
                                <span><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Sẵn sàng cho bài kiểm tra</span>
                                <span class="landing-preview-footer-mark">EduTest</span>
                            </div>
                        </div>
                        <span class="landing-preview-accent" aria-hidden="true"></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-section" id="features">
            <div class="container">
                <div class="landing-section-heading">
                    <div>
                        <p class="landing-eyebrow">Công cụ học tập</p>
                        <h2>Mọi hoạt động học tập, kết nối trong một nơi</h2>
                    </div>
                    <p class="landing-section-intro">Tập trung vào việc dạy và học với các công cụ cần thiết cho lớp học trực tuyến.</p>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <article class="landing-feature-card">
                            <span class="landing-feature-icon" aria-hidden="true"><i class="fa-solid fa-file-pen"></i></span>
                            <h3>Tạo đề thi</h3>
                            <p>Xây dựng ngân hàng câu hỏi và tạo đề kiểm tra theo nhu cầu của lớp học.</p>
                        </article>
                    </div>
                    <div class="col-md-4">
                        <article class="landing-feature-card">
                            <span class="landing-feature-icon" aria-hidden="true"><i class="fa-solid fa-square-check"></i></span>
                            <h3>Thi trực tuyến</h3>
                            <p>Học sinh làm bài trực tuyến và xem lại kết quả sau khi hoàn thành.</p>
                        </article>
                    </div>
                    <div class="col-md-4">
                        <article class="landing-feature-card">
                            <span class="landing-feature-icon" aria-hidden="true"><i class="fa-solid fa-school"></i></span>
                            <h3>Quản lý lớp học</h3>
                            <p>Sắp xếp thành viên lớp, giao đề thi và theo dõi hoạt động học tập.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-audience-section" id="target">
            <div class="container">
                <div class="landing-section-heading">
                    <div>
                        <p class="landing-eyebrow">Cho cộng đồng giáo dục</p>
                        <h2>Mỗi vai trò, một không gian phù hợp</h2>
                    </div>
                    <p class="landing-section-intro">Công cụ được sắp xếp để giáo viên dễ quản lý và học sinh dễ tập trung ôn luyện.</p>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <article class="landing-audience-card">
                            <span class="landing-audience-icon" aria-hidden="true"><i class="fa-solid fa-chalkboard-user"></i></span>
                            <div>
                                <h3>Giáo viên</h3>
                                <ul>
                                    <li>Quản lý lớp học và danh sách học sinh.</li>
                                    <li>Tạo ngân hàng câu hỏi và đề thi.</li>
                                    <li>Giao bài, xem kết quả và theo dõi tiến độ.</li>
                                </ul>
                            </div>
                        </article>
                    </div>
                    <div class="col-md-6">
                        <article class="landing-audience-card">
                            <span class="landing-audience-icon" aria-hidden="true"><i class="fa-solid fa-user-graduate"></i></span>
                            <div>
                                <h3>Học sinh</h3>
                                <ul>
                                    <li>Tham gia lớp học và nhận đề được giao.</li>
                                    <li>Làm bài kiểm tra trực tuyến.</li>
                                    <li>Xem lại lịch sử và kết quả bài làm.</li>
                                </ul>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-cta-wrap">
            <div class="container">
                <div class="landing-cta">
                    <div>
                        <p class="landing-eyebrow">Bắt đầu cùng EduTest</p>
                        <h2>Sẵn sàng cho lớp học tiếp theo?</h2>
                        <p>Tạo tài khoản để bắt đầu tổ chức hoạt động học tập và kiểm tra.</p>
                    </div>
                    <a href="index.php?action=register" class="btn-admin-primary landing-cta-button">
                        Tạo tài khoản miễn phí <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <div class="container">
            <a class="landing-brand" href="index.php?action=home">
                <span class="landing-brand-mark" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
                <span>EduTest</span>
            </a>
            <p>Nền tảng học tập và thi trực tuyến.</p>
            <span class="landing-copyright">&copy; 2026 EduTest</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
