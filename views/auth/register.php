<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - EduTest</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }
        .auth-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            background-color: #ffffff;
            width: 100%;
            max-width: 480px;
        }
        .role-option { display: none; }
        .role-label {
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
        }
        .role-option:checked + .role-label {
            border-color: #0d6efd;
            background-color: #e7f1ff;
            color: #0d6efd;
            font-weight: 600;
        }
        .input-group-text { background-color: #f8f9fa; border-right: none; }
        .form-control-with-icon { border-left: none; }
        .form-control-with-icon:focus { box-shadow: none; border-color: #dee2e6; }
        .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
            border-radius: 0.375rem;
        }
        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control { border-color: #86b7fe; }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="auth-card p-4">
        
        <div class="text-center mb-4">
            <a href="index.php" class="text-decoration-none text-primary fw-bold fs-3">
                <i class="fa-solid fa-graduation-cap me-2"></i>EduTest
            </a>
            <h5 class="fw-bold mt-3 mb-1">Tạo tài khoản mới</h5>
            <p class="text-muted small">Điền thông tin bên dưới để bắt đầu sử dụng hệ thống</p>
        </div>

        <!-- Thông báo lỗi từ Controller -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Thông báo thành công từ Controller -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success py-2 small" role="alert">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="index.php?action=register" method="POST">
            <!-- Chọn Vai trò -->
            <div class="mb-3">
                <label class="form-label fw-semibold d-block">Bạn là:</label>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="radio" class="role-option" name="role" id="roleStudent" value="student" <?php echo (!isset($_POST['role']) || $_POST['role'] === 'student') ? 'checked' : ''; ?>>
                        <label class="role-label" for="roleStudent">
                            <i class="fa-solid fa-user-graduate d-block fs-4 mb-1"></i>
                            Học sinh / SV
                        </label>
                    </div>
                    <div class="col-6">
                        <input type="radio" class="role-option" name="role" id="roleTeacher" value="teacher" <?php echo (isset($_POST['role']) && $_POST['role'] === 'teacher') ? 'checked' : ''; ?>>
                        <label class="role-label" for="roleTeacher">
                            <i class="fa-solid fa-chalkboard-user d-block fs-4 mb-1"></i>
                            Giáo viên
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Họ và tên</label>
                <div class="input-group">
                    <span class="input-group-text text-muted"><i class="fa-regular fa-user"></i></span>
                    <input type="text" name="full_name" class="form-control form-control-with-icon" placeholder="Nguyễn Văn A" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Địa chỉ Email</label>
                <div class="input-group">
                    <span class="input-group-text text-muted"><i class="fa-regular fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control form-control-with-icon" placeholder="name@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Trường học (Không bắt buộc)</label>
                <div class="input-group">
                    <span class="input-group-text text-muted"><i class="fa-solid fa-school"></i></span>
                    <input type="text" name="school_name" class="form-control form-control-with-icon" placeholder="THPT Chuyên / Đại học..." value="<?php echo htmlspecialchars($_POST['school_name'] ?? ''); ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text text-muted"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" id="regPassword" name="password" class="form-control form-control-with-icon" placeholder="Ít nhất 6 ký tự" minlength="6" required>
                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword('regPassword', this)">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="terms" required>
                <label class="form-check-label small text-muted" for="terms">
                    Tôi đồng ý với <a href="#" class="text-primary text-decoration-none">Điều khoản dịch vụ</a> và <a href="#" class="text-primary text-decoration-none">Chính sách bảo mật</a>.
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3 mb-3">
                Tạo tài khoản <i class="fa-solid fa-user-plus ms-1"></i>
            </button>
        </form>

        <div class="text-center pt-2 border-top">
            <p class="small text-muted mb-0">Đã có tài khoản? <a href="index.php?action=login" class="text-primary fw-semibold text-decoration-none">Đăng nhập</a></p>
        </div>

    </div>
</div>

<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>

</body>
</html>