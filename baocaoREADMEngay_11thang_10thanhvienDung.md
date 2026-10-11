# Module giao diện làm bài thi và giám sát vi phạm

## 1. Module này giải quyết vấn đề gì?

Trong một bài thi trực tuyến, học sinh cần biết mình đang làm đề nào, còn bao nhiêu thời gian và đáp án đã được lưu hay chưa. Giáo viên cũng cần biết học sinh nào đang làm, ai đã nộp bài và lượt thi nào có sự kiện cần xem xét.

Phần Thành viên 3 tập trung vào hai phía của cùng một lượt thi:

- **Phía học sinh:** chuẩn bị, bắt đầu, làm bài, lưu tiến độ và nộp bài.
- **Phía giáo viên:** thiết lập quy chế giám sát, theo dõi trạng thái, xem log và xử lý lượt bị tự nộp.

Mục tiêu là để lượt thi được điều khiển bằng trạng thái và dữ liệu server, thay vì chỉ dựa vào đồng hồ hoặc kết quả do trình duyệt gửi lên.

---

## 2. Cách em phân tích luồng

Em đi theo quy trình người dùng thực tế trước khi đối chiếu code:

```text
Giáo viên cấu hình đề
        ↓
Học sinh xem quy chế và bắt đầu
        ↓
Server tạo lượt thi, chụp cấu hình và đáp án đúng
        ↓
Học sinh làm bài, autosave và theo dõi thời gian
        ↓
Nộp thủ công / hết giờ / chạm ngưỡng vi phạm
        ↓
Server hoàn tất lượt thi và ghi lý do
        ↓
Giáo viên xem log, thống kê và quyết định phúc khảo
```

Sau đó em kiểm tra ba phần liên quan:

- **Giao diện:** học sinh và giáo viên nhìn thấy trạng thái gì, thao tác ở đâu?
- **Controller:** server xác thực người dùng, thời hạn, đáp án và quyền sở hữu lượt thi ra sao?
- **Database:** thời gian, đáp án lưu, cấu hình lúc bắt đầu, log và lý do nộp được ghi ở bảng nào?

Các trường hợp cần nghĩ trước gồm tải lại trang, mất mạng, bấm nộp hai lần, hết giờ giữa lúc autosave, chuyển tab nhiều lần, giáo viên đổi cấu hình giữa lúc học sinh đang thi và học sinh gửi một answer ID không thuộc đề.

---

## 3. Ý 1 - Giao diện làm bài của học sinh

### 3.1. Trang chuẩn bị trước khi thi

Học sinh chọn đề công khai từ dashboard để mở trang chuẩn bị. Trang này hiển thị:

- Tên đề và số câu hỏi.
- Thời lượng làm bài.
- Trạng thái tráo thứ tự câu hỏi và đáp án.
- Trạng thái giám sát, ngưỡng vi phạm và cách xử lý khi chạm ngưỡng.
- Lịch sử các lượt đã hoàn thành và đường dẫn xem chi tiết.
- Nút **Bắt đầu làm bài** hoặc **Tiếp tục làm bài** nếu đã có lượt đang làm.

Khi bắt đầu, trình duyệt hỏi xác nhận và thử yêu cầu chế độ toàn màn hình. Fullscreen có thể không được trình duyệt cho phép; người dùng vẫn có thể tiếp tục nếu trình duyệt từ chối yêu cầu đó.

### 3.2. Đồng hồ và lượt thi

Khi học sinh bắt đầu, server tạo bản ghi `exam_attempts` với trạng thái `in_progress`. Thời lượng được chụp vào `duration_minutes_snapshot`; cấu hình giám sát và hai cờ tráo câu hỏi/đáp án cũng được chụp vào các trường snapshot tương ứng.

Mỗi lượt thi có `shuffle_seed` riêng. Khi bật tráo, server xếp câu hỏi và đáp án theo seed này; cùng một lượt luôn thấy cùng thứ tự khi tải lại, còn lượt mới có thể có thứ tự khác. Deadline được tính từ `start_time` của lượt thi trong database cộng với thời lượng đã chụp. Vì vậy tải lại trang không đặt lại đồng hồ. Khi thời gian còn 60 giây, giao diện đổi màu cảnh báo. Nếu hết giờ, server hoàn tất và chấm lượt thi với lý do `time_expired`.

### 3.3. Autosave và khôi phục đáp án

Giao diện gửi đáp án lên route `autosave_answers`:

- Sau khi học sinh thay đổi lựa chọn, autosave được debounce khoảng 500 ms.
- Có thêm chu kỳ autosave định kỳ mỗi 5 giây.
- Server kiểm tra session, CSRF token, lượt thi thuộc đúng học sinh và vẫn đang làm.
- Chỉ answer ID thuộc câu hỏi của đề được lưu; dữ liệu từ trình duyệt không được tin cậy nguyên trạng.
- Mở lại lượt đang làm sẽ tải lựa chọn đã lưu và đánh dấu lại trên form.

