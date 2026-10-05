# Báo cáo dự án EduTest - Nhóm 4/11

## 1. Thông tin nhóm

| Thành viên | Vai trò/phần phụ trách |
|---|---|
| Thắng | Thành viên 1, nhóm trưởng: tài khoản, phân quyền, người dùng và lớp học |
| Cảnh | Thành viên 2: ngân hàng câu hỏi và soạn đề thi |
| Dũng | Thành viên 3: giao diện làm bài và giám sát vi phạm |
| Hợp | Thành viên 4: chấm điểm và thống kê |
| Bình | Thành viên 5: diễn đàn và kiểm duyệt nội dung |

Phần đánh giá dưới đây căn cứ vào bảng phân công của nhóm và các file hiện có trong repository. Phân công không đồng nghĩa với xác nhận tác giả từng dòng code hoặc commit.

## 2. Ý tưởng và mục tiêu dự án

EduTest là website hỗ trợ học sinh, sinh viên, giáo viên và người có nhu cầu tự học. Người dùng có thể tham gia các bài kiểm tra trực tuyến, theo dõi kết quả và sử dụng dữ liệu học tập để đánh giá tiến độ. Về định hướng dài hạn, hệ thống có thể mở rộng thêm lớp học, tài liệu và không gian thảo luận kiến thức.

Đối tượng sử dụng không giới hạn độ tuổi, gồm học sinh, sinh viên, giáo viên và người tự học. Trong phạm vi phiên bản hiện tại, luồng kiểm tra trực tuyến, tài khoản, kết quả và thống kê là những phần thể hiện rõ nhất trong code.

## 3. Phân công và kết quả theo thành viên

### Thành viên 1 - Thắng, nhóm trưởng

**Phạm vi được phân công:** đăng ký, đăng nhập, phân quyền; quản lý tài khoản và lớp học.

**Đã có trong code:**
- Đăng ký tài khoản bằng email, họ tên, trường học và mật khẩu.
- Đăng nhập, xác minh mật khẩu đã băm và từ chối tài khoản không hoạt động.
- Đăng xuất, quên mật khẩu và đặt lại mật khẩu qua email.
- Session lưu thông tin người dùng và vai trò.

**Đánh giá hiện tại:** phần xác thực tài khoản đã có; phần quản trị người dùng, quản lý lớp, thêm học sinh vào lớp và phân quyền đầy đủ theo RBAC chưa thấy có controller, route hoặc giao diện tương ứng trong repository. Form đăng ký nhận vai trò từ dữ liệu gửi lên, vì vậy cần bổ sung kiểm soát phía server để người dùng không tự chọn vai trò đặc quyền.

**File liên quan:** `controllers/AuthController.php`, `models/User.php`, `views/auth/`, bảng `users`, `classes`, `class_students` trong `db.sql`.

### Thành viên 2 - Cảnh

**Phạm vi được phân công:** tạo, sửa, xóa ngân hàng câu hỏi; tùy chỉnh loại câu hỏi; tạo đề và cấu hình câu hỏi, thời gian, thứ tự.

**Đã có trong code:**
- Cơ sở dữ liệu có các bảng `question_banks`, `questions`, `answers`, `exams`, `exam_questions`.
- Model đề thi đọc đề đã công khai và lấy câu hỏi, lựa chọn để phục vụ lượt thi.
- Dữ liệu mẫu có một ngân hàng câu hỏi, câu hỏi và đề thi minh họa.

**Đánh giá hiện tại:** phần đọc đề/câu hỏi phục vụ thi đã có. Trong repository hiện tại chưa thấy chức năng CRUD ngân hàng câu hỏi, tạo/sửa đề thi hoặc giao diện cho giáo viên soạn đề; các bảng dữ liệu và dữ liệu mẫu chưa thay thế cho các chức năng này.

**File liên quan:** `models/Exam.php`, `controllers/ExamController.php`, bảng `question_banks`, `questions`, `answers`, `exams`, `exam_questions` trong `db.sql`.

### Thành viên 3 - Dũng

**Phạm vi được phân công:** giao diện làm bài, đếm ngược, tự nộp và giám sát vi phạm.

**Đã có trong code:**
- Học sinh chuẩn bị và bắt đầu lượt thi; server lưu lượt thi cùng cấu hình tại thời điểm bắt đầu.
- Đồng hồ tính theo thời điểm bắt đầu và thời lượng; tải lại trang tiếp tục lượt thi hiện có.
- Đáp án được lưu tự động khi thay đổi và theo chu kỳ; có thể khôi phục đáp án đã lưu.
- Hệ thống tự hoàn tất bài khi hết giờ.
- Khi giám sát được bật, client ghi nhận rời tab, mất tiêu điểm cửa sổ, menu chuột phải, thao tác clipboard và một số phím tắt; server lưu log và có thể tự nộp khi chạm ngưỡng.
- Giáo viên có trang theo dõi lượt thi, nhật ký vi phạm và xử lý lượt bị tự nộp.

**Giới hạn:** trình duyệt không thể khóa các thao tác ở cấp hệ điều hành như Alt+Tab. Tính năng hiện ghi nhận một số sự kiện phía trình duyệt, không thể bảo đảm ngăn mọi hình thức gian lận.

**File liên quan:** `controllers/ExamController.php`, `views/exams/prepare.php`, `views/exams/take.php`, `views/exams/monitoring.php`, `views/exams/attempt-detail.php`, bảng `exam_attempts`, `violation_logs` trong `db.sql`.

### Thành viên 4 - Hợp

**Phạm vi được phân công:** chấm điểm tự động và thống kê kết quả.

