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

## 5. Báo cáo riêng về module chấm điểm tự động và báo cáo thống kê

Phần này là phần Hợp phụ trách. Mục tiêu không chỉ là làm cho hệ thống hiện ra một con điểm, mà là giải thích được:

- Hệ thống chấm như thế nào và vì sao kết quả đó là đúng.
- Giáo viên đọc được gì từ kết quả của cả lớp.
- Học sinh xem lại được gì sau mỗi lần làm bài.
- Mỗi quyết định trong code giải quyết vấn đề nghiệp vụ nào.

### 5.1. Em đã dùng gì để phân tích hệ thống?

Trước khi chỉnh code, em đọc luồng nghiệp vụ theo thứ tự một người dùng thực tế sẽ đi qua:

1. Học sinh mở đề và bắt đầu một lượt thi.
2. Học sinh chọn đáp án, hệ thống tự lưu đáp án.
3. Học sinh nộp bài, hoặc hệ thống tự nộp khi hết giờ/vượt giới hạn vi phạm.
4. Server chấm điểm và lưu kết quả.
5. Giáo viên xem báo cáo của đề.
6. Học sinh xem lịch sử và chi tiết câu đúng/sai của mình.

Sau đó em đối chiếu luồng này với:

- Route trong `index.php`.
- Controller trong `controllers/ExamController.php`.
- Giao diện trong `views/exams/`.
- Cấu trúc dữ liệu trong `db.sql`.
- Các trường hợp có thể làm sai điểm: nộp nhiều lần, câu nhiều đáp án, bỏ trống câu, hết giờ, vi phạm, giáo viên sửa đáp án giữa lúc học sinh đang làm.

Em cũng dùng dữ liệu mẫu trong SQL để tự kiểm tra các trường hợp như chọn đúng, chọn sai, chọn nhiều đáp án và làm lại cùng một đề nhiều lần. Cách phân tích này giúp em nhìn module theo cả ba phần: giao diện, xử lý nghiệp vụ và dữ liệu lưu trong database.

### 5.2. Ý 1 - Chấm điểm tự động: em vận dụng được gì?

#### Định nghĩa bài toán

Một câu hỏi được tính đúng khi tập đáp án học sinh chọn giống hoàn toàn tập đáp án đúng của câu hỏi:

- Câu một đáp án: phải chọn đúng đáp án duy nhất.
- Câu nhiều đáp án: phải chọn đủ và không chọn thừa đáp án đúng.
- Không chọn hoặc chọn thiếu đều không được tính điểm.

Điểm của lượt thi là tổng `score_weight` của những câu được tính đúng. Việc chấm được thực hiện ở server, không tin vào kết quả do trình duyệt gửi lên.

#### Cách em triển khai

Luồng chấm chính nằm ở `ExamController::finalizeAttempt()`:

1. Kiểm tra lượt thi có thuộc đúng học sinh và vẫn đang `in_progress` hay không.
2. Lọc các đáp án gửi lên, chỉ giữ những đáp án thật sự thuộc đề thi.
3. Lưu lựa chọn vào `attempt_answers`.
4. Lấy tập đáp án đúng và tập đáp án học sinh chọn.
5. Sắp xếp hai tập rồi so sánh.
6. Ghi `is_correct` cho từng câu.
7. Cộng điểm theo trọng số câu hỏi và lưu vào `exam_attempts.total_score`.

Em cũng thêm một số kiểm soát để kết quả không bị thay đổi ngoài ý muốn:

- Không cho bắt đầu đề có câu `fill_blank` hoặc `essay` khi hệ thống chưa có cách chấm tương ứng.
- Câu một đáp án phải có đúng một đáp án đúng.
- Không để cùng một câu xuất hiện nhiều lần trong một đề làm điểm bị cộng lặp.
- Khi bắt đầu lượt thi, hệ thống lưu snapshot đáp án đúng vào `attempt_correct_answers`. Vì vậy nếu giáo viên sửa ngân hàng câu hỏi sau đó, lượt thi đang diễn ra vẫn được chấm theo đáp án tại thời điểm học sinh bắt đầu.
- Khi tự nộp do hết giờ hoặc vi phạm, câu chưa trả lời vẫn được tạo bản ghi để báo cáo không bị thiếu câu.

#### File và dữ liệu liên quan

- `controllers/ExamController.php`: nhận bài, lưu đáp án, chấm điểm và hoàn tất lượt thi.
- `views/exams/take.php`: giao diện chọn đáp án, tự lưu và nộp bài.
- `views/exams/result.php`: hiển thị điểm sau khi nộp.
- `views/exams/attempt-detail.php`: đối chiếu từng câu và đáp án đúng/sai.
- `exam_attempts`: lưu một lượt làm bài và tổng điểm.
- `attempt_answers`: lưu lựa chọn của học sinh và trạng thái đúng/sai.
- `attempt_correct_answers`: lưu đáp án đúng tại thời điểm bắt đầu lượt thi.
- `exam_questions`: lưu câu hỏi trong đề và trọng số điểm.