Nếu autosave trả về đã hết giờ, server tự hoàn tất lượt. Khi người dùng bấm nộp, giao diện cố lưu lần cuối rồi gửi form; nếu autosave lỗi mạng, form vẫn có thể gửi dữ liệu đang hiển thị để server xử lý.

### 3.4. Sau khi nộp

Trang kết quả hiển thị điểm và giải thích lý do kết thúc:

- `manual`: học sinh chủ động nộp.
- `time_expired`: hết giờ.
- `violation_limit`: chạm giới hạn vi phạm và bị tự nộp.

Nếu lượt bị tự nộp do vi phạm, trạng thái xem xét ban đầu là `pending` và trang kết quả báo học sinh đang chờ giáo viên xem xét.

---

## 4. Ý 2 - Giám sát vi phạm

### 4.1. Sự kiện được ghi nhận

Khi chống gian lận được bật, trình duyệt theo dõi các sự kiện:

- `visibilitychange`: tab bị ẩn hoặc trình duyệt bị thu nhỏ.
- `blur`: cửa sổ trình duyệt mất focus.
- Mở menu chuột phải.
- Copy, cut, paste.
- Một số phím tắt như F12, Ctrl/Cmd+C/X/V và Ctrl/Cmd+Shift+I/J/C.

Một số thao tác bị chặn ở phía client đồng thời gửi log đến `log_violation`. Server chỉ nhận log nếu attempt thuộc học sinh đang đăng nhập và vẫn `in_progress`; mỗi log có loại sự kiện, chi tiết và thời gian ghi nhận. Sự kiện dồn trong khoảng debounce được hạn chế để tránh đếm lặp quá nhanh.

### 4.2. Cảnh báo và hành động

Giao diện hiển thị số vi phạm hiện tại theo dạng `Vi phạm n/giới hạn` và cảnh báo như `Cảnh báo 1/3`.

Giáo viên chọn một trong hai hành động:

- **Tự động nộp bài:** khi số vi phạm chạm giới hạn, server chốt lượt, chấm theo đáp án đã autosave và đặt `submit_reason = violation_limit`.
- **Chỉ ghi log:** tiếp tục cho học sinh làm bài nhưng vẫn ghi nhận vi phạm.

Lượt thi dùng snapshot cấu hình tại thời điểm bắt đầu, vì thế thay đổi cài đặt của giáo viên chỉ áp dụng cho lượt mới.

### 4.3. Giới hạn kỹ thuật và cách giải thích

Trang web không thể vô hiệu hóa `Alt+Tab` hoặc các phím ở cấp hệ điều hành. Sự kiện `blur` cũng có thể xảy ra khi người dùng bấm ra ngoài cửa sổ vì lý do bình thường. Các biện pháp chặn copy/context menu/phím tắt chỉ ở phía trình duyệt, nên không thể cam kết chống gian lận tuyệt đối.

Khi trình bày, em phân biệt rõ **phát hiện và lưu dấu hiệu** với **ngăn chặn gian lận**. Hệ thống có thể hỗ trợ giáo viên điều tra và áp dụng quy chế, nhưng log trình duyệt không tự chứng minh chắc chắn học sinh gian lận.

---

## 5. Ý 3 - Công cụ dành cho giáo viên

### 5.1. Cấu hình đề

Trong trang thống kê của đề do giáo viên sở hữu, giáo viên có thể:

- Đổi thời lượng làm bài, giới hạn trong khoảng 1-360 phút.
- Bật hoặc tắt giám sát.
- Bật hoặc tắt tráo thứ tự câu hỏi.
- Bật hoặc tắt tráo thứ tự đáp án.
- Chọn ngưỡng vi phạm từ 1-20 lần.
- Chọn tự nộp hoặc chỉ ghi log.

Mỗi lần cập nhật được kiểm tra quyền sở hữu đề và CSRF token ở server.

### 5.2. Theo dõi trực tiếp

Trang **Giám sát** lấy dữ liệu ban đầu từ server rồi polling khoảng 5 giây một lần. Bảng hiển thị:

- Tên học sinh.
- Đang làm, đã nộp, bị thu bài do vi phạm hoặc đã hủy kết quả.
- Số log vi phạm của lượt.
- Lý do nộp, thời gian bắt đầu và kết thúc.
- Liên kết mở chi tiết lượt thi.

Đề phải thuộc giáo viên đang đăng nhập mới xem được trang monitor và API polling.

### 5.3. Log và phúc khảo

