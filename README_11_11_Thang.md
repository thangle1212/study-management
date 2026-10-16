# Báo cáo cá nhân: Xây dựng và kiểm soát chức năng tài khoản

**Sinh viên:** Thắng  
**Dự án:** EduTest / Study Management  
**Phần em phụ trách:** Đăng nhập, đăng ký, quên/đặt lại mật khẩu, phân quyền và quản lý tài khoản bằng tài khoản Admin.

## 1. Cách em xây dựng hệ thống với sự trợ giúp của Agent (3 điểm)

### 1.1. Lên yêu cầu và kế hoạch chức năng

Khi xây dựng phần quản lý tài khoản Admin, trước tiên em xác định các thao tác người quản trị cần thực hiện:

- Xem danh sách và tìm kiếm tài khoản.
- Tạo tài khoản mới và chọn vai trò ngay khi tạo.
- Thay đổi vai trò giữa `student`, `teacher` và `admin`.
- Sửa email hoặc đặt mật khẩu mới cho tài khoản.
- Khóa và mở khóa tài khoản.

Sau đó em chia công việc theo cấu trúc MVC của dự án:

1. **Route:** thêm action trong `index.php` để điều hướng đến chức năng quản trị.
2. **Kiểm tra quyền:** chỉ tài khoản có role `admin` mới được mở và thao tác trên trang quản trị.
3. **Nghiệp vụ:** xử lý kiểm tra dữ liệu, cập nhật tài khoản và tạo tài khoản trong `AdminController`.
4. **Truy vấn dữ liệu:** bổ sung các hàm tương ứng trong model `User`.
5. **Giao diện:** tạo/chỉnh sửa các trang riêng trong `views/admin`, đồng bộ phong cách với giao diện học sinh đã có.
6. **Kiểm thử:** kiểm tra cú pháp, các trường hợp hợp lệ/không hợp lệ và quyền truy cập trước khi kết luận chức năng hoàn thành.

Trong dự án hiện tại, phần kiểm tra quyền không nằm trong một file riêng chỉ chứa danh sách role. `BaseController` cung cấp các hàm kiểm tra đăng nhập, quyền Admin và CSRF; `AdminController` gọi các hàm này trước khi xử lý yêu cầu quản trị. Role được lưu trong cột `users.role`.

### 1.2. Cách em dùng Agent

Em dùng Copilot Agent trong VS Code để hỗ trợ đọc cấu trúc dự án, lần theo route → controller → model → view và đề xuất thay đổi. Em không yêu cầu Agent “làm toàn bộ phần Admin” một cách chung chung mà chia thành từng đầu việc có tiêu chí cụ thể.

Ví dụ yêu cầu em đưa cho Agent:

> “Đọc luồng quản lý user hiện tại. Bổ sung chức năng cho Admin tạo tài khoản với role student/teacher/admin, sửa email và đặt lại mật khẩu; mật khẩu phải được hash, kiểm tra email trùng ở server, các form thay đổi dữ liệu phải dùng POST và CSRF. Giữ lại các chức năng lọc role, tìm kiếm, khóa/mở khóa và phong cách giao diện Admin hiện tại. Sau khi sửa, liệt kê file thay đổi và cách kiểm tra.”

Với giao diện, em mô tả rõ mong muốn: đặt trong `views/admin`, nền sáng, các thẻ nền trắng bo góc, dùng màu xanh dương và icon để đồng bộ với giao diện học sinh. Em đưa giao diện có sẵn làm tham khảo, sau đó kiểm tra phần Agent tạo để bảo đảm vẫn giữ đủ chức năng chứ không chỉ đẹp về hình thức.

Quy trình của em khi làm việc với Agent:

1. Đọc yêu cầu và xác định phạm vi file cần sửa.
2. Tìm code hiện có trước để dùng lại cấu trúc, không tạo thêm cách xử lý trùng lặp.
3. Đưa yêu cầu và tiêu chí nghiệm thu rõ ràng cho Agent.
4. Đọc lại từng phần diff, đặc biệt là route, kiểm tra quyền, truy vấn SQL và xử lý mật khẩu.
5. Chạy kiểm tra cú pháp và thử các ca chức năng.
6. Chỉ stage/commit các thay đổi em đã kiểm tra.

Agent hỗ trợ viết và tìm code nhanh hơn, nhưng em vẫn phải tự hiểu luồng, xem diff và chịu trách nhiệm với kết quả.

### 1.3. Kỹ năng/skill hỗ trợ
Em dùng khả năng tìm kiếm trong IDE và `rg` để tìm tên hàm, route, nơi dùng role và các điểm ghi/đọc mật khẩu trước khi sửa. Việc này giúp xác định ảnh hưởng của một thay đổi trên nhiều file, ví dụ thêm chính sách mật khẩu thì không chỉ sửa form đăng ký mà còn phải tìm luồng reset password và Admin đặt mật khẩu mới.

## 2. Kỹ năng kiểm soát code (5 điểm)

### 2.a. Biết chức năng ở đâu và sửa như thế nào

| Chức năng | Vị trí trong dự án | Em hiểu luồng xử lý |
|---|---|---|
| Điều hướng | `index.php` | Nhận `action` và gọi controller tương ứng, ví dụ `login`, `register`, `admin_users`, `admin_update_user`, `admin_create_user`. |
| Đăng nhập | `controllers/AuthController.php` — `login()` | Tìm tài khoản theo email qua model, dùng `password_verify()` so mật khẩu nhập vào với hash trong DB, kiểm tra tài khoản có hoạt động rồi mới tạo session. |
| Đăng ký | `controllers/AuthController.php` — `register()` | Nhận thông tin từ form; tài khoản đăng ký công khai được gán role `student` ở server, không tin role do trình duyệt gửi lên. |
| Quên mật khẩu | `controllers/AuthController.php` — `forgotPassword()` | Sinh token ngẫu nhiên, yêu cầu model lưu token và thời hạn, sau đó gửi đường dẫn khôi phục qua PHPMailer/SMTP. |
| Đặt lại mật khẩu | `controllers/AuthController.php` — `resetPassword()`; `models/User.php` | Kiểm tra token còn hạn, kiểm tra mật khẩu và xác nhận, lưu hash mới rồi xóa token đã dùng. |
| Kiểm tra đăng nhập/quyền | `controllers/BaseController.php` — `requireLogin()`, `requireAdmin()` | Kiểm tra session, trạng thái và role từ database; chỉ Admin được truy cập các chức năng quản trị. |
| Kiểm tra POST/CSRF | `controllers/BaseController.php` — `requirePostWithCsrf()` | Từ chối request không phải POST hoặc token không trùng token trong session. |
| Danh sách/tìm kiếm user | `controllers/AdminController.php`, `models/User.php`, `views/admin/users.php` | Controller kiểm tra quyền và lọc role; model lấy danh sách; view hiển thị, lọc theo role và tìm kiếm tên/email. |
| Tạo user và cấp role | `controllers/AdminController.php` — `createUserAction()`; `models/User.php`; `views/admin/users.php` | Admin gửi form POST; server kiểm tra dữ liệu, role và email trùng; model hash mật khẩu trước khi thêm vào bảng `users`. |
| Sửa email/đặt mật khẩu mới | `controllers/AdminController.php` — `updateUserAction()`; `models/User.php` — `updateAccountDetails()` | Admin sửa email và có thể đặt mật khẩu mới. Bỏ trống trường mật khẩu thì chỉ cập nhật email. |
| Khóa/mở khóa và đổi role | `controllers/AdminController.php`; `models/User.php` | Server chỉ chấp nhận trạng thái/role thuộc danh sách cho phép; không cho Admin tự khóa hoặc tự đổi role tài khoản của mình. |