### 5.3. Ý 2 - Báo cáo của giảng viên: em vận dụng được gì?

Em hiểu báo cáo giảng viên không nên chỉ là một bảng điểm dài. Giáo viên cần nhìn nhanh được tình hình, sau đó mới đi sâu vào từng câu hỏi và từng học sinh.

Trang thống kê giáo viên hiện có các phần:

- KPI số lượt tham gia.
- Điểm trung bình, cao nhất và thấp nhất theo từng lượt.
- Tỷ lệ đạt. Một lượt đạt khi số câu đúng lớn hơn hoặc bằng một nửa tổng số câu trong đề.
- Số lượt đang chờ xem xét do vi phạm.
- Biểu đồ tổng quan đúng/sai có cả số lượng và phần trăm.
- Top câu hỏi có tỷ lệ sai cao nhất để biết nội dung nào học sinh đang yếu.
- Phổ điểm có thể chọn:
  - Lần đầu.
  - Lần cao nhất.
  - Tất cả các lượt.
- Bảng danh sách nộp bài có tìm kiếm theo tên và lọc `Tất cả`, `Đã nộp`, `Vi phạm`.

Phần phổ điểm được tách khỏi bảng chi tiết. Bảng vẫn hiển thị tất cả lượt để giáo viên tra cứu, còn biểu đồ cho phép chọn cách tính để tránh nhầm giữa “số sinh viên” và “số lượt làm bài”.

Các file chính:

- `controllers/ExamController.php`, hàm `statistics()`.
- `views/exams/statistics.php`.
- `views/exams/monitoring.php` và `views/exams/attempt-detail.php` cho phần theo dõi và xem chi tiết.

### 5.4. Ý 3 - Báo cáo và thống kê của sinh viên: em vận dụng được gì?

Ở phía học sinh, em tách rõ hai khái niệm:

- **Số đề đã tham gia:** mỗi đề chỉ tính một lần.
- **Tổng lượt làm:** tính tất cả những lần học sinh đã hoàn thành, kể cả làm lại cùng một đề.

Trang thống kê cá nhân có:

- Bộ lọc xem tất cả đề hoặc một đề cụ thể.
- Điểm trung bình.
- Tỷ lệ chính xác.
- Thời gian trung bình hoàn thành bài.
- Biểu đồ tiến bộ theo từng lượt của từng đề.
- Biểu đồ được quy đổi sang phần trăm điểm để có thể so sánh các đề có tổng điểm khác nhau.
- Nhãn điểm hiển thị trực tiếp trên các điểm của biểu đồ.
- Bảng lịch sử có lần thi, đề thi, điểm, số câu đúng/sai, thời lượng, thời gian hoàn thành và trạng thái.
- Huy hiệu `Vi phạm - Tự nộp` hoặc `Hết giờ` để học sinh hiểu vì sao lượt thi kết thúc.
- Nút xem chi tiết từng câu đúng/sai.

Các file chính:

- `controllers/ExamController.php`, hàm `studentStatistics()`.
- `views/exams/student-statistics.php`.
- `views/exams/attempt-detail.php`.

### 5.5. Em viết code như thế nào và dùng AI ở đâu?

Phần lớn quá trình làm module này em dùng **vibe coding** với AI, nhưng em không xem việc AI sinh ra code là đã hoàn thành bài. Cách em làm là:

1. Đọc code và mô tả lại cho AI luồng hiện tại.
2. Nêu rõ yêu cầu nghiệp vụ, ví dụ: “câu nhiều đáp án chỉ đúng khi tập lựa chọn giống hoàn toàn đáp án đúng”.
3. Nhờ AI đề xuất hoặc viết phần thay đổi nhỏ.
4. Em đọc lại diff, kiểm tra tên biến, câu SQL, quyền truy cập và luồng dữ liệu.
5. Nếu có thay đổi giao diện, em đối chiếu lại với dữ liệu thật mà giao diện cần hiển thị.
6. Chạy kiểm tra cú pháp và kiểm tra Problems trong VS Code.
7. Thử các trường hợp biên thay vì chỉ thử trường hợp làm bài bình thường.

AI được dùng nhiều ở phần gợi ý cấu trúc, viết truy vấn, chỉnh giao diện và rà soát các trường hợp thiếu. Những phần em cần tự hiểu và tự giải thích khi báo cáo là:

- Vì sao chấm ở server.
- Vì sao phải lưu snapshot đáp án đúng.
- Vì sao phải tách số đề và số lượt.
- Vì sao phổ điểm cần có lựa chọn cách tính.
- Vì sao biểu đồ câu hỏi sai nhiều có ích hơn một tỷ lệ đúng/sai tổng quát.

Nói cách khác, AI hỗ trợ em viết nhanh hơn, còn yêu cầu nghiệp vụ, cách kiểm tra và quyết định giữ hay sửa code là phần em phải chịu trách nhiệm.

### 5.6. Em kiểm soát code bằng cách nào?

Để không phụ thuộc mù quáng vào code sinh ra, em dùng các cách sau:

- Kiểm tra quyền truy cập ở controller, không chỉ ẩn nút trên giao diện.
- Dùng prepared statement cho truy vấn có dữ liệu người dùng.
- Kiểm tra dữ liệu đầu vào và giới hạn đáp án theo đúng câu hỏi của đề.
- Dùng transaction khi hoàn tất lượt thi.
- Kiểm tra trạng thái lượt thi trước khi chấm, tránh nộp hai lần.
- Chạy `php -l` cho các file PHP đã sửa.
- Kiểm tra Problems panel trong VS Code.
- Chạy `git diff --check` để bắt lỗi khoảng trắng/định dạng.
- Đọc lại diff sau mỗi thay đổi lớn.
- Kiểm tra cả trường hợp bình thường và trường hợp biên: bỏ trống, chọn nhiều đáp án, hết giờ, vi phạm, làm lại nhiều lần, sửa đáp án giữa lượt thi.

Các kiểm tra này chưa thay thế cho một bộ test tự động đầy đủ. Những phần nên làm tiếp là test chấm điểm theo từng loại câu hỏi, test quyền xem báo cáo, test nhiều lượt làm và test dữ liệu lớp không có sinh viên.

### 5.7. Những công cụ em đã biết và đang dùng

- VS Code để đọc code, xem Problems và quản lý thay đổi.
- PHP CLI để kiểm tra cú pháp.
- MariaDB/MySQL và phpMyAdmin để xem cấu trúc, dữ liệu và kiểm tra truy vấn.
- Git để xem diff và theo dõi file đã thay đổi.
- Browser DevTools để kiểm tra request, response và lỗi JavaScript.
- Chart.js để vẽ biểu đồ thống kê.
- AI/Copilot SDK để phân tích code, gợi ý hướng xử lý, tạo bản nháp code và rà soát các trường hợp có thể bỏ sót.

### 5.8. Hạn chế và hướng phát triển

Module hiện tại đã đáp ứng phần chấm điểm và báo cáo cho câu hỏi trắc nghiệm một đáp án/nhiều đáp án. Một số hướng phát triển tiếp:

- Thêm chấm tự động cho điền khuyết với quy tắc chuẩn hóa câu trả lời.
- Thêm quy trình chấm và phúc khảo cho câu tự luận.
- Xuất báo cáo CSV/Excel.
- Cho giáo viên chọn cách tính KPI riêng: theo lượt, theo sinh viên, lần đầu hoặc lần cao nhất.
- Thêm bộ test tự động cho toàn bộ luồng nộp bài và chấm điểm.
- Có lịch sử thay đổi đáp án và cấu hình đề để dễ kiểm tra khi cần giải trình.hai và trong khoảng thời gian mở; chưa thấy giao diện quản lý trạng thái đề. |

Diễn đàn được ghi là ngoài phạm vi MVP ban đầu, nhưng sau đó đã được giao cho Thành viên 5 trong bảng phân công. Trong phiên bản code hiện tại, phần này mới có cấu trúc dữ liệu và chưa có chức năng sử dụng.

## 5. Đối chiếu tiêu chuẩn an toàn

- **Phân quyền/RBAC:** có kiểm tra đăng nhập, kiểm tra vai trò học sinh ở luồng làm bài, kiểm tra quyền giáo viên trên thống kê và kiểm tra chủ sở hữu lượt thi ở một số luồng. Chưa có hệ thống RBAC bao quát mọi chức năng; cần rà soát mọi route và giới hạn đăng ký vai trò.
- **Bảo vệ đáp án trước khi nộp:** truy vấn câu hỏi dùng cho màn làm bài lấy nội dung lựa chọn nhưng không lấy cột `is_correct`; đáp án được đối chiếu và chấm ở phía server khi nộp. Đây là hướng xử lý phù hợp với yêu cầu không gửi đáp án đúng trước khi nộp.
- **Audit log thay đổi điểm:** chưa thấy bảng hoặc luồng lưu lịch sử thay đổi điểm; hiện có log vi phạm trong lúc thi, không phải audit log chỉnh sửa điểm.
- **Upload an toàn:** chưa thấy luồng tải tệp đề thi/tài liệu trong ứng dụng, nên chưa có kiểm tra định dạng hoặc quét mã độc.
- **Chống gian lận:** đã ghi nhận một số sự kiện trình duyệt và áp dụng ngưỡng xử lý; đây là biện pháp hỗ trợ giám sát, không thể thay thế xác thực danh tính hoặc bảo đảm chống gian lận tuyệt đối.

## 6. Kết luận

Code hiện tại đã thể hiện rõ luồng thi trực tuyến, tự lưu bài, chấm điểm tự động, xem kết quả/thống kê và giám sát một số hành vi trong khi thi. Xác thực tài khoản và khôi phục mật khẩu cũng đã có. Các phần cần ưu tiên hoàn thiện tiếp gồm quản trị người dùng/lớp và RBAC đầy đủ, giao diện tạo đề/ngân hàng câu hỏi, chức năng xuất dữ liệu, audit log thay đổi điểm và các chức năng diễn đàn nếu nhóm đưa diễn đàn vào phạm vi phiên bản này.