Trang chi tiết lượt thi hiển thị log theo thời gian, loại vi phạm và nội dung chi tiết. Với lượt tự nộp do vi phạm đang `pending`, giáo viên có thể:

- **Hủy kết quả:** đổi trạng thái lượt sang `voided`.
- **Cho phép làm lại:** đánh dấu `retake_allowed`, giữ lại ghi chú và thông tin người duyệt.

Quyết định phúc khảo chỉ được thực hiện bởi giáo viên sở hữu đề và chỉ áp dụng cho lượt đang chờ xem xét.

Lượt đã chuyển sang `voided` vẫn được giữ trong bảng điểm với nhãn **“Vi phạm - đã hủy kết quả”** và bộ lọc Vi phạm. Tuy nhiên, lượt đã hủy không được tính vào điểm trung bình, cao/thấp nhất, tỷ lệ đạt, biểu đồ đúng/sai hoặc phổ điểm.

---

## 6. Các file và bảng dữ liệu liên quan

### File chính

- `controllers/ExamController.php`: chuẩn bị, bắt đầu, tải đề, autosave, nộp/chấm, log, cấu hình, polling và phúc khảo.
- `index.php`: route các thao tác thi và giám sát.
- `views/exams/prepare.php`: thông tin/quy chế, lịch sử lượt thi, bắt đầu hoặc tiếp tục.
- `views/exams/take.php`: câu hỏi, countdown, autosave và giám sát trình duyệt.
- `views/exams/result.php`: điểm và lý do nộp.
- `views/exams/statistics.php`: cấu hình và báo cáo dành cho giáo viên.
- `views/exams/monitoring.php`: dashboard polling trạng thái lượt thi.
- `views/exams/attempt-detail.php`: lựa chọn, đáp án, log và phúc khảo.

### Bảng dữ liệu

- `exams`: thời lượng, bật/tắt giám sát, ngưỡng/hành động vi phạm và cờ shuffle câu hỏi/đáp án.
- `exam_attempts`: trạng thái, điểm, lý do nộp, kết quả phúc khảo, snapshot cấu hình shuffle và `shuffle_seed`.
- `attempt_answers`: lựa chọn của học sinh và trạng thái đúng/sai.
- `attempt_correct_answers`: đáp án đúng được chụp tại thời điểm bắt đầu lượt.
- `violation_logs`: loại sự kiện, chi tiết và thời điểm ghi nhận.
- `exam_questions`: danh sách câu hỏi và trọng số điểm.

---

## 7. Em dùng AI và vibe coding như thế nào?

Em dùng Agent/Copilot để tìm các điểm nối giữa route, controller, view và bảng dữ liệu; chia yêu cầu thành các lát nhỏ như countdown, autosave, log vi phạm, monitor và phúc khảo; sau đó nhờ AI đề xuất code hoặc rà soát trường hợp biên.

Quy trình em dùng:

1. Mô tả luồng người dùng và tiêu chí hoàn thành trước khi sửa.
2. Tìm route đang chạy và controller trực tiếp xử lý nghiệp vụ.
3. Sửa một lát chức năng, đọc lại diff thay vì nhận nguyên văn code sinh ra.
4. Chạy PHP lint, kiểm tra JavaScript và thử request qua ứng dụng.
5. Kiểm tra database để chắc autosave/log/trạng thái thực sự được ghi.
6. Commit từng phần với message mô tả mục đích.

AI giúp tăng tốc tìm và viết code, nhưng em vẫn phải giải thích được vì sao server giữ quyền chốt điểm, vì sao cấu hình cần snapshot và vì sao tín hiệu trình duyệt có thể báo nhầm.

Trong module này em không sử dụng Graphify. Một graph/code map có thể hữu ích ở lần sau để xem quan hệ giữa action trong `index.php`, method trong `ExamController`, view và các bảng liên quan.

---

## 8. Em kiểm soát code và đánh giá tiến độ như thế nào?

Em dùng các công cụ sau:

- `git diff` để xem chính xác file/hunk nào thay đổi.
- `git diff --check` để tìm whitespace lỗi.
- `git status` để biết file nào chưa stage/commit.
- `git log --oneline --graph` để theo dõi commit trên nhánh.
- `php -l <file>` và Problems trong VS Code để phát hiện lỗi cú pháp.
- Network/Console trong Browser DevTools để kiểm tra fetch autosave, log vi phạm và polling.
- Query database để xác minh attempt, đáp án, log và trạng thái nộp.

Với vai trò trưởng nhóm, em không đánh giá hiệu quả chỉ bằng số dòng code. Em xem:

- Commit có nhỏ, thường xuyên và nêu rõ mục đích không.
- Mỗi tính năng có đường đi rõ từ UI đến controller và database không.
- Người làm giải thích được luồng và trường hợp lỗi không.
- Có kiểm tra quyền, validate input và kiểm thử kết quả không.
- Thay đổi có ảnh hưởng phần người khác không, và diff có tập trung không.

---

## 9. Kiểm thử chức năng

Các trường hợp nên trình diễn/kiểm tra:

| Trường hợp | Kết quả cần thấy |
|---|---|
| Bắt đầu đề hợp lệ | Tạo attempt `in_progress`, lưu snapshot thời lượng/chính sách và đáp án đúng. |
| Đổi đáp án | Autosave trả thành công; tải lại lượt thi vẫn thấy lựa chọn cũ. |
| Hết giờ | Server hoàn tất lượt, lưu `time_expired`, chấm dữ liệu đã lưu. |
| Tab ẩn hoặc cửa sổ mất focus | Có cảnh báo và một bản ghi trong `violation_logs`. |
| Chạm ngưỡng, chọn auto submit | Lượt hoàn tất với `violation_limit` và vào hàng chờ xem xét. |
| Chạm ngưỡng, chọn log only | Vi phạm được ghi nhưng attempt vẫn `in_progress`. |
| Học sinh thử mở dữ liệu attempt của người khác | Server từ chối do kiểm tra student_id/attempt_id. |
| Giáo viên không sở hữu đề gọi monitor/review | Server từ chối do kiểm tra role và teacher_id. |
| Giáo viên phúc khảo lượt pending | Lưu `voided` hoặc `retake_allowed`, người duyệt, thời điểm và ghi chú. |
| Cùng attempt tải lại đề đã tráo | Cùng seed nên câu hỏi và đáp án giữ nguyên thứ tự. |
| Bắt đầu lượt mới khi shuffle bật | Seed mới có thể tạo thứ tự câu hỏi/đáp án khác. |
| Tắt riêng một cờ shuffle | Thứ tự tương ứng giữ theo thứ tự gốc; cờ còn lại vẫn hoạt động độc lập. |
| Giáo viên hủy lượt do vi phạm | Học sinh vẫn hiện trong bảng điểm với nhãn vi phạm; điểm bị loại khỏi các phép tổng hợp. |

Các test integration đã dùng tài khoản và đề tạm để đi qua settings, start, autosave, ngưỡng vi phạm, monitoring và review; dữ liệu tạm được dọn sau test. Khi trình diễn tại máy mới, cần có MariaDB chạy và schema đúng với `db.sql` hiện tại.

Các commit bổ sung gần đây trên nhánh `Dung`:

- `1cafc35` - Lưu cấu hình tráo câu hỏi theo lượt thi.
- `d67a67b` - Tráo câu hỏi và đáp án ổn định theo lượt thi.
- `a38cdcf` - Thêm cấu hình tráo câu hỏi và đáp án.
- `f9b389d` - Giữ lượt bị hủy do vi phạm trong bảng điểm.

---

## 10. Điều em rút ra và hướng phát triển

Qua module này em hiểu đồng hồ phía client không đủ để bảo vệ thời gian thi; server phải kiểm tra deadline khi autosave/nộp. Autosave giảm nguy cơ mất bài nhưng cũng cần kiểm tra chủ sở hữu và danh sách answer ID ở backend. Snapshot cấu hình/đáp án giúp một lượt thi không bị đổi quy tắc giữa chừng.

Hướng phát triển tiếp:

- Thêm migration versioned để database cũ cập nhật đồng nhất, không phải tự chạy SQL thủ công.
- Thêm test tự động cho autosave mất mạng, race giữa hết giờ và nộp, cùng các quyền truy cập.
- Có audit rõ thao tác phúc khảo, thay đổi cấu hình và điểm.
- Cấu hình chính sách fullscreen/blur theo loại kỳ thi, tránh coi mọi `blur` là bằng chứng vi phạm.
- Tích hợp monitor với lớp học và danh sách sinh viên được giao đề.
- Đánh giá khả năng truy cập và trải nghiệm trên điện thoại khi fullscreen/autosave.

---

## 11. Kết luận

Phần Thành viên 3 nối trải nghiệm làm bài của học sinh với công cụ giám sát và xem xét của giáo viên. Mỗi lượt có thể tráo câu hỏi/đáp án theo seed ổn định, autosave và deadline dựa trên database; log vi phạm đi qua xác thực quyền; lý do nộp và dữ liệu phúc khảo được lưu để giáo viên có thể giải thích quyết định. Lượt bị hủy do vi phạm vẫn hiện rõ trên bảng điểm nhưng không làm sai các chỉ số điểm. Các sự kiện trình duyệt chỉ là tín hiệu hỗ trợ, không thể khẳng định gian lận tuyệt đối.