**Đã có trong code:**
- Khi kết thúc lượt thi, server đối chiếu tập đáp án học sinh chọn với tập đáp án đúng và cộng điểm theo trọng số câu hỏi.
- Lưu điểm, trạng thái hoàn thành và lý do nộp vào lượt thi.
- Giáo viên xem danh sách kết quả, số lần làm, phân bố điểm và thống kê đúng/sai theo câu hỏi của đề.
- Học sinh xem lịch sử kết quả và thống kê cá nhân.
- Học sinh chỉ xem kết quả thuộc lượt thi của mình; giáo viên chỉ xem thống kê đề do mình phụ trách ở route thống kê.

**Đánh giá hiện tại:** chấm tự động và thống kê cơ bản đã được triển khai. Chưa thấy chức năng xuất dữ liệu báo cáo (ví dụ CSV) trong code hiện có.

**File liên quan:** `controllers/ExamController.php`, `views/exams/result.php`, `views/exams/statistics.php`, `views/exams/student-statistics.php`, bảng `exam_attempts`, `attempt_answers` trong `db.sql`.

### Thành viên 5 - Bình

**Phạm vi được phân công:** đăng bài, bình luận và kiểm duyệt diễn đàn.

**Hiện trạng trong repository:** cơ sở dữ liệu khai báo `forum_posts` và `forum_comments`, nhưng `controllers/ForumController.php` hiện rỗng; chưa thấy route diễn đàn trong `index.php` hoặc giao diện diễn đàn trong `views/`. Vì vậy chức năng đăng bài, bình luận, ẩn/xóa nội dung chưa được triển khai trong code hiện tại.

**File liên quan:** `controllers/ForumController.php`, `index.php`, các bảng `forum_posts`, `forum_comments` trong `db.sql`.

## 4. Đối chiếu với kế hoạch MVP

| Hạng mục kế hoạch | Hiện trạng theo code hiện có |
|---|---|
| Quản lý tài khoản | Có đăng ký, đăng nhập, đăng xuất, khôi phục mật khẩu; quản trị người dùng và RBAC đầy đủ còn thiếu. |
| Tạo đề thi, quản lý ngân hàng câu hỏi | Có cấu trúc bảng và chức năng đọc đề khi làm bài; giao diện/chức năng tạo và quản lý chưa thấy trong repository. |
| Học theo lớp, khóa học/tài liệu | Có bảng lớp, thành viên lớp và bài học trong SQL; chưa thấy luồng quản lý lớp/bài học trong ứng dụng. |
| Làm bài thi | Có luồng chuẩn bị, làm bài, lưu đáp án, đếm giờ và nộp bài. |
| Chấm điểm tự động | Có chấm phía server khi hoàn tất lượt thi. |
| Quản lý điểm và lịch sử thi | Có kết quả, thống kê phía giáo viên và lịch sử/thống kê phía học sinh. |
| Xuất dữ liệu | Chưa thấy chức năng xuất dữ liệu trong code hiện tại. |
| Trạng thái đề thi | Có truy vấn chỉ cho phép làm đề đã công khai và trong khoảng thời gian mở; chưa thấy giao diện quản lý trạng thái đề. |

Diễn đàn được ghi là ngoài phạm vi MVP ban đầu, nhưng sau đó đã được giao cho Thành viên 5 trong bảng phân công. Trong phiên bản code hiện tại, phần này mới có cấu trúc dữ liệu và chưa có chức năng sử dụng.

## 5. Đối chiếu tiêu chuẩn an toàn

- **Phân quyền/RBAC:** có kiểm tra đăng nhập, kiểm tra vai trò học sinh ở luồng làm bài, kiểm tra quyền giáo viên trên thống kê và kiểm tra chủ sở hữu lượt thi ở một số luồng. Chưa có hệ thống RBAC bao quát mọi chức năng; cần rà soát mọi route và giới hạn đăng ký vai trò.
- **Bảo vệ đáp án trước khi nộp:** truy vấn câu hỏi dùng cho màn làm bài lấy nội dung lựa chọn nhưng không lấy cột `is_correct`; đáp án được đối chiếu và chấm ở phía server khi nộp. Đây là hướng xử lý phù hợp với yêu cầu không gửi đáp án đúng trước khi nộp.
- **Audit log thay đổi điểm:** chưa thấy bảng hoặc luồng lưu lịch sử thay đổi điểm; hiện có log vi phạm trong lúc thi, không phải audit log chỉnh sửa điểm.
- **Upload an toàn:** chưa thấy luồng tải tệp đề thi/tài liệu trong ứng dụng, nên chưa có kiểm tra định dạng hoặc quét mã độc.
- **Chống gian lận:** đã ghi nhận một số sự kiện trình duyệt và áp dụng ngưỡng xử lý; đây là biện pháp hỗ trợ giám sát, không thể thay thế xác thực danh tính hoặc bảo đảm chống gian lận tuyệt đối.

## 6. Kết luận

Code hiện tại đã thể hiện rõ luồng thi trực tuyến, tự lưu bài, chấm điểm tự động, xem kết quả/thống kê và giám sát một số hành vi trong khi thi. Xác thực tài khoản và khôi phục mật khẩu cũng đã có. Các phần cần ưu tiên hoàn thiện tiếp gồm quản trị người dùng/lớp và RBAC đầy đủ, giao diện tạo đề/ngân hàng câu hỏi, chức năng xuất dữ liệu, audit log thay đổi điểm và các chức năng diễn đàn nếu nhóm đưa diễn đàn vào phạm vi phiên bản này.
