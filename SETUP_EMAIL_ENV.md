# Hướng dẫn cấu hình chức năng Quên mật khẩu bằng ENV

File này hướng dẫn cách bật và sử dụng chức năng quên mật khẩu trong dự án, dựa trên cấu hình SMTP được lấy từ biến môi trường `.env`.

## 1. Tạo file `.env` ở thư mục gốc dự án

Tạo file `.env` trong thư mục gốc của project:

```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
SMTP_FROM_EMAIL=your_email@gmail.com
SMTP_FROM_NAME=EduTest
```

> Lưu ý: tên biến phải đúng với code đang dùng trong dự án.

## 2. Giải thích từng biến

- `SMTP_HOST`: địa chỉ SMTP server. Ví dụ: `smtp.gmail.com`
- `SMTP_PORT`: cổng SMTP, thường là `587` cho STARTTLS
- `SMTP_USERNAME`: email dùng để gửi mail
- `SMTP_PASSWORD`: mật khẩu hoặc app password của email gửi
- `SMTP_FROM_EMAIL`: email hiển thị làm người gửi
- `SMTP_FROM_NAME`: tên hiển thị trong email gửi đi

## 3. Cấu hình Gmail nếu dùng tài khoản Gmail

Nếu bạn dùng Gmail để gửi email reset mật khẩu, hãy làm theo các bước sau:

1. Đăng nhập vào tài khoản Gmail bạn dùng để gửi mail.
2. Vào mục Bảo mật.
3. Tìm phần "Xác minh 2 bước" và bật lên.
4. Tạo "Mật khẩu ứng dụng" (App Password).
5. Dùng mật khẩu ứng dụng đó cho biến `SMTP_PASSWORD`.

Ví dụ:

```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=example@gmail.com
SMTP_PASSWORD=abcd efgh ijkl mnop
SMTP_FROM_EMAIL=example@gmail.com
SMTP_FROM_NAME=EduTest
```

> Gmail không cho phép dùng mật khẩu thường cho SMTP, nên bắt buộc phải dùng App Password.

## 4. Đảm bảo file `.env` được load

Dự án đã có file `config/env.php` để đọc các biến từ `.env` và gán vào `$_ENV` / `getenv()`. Khi chạy ứng dụng, hệ thống sẽ tự đọc file này nếu tồn tại.

## 5. Cách sử dụng chức năng quên mật khẩu

1. Mở trang đăng nhập.
2. Click vào nút "Quên mật khẩu?".
3. Nhập email đã đăng ký.
4. Hệ thống sẽ kiểm tra email trong database.
5. Nếu email tồn tại, hệ thống tạo token và gửi email chứa link đặt lại mật khẩu.
6. Mở email nhận được và click vào liên kết.
7. Nhập mật khẩu mới và xác nhận.
8. Hệ thống sẽ cập nhật mật khẩu mới và xóa token cũ.

## 6. Luồng hoạt động thực tế

- Người dùng nhập email ở trang quên mật khẩu.
- Controller `AuthController::forgotPassword()` sẽ:
  - kiểm tra email có tồn tại không
  - tạo `token` ngẫu nhiên
  - lưu token vào database
  - gửi email bằng PHPMailer qua SMTP
- Khi người dùng click vào link, hệ thống gọi `resetPassword()`.
- Nếu token hợp lệ, người dùng nhập mật khẩu mới.
- Mật khẩu sẽ được hash và lưu vào database.

## 7. Các lỗi thường gặp

### Lỗi 1: Email không gửi được
- Kiểm tra lại `.env`
- Kiểm tra `SMTP_HOST`, `SMTP_PORT`, `SMTP_USERNAME`, `SMTP_PASSWORD`
- Với Gmail, hãy dùng App Password thay vì mật khẩu thường
- Kiểm tra nếu tài khoản email có bật bảo mật 2 lớp

### Lỗi 2: Không nhận được email
- Kiểm tra hộp thư rác / spam
- Kiểm tra email có đúng với tài khoản đã đăng ký hay không
- Kiểm tra server có quyền gửi SMTP hay không

### Lỗi 3: Link reset không hoạt động
- Kiểm tra token đã được lưu vào database chưa
- Token có thể hết hạn nếu đã được sử dụng hoặc bị mất
- Kiểm tra URL được tạo có đúng route `index.php?action=reset_password&token=...`

## 8. Ví dụ URL reset

```text
http://localhost/study-management/index.php?action=reset_password&token=abc123xyz
```

## 9. Gợi ý bảo mật

- Không lưu token ở dạng plain text không cần thiết
- Nên có thời gian hết hạn cho token, ví dụ 2 giờ
- Nên chỉ gửi email qua SMTP có xác thực
- Không phát tán email reset cho người khác

## 10. Kiểm tra nhanh

Bạn có thể test chức năng bằng cách:

1. Tạo tài khoản người dùng trong hệ thống
2. Vào trang đăng nhập
3. Click "Quên mật khẩu?"
4. Nhập email đã đăng ký
5. Kiểm tra email đến và click link reset

Nếu mọi thứ đúng, hệ thống sẽ hoạt động như mong đợi.

---

Nếu bạn muốn, mình có thể tiếp tục tạo thêm file `.env.example` để bạn dễ copy và paste cấu hình SMTP vào dự án. 