**Ví dụ nếu thầy yêu cầu thêm điều kiện “mật khẩu phải có ký tự @”:**

1. Em tìm tất cả nơi tạo hoặc đổi mật khẩu: đăng ký, đặt lại mật khẩu, Admin tạo user và Admin đặt mật khẩu mới.
2. Em đặt kiểm tra ở server-side, có thể tạo một hàm kiểm tra dùng chung trong model/service để các luồng cùng áp dụng.
3. Form có thể thêm gợi ý cho người dùng, nhưng kiểm tra HTML/JavaScript không thay thế kiểm tra ở server vì request có thể được gửi trực tiếp.
4. Em thêm test cho mật khẩu có `@`, không có `@`, ngắn hơn giới hạn và trường hợp xác nhận không khớp.
5. Em xem lại diff và tìm kiếm toàn dự án để bảo đảm không còn luồng đổi mật khẩu nào bỏ qua điều kiện mới.

Hiện tại trang đăng ký có thuộc tính `minlength="6"` ở phía trình duyệt, còn các luồng Admin và đặt lại mật khẩu có kiểm tra tối thiểu 6 ký tự ở server. `AuthController::register()` hiện chưa tự kiểm tra độ dài mật khẩu ở server, nên nếu muốn bảo đảm chính sách này thì em cần bổ sung kiểm tra đó. Code **chưa bắt buộc mật khẩu có `@`**; nếu nhận yêu cầu này, em cần triển khai đồng bộ ở mọi luồng đổi/tạo mật khẩu và kiểm thử lại.

### 2.b. Hiểu log, diff và cách đánh giá hiệu quả làm việc

Các công cụ Git và log giúp em biết thay đổi nào đã xảy ra, chứ không chỉ dựa vào lời nói:

- `git status`: xem nhánh hiện tại, file nào đã sửa, đã stage hay còn conflict.
- `git diff`: đọc nội dung thay đổi chưa stage; `git diff --cached`: kiểm tra phần sắp commit.
- `git diff --check`: tìm lỗi khoảng trắng/dòng thừa trong diff.
- `git log --oneline --graph`: xem lịch sử commit và quan hệ giữa các nhánh.
- Log PHP/PDO: dựa vào thông báo lỗi, tên file và dòng lỗi để lần theo route, controller, model và câu SQL liên quan.

Khi có lỗi, em tìm nguyên nhân từ dữ liệu đầu vào, session, truy vấn và ràng buộc database trước; không xóa validation hay khóa ngoại chỉ để làm thông báo lỗi biến mất.

Nếu đóng vai trò leader, em đánh giá thành viên theo các tiêu chí:

1. Hoàn thành đúng yêu cầu và phạm vi đã nhận.
2. Thay đổi chạy được và có kiểm thử phù hợp, gồm cả trường hợp lỗi/quyền bị từ chối.
3. Diff rõ ràng, không có code debug, conflict marker hoặc thay đổi ngoài phạm vi.
4. Giải thích được luồng code, lý do sửa và ảnh hưởng đến chức năng liên quan.
5. Commit/PR mô tả đúng nội dung, phối hợp và báo sớm khi gặp lỗi hoặc conflict.
6. Không để lộ mật khẩu, token khôi phục hay bí mật SMTP trong source code/Git.

Em không đánh giá hiệu quả chỉ bằng số dòng code hoặc số commit. Em sẽ đối chiếu task, diff, log kiểm thử và khả năng thành viên giải thích phần mình làm.

## 3. Kết hợp công cụ kiểm thử tính năng (2 điểm)

Em chia kiểm thử thành các lớp để không chỉ nhìn giao diện rồi kết luận chức năng đúng:

1. **Lint PHP:** chạy `php -l` trên các file PHP đã sửa để bắt lỗi cú pháp.
2. **Kiểm tra diff:** chạy `git diff --check`, rà conflict marker và kiểm tra file thay đổi.
3. **Kiểm thử model/database:** dùng database tạm để thử tạo user, kiểm tra email trùng, xác minh password được hash, sửa email và đặt mật khẩu mới.
4. **Kiểm thử tích hợp:** thao tác từ form trên trình duyệt, đi qua route/controller/model và xác nhận kết quả trong database.
5. **Kiểm thử quyền và dữ liệu không hợp lệ:** thử user không phải Admin truy cập route quản trị, token CSRF sai, role không hợp lệ, email trùng, ID không tồn tại và mật khẩu không đạt yêu cầu.

| Chức năng | Trường hợp đúng | Trường hợp cần bị từ chối |
|---|---|---|
| Đăng nhập | Email/mật khẩu đúng, tài khoản active | Sai mật khẩu, email không tồn tại, tài khoản locked |
| Đăng ký | Email mới, dữ liệu hợp lệ, role được gán student | Email trùng, dữ liệu không hợp lệ, client tự gửi role admin |
| Quên/đặt lại mật khẩu | Token hợp lệ và chưa hết hạn; mật khẩu xác nhận khớp | Token sai/hết hạn, mật khẩu không đạt yêu cầu, xác nhận không khớp |
| Admin tạo tài khoản | Admin tạo student/teacher/admin với email mới | Không phải Admin, CSRF sai, email trùng, role ngoài danh sách |
| Admin sửa tài khoản | Đổi email; đổi mật khẩu; để trống mật khẩu để giữ nguyên | Email sai/trùng, mật khẩu mới không đạt yêu cầu |
| Phân quyền/khóa tài khoản | Admin đổi role hoặc khóa/mở user khác | Người thường gọi route Admin; Admin tự đổi role hoặc tự khóa |

Kết quả kiểm thử chỉ được ghi là **đã đạt** khi đã chạy trên phiên bản code cuối và có thể chỉ ra log/ảnh chụp tương ứng. Nếu mới xây dựng test case nhưng chưa chạy thì em ghi là **kế hoạch kiểm thử**, không ghi thành kết quả pass.

## 4. Lưu ý bảo mật và giới hạn chức năng

- Mật khẩu được lưu dưới dạng hash một chiều. Admin không thể xem lại mật khẩu cũ; Admin chỉ có thể đặt mật khẩu mới.
- Không nên lưu mật khẩu đọc được hoặc dùng mã hóa có thể giải ngược chỉ để Admin xem, vì khi database hoặc khóa bị lộ thì mật khẩu người dùng cũng có thể bị lộ.
- Các form thay đổi dữ liệu quản trị dùng POST và CSRF; quyền được kiểm tra ở server, không chỉ ẩn nút trên giao diện.
- Email và mật khẩu mới được kiểm tra ở server; mật khẩu được hash trước khi ghi vào database.
- Cấu hình SMTP như username/App Password phải đặt ngoài source code và không commit lên Git.

## 5. Tóm tắt để trình bày

> Phần em phụ trách là vòng đời tài khoản gồm đăng nhập, đăng ký, quên/đặt lại mật khẩu và quản trị tài khoản. Em lên danh sách tính năng, chia việc theo route, controller, model, view rồi dùng Copilot Agent hỗ trợ đọc và triển khai từng phần. Với chức năng Admin, người có quyền có thể tìm tài khoản, tạo user và chọn role, đổi role, sửa email, đặt mật khẩu mới và khóa/mở tài khoản. Em kiểm soát thay đổi bằng `git status`, `git diff`, log và test; đăng nhập xác minh bằng `password_verify()`, mật khẩu lưu bằng `password_hash()`, còn các thao tác Admin được kiểm tra quyền và CSRF ở server. Em đánh giá kết quả qua yêu cầu, diff, kiểm thử và khả năng giải thích, không chỉ qua số dòng code.
