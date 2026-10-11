# Module giao diện làm bài thi và giám sát vi phạm

## 1. Module này giải quyết vấn đề gì?

Trong bài thi trực tuyến, học sinh cần biết thời gian còn lại và đáp án đã được lưu chưa; giáo viên cần theo dõi lượt thi, xem dấu hiệu vi phạm và có cơ sở xem xét kết quả.

Phần Thành viên 3 gồm hai phía:

- **Học sinh:** xem quy chế, bắt đầu, làm bài, lưu tiến độ và nộp bài.
- **Giáo viên:** cấu hình giám sát, theo dõi trạng thái, xem log và phúc khảo.

Trạng thái lượt thi, thời hạn và kết quả được kiểm tra ở server, không chỉ dựa vào dữ liệu trình duyệt gửi lên.

## 2. Cách em phân tích luồng

```text
Giáo viên cấu hình đề
	↓
Học sinh xem quy chế và bắt đầu
	↓
Server tạo attempt, chụp cấu hình và đáp án đúng
	↓
Học sinh chọn đáp án, autosave, theo dõi thời gian
	↓
Nộp thủ công / hết giờ / chạm ngưỡng vi phạm
	↓
Server hoàn tất lượt và ghi lý do
	↓
Giáo viên xem log, thống kê và phúc khảo
```

Em đối chiếu giao diện, controller và database; đồng thời kiểm tra các trường hợp tải lại, mất mạng, nộp lặp, hết giờ giữa autosave, chuyển tab, đổi cấu hình giữa lượt và gửi answer ID không thuộc đề.

## 3. Ý 1 - Giao diện làm bài của học sinh

### 3.1. Trang chuẩn bị

Trang chuẩn bị hiển thị tên đề, số câu, thời lượng, trạng thái shuffle/giám sát, ngưỡng vi phạm, hành động khi vượt ngưỡng và lịch sử lượt trước. Học sinh chọn **Bắt đầu làm bài** hoặc **Tiếp tục làm bài**. Trình duyệt hỏi xác nhận và thử yêu cầu fullscreen; trình duyệt có thể từ chối quyền này.

### 3.2. Đồng hồ, lượt thi và tráo thứ tự

Khi bắt đầu, server tạo `exam_attempts` trạng thái `in_progress`, chụp thời lượng và chính sách vào các trường snapshot. Deadline tính từ `start_time` trong database nên tải lại trang không đặt lại giờ. Hết giờ, server hoàn tất lượt với lý do `time_expired`.

Đề có thể bật/tắt riêng tráo câu hỏi và đáp án. Mỗi lượt có `shuffle_seed`: cùng một lượt giữ nguyên thứ tự khi reload, còn lượt mới có thể nhận thứ tự khác.

### 3.3. Autosave và khôi phục

Đáp án được gửi đến route `autosave_answers`:

- Autosave debounce khoảng 500 ms sau khi đổi lựa chọn và chạy định kỳ mỗi 5 giây.
- Server kiểm tra session, CSRF token, chủ sở hữu attempt và trạng thái `in_progress`.
- Chỉ answer ID thuộc câu hỏi của đề được lưu.
- Khi mở lại lượt đang làm, lựa chọn đã lưu được khôi phục.

Khi bấm nộp, giao diện thử lưu lần cuối. Nếu autosave gặp lỗi mạng, form vẫn gửi lựa chọn hiện tại để server xử lý.

### 3.4. Sau khi nộp

Trang kết quả hiển thị điểm và lý do: `manual` (nộp thủ công), `time_expired` (hết giờ), `violation_limit` (tự nộp do vi phạm). Lượt bị tự nộp do vi phạm chuyển sang trạng thái `pending` để giáo viên xem xét.

## 4. Ý 2 - Giám sát vi phạm

### 4.1. Sự kiện ghi nhận

Khi bật giám sát, trình duyệt theo dõi `visibilitychange`, `blur`, menu chuột phải, copy/cut/paste và một số phím tắt như F12, Ctrl/Cmd+C/X/V, Ctrl/Cmd+Shift+I/J/C. Log gửi tới `log_violation`; server chỉ ghi nếu attempt thuộc học sinh đang đăng nhập và còn `in_progress`. Mỗi log gồm loại, chi tiết và thời điểm.

### 4.2. Cảnh báo và hành động

Giao diện hiển thị `Vi phạm n/giới hạn` và cảnh báo tương ứng. Giáo viên chọn:

- **Tự động nộp bài:** chạm ngưỡng thì server chấm và hoàn tất với `submit_reason = violation_limit`.
- **Chỉ ghi log:** tiếp tục cho làm bài nhưng vẫn ghi vi phạm.

Cấu hình được snapshot khi bắt đầu, nên thay đổi sau đó chỉ áp dụng cho lượt mới.

### 4.3. Giới hạn kỹ thuật

Web không thể vô hiệu hóa `Alt+Tab` hay phím cấp hệ điều hành. `blur` cũng có thể xảy ra vì lý do bình thường. Chặn copy/context menu/phím tắt chỉ là hạn chế phía trình duyệt, không chứng minh gian lận tuyệt đối. Log là dấu hiệu hỗ trợ giáo viên xem xét.

## 5. Ý 3 - Công cụ giáo viên

### 5.1. Cấu hình

Trong thống kê đề của mình, giáo viên có thể đổi thời lượng (1-360 phút), bật/tắt giám sát, bật/tắt tráo câu hỏi/đáp án, đặt ngưỡng (1-20) và chọn tự nộp hoặc chỉ ghi log. Server kiểm tra quyền sở hữu đề và CSRF token.

### 5.2. Giám sát trực tiếp

Trang **Giám sát** polling khoảng 5 giây/lần, hiển thị học sinh, trạng thái lượt, số vi phạm, lý do nộp, thời điểm bắt đầu/kết thúc và liên kết xem chi tiết. Giáo viên chỉ xem đề mình sở hữu.

### 5.3. Log và phúc khảo

Chi tiết lượt hiển thị thời điểm, loại và nội dung vi phạm. Với lượt `pending`, giáo viên có thể:

- **Hủy kết quả:** chuyển sang `voided`.
- **Cho phép làm lại:** đặt `retake_allowed`, lưu ghi chú và người duyệt.

Lượt `voided` vẫn xuất hiện trên bảng điểm với nhãn **“Vi phạm - đã hủy kết quả”** và bộ lọc Vi phạm, nhưng không tính vào KPI, tỷ lệ đạt, biểu đồ đúng/sai hoặc phổ điểm.

## 6. File và bảng dữ liệu

### File chính

- `controllers/ExamController.php`: bắt đầu, tải đề, autosave, nộp/chấm, log, cấu hình, polling và phúc khảo.
- `index.php`: route các thao tác thi.
- `views/exams/prepare.php`: quy chế, lịch sử và bắt đầu/tiếp tục.
- `views/exams/take.php`: câu hỏi, countdown, autosave, giám sát client.
- `views/exams/result.php`: điểm và lý do nộp.
- `views/exams/statistics.php`: cấu hình và bảng điểm.
- `views/exams/monitoring.php`: dashboard giám sát.
- `views/exams/attempt-detail.php`: đáp án, log và phúc khảo.

### Bảng dữ liệu

- `exams`: thời lượng, chính sách vi phạm và cờ shuffle.
- `exam_attempts`: trạng thái, điểm, lý do nộp, phúc khảo, snapshot và `shuffle_seed`.
- `attempt_answers`: đáp án học sinh và đúng/sai.
- `attempt_correct_answers`: đáp án đúng tại lúc bắt đầu.
- `violation_logs`: loại, chi tiết và thời gian vi phạm.
- `exam_questions`: câu hỏi và trọng số điểm.

## 7. Em dùng AI và vibe coding như thế nào?

Em dùng Agent/Copilot để tìm quan hệ route-controller-view-database, chia yêu cầu thành các phần như countdown, autosave, log, monitor và phúc khảo, rồi nhờ AI gợi ý code hoặc rà trường hợp biên.

Quy trình: mô tả luồng và tiêu chí; tìm controller đang chạy; sửa từng phần; đọc diff; chạy PHP lint/kiểm tra JavaScript; thử request và database; commit nhỏ với message rõ mục đích.

AI giúp tăng tốc, nhưng em cần giải thích được vì sao server chốt điểm, vì sao cần snapshot và vì sao log trình duyệt có thể báo nhầm. Em không sử dụng Graphify trong module này; graph/code map có thể hữu ích để hình dung liên hệ giữa route, method, view và bảng dữ liệu.

## 8. Kiểm soát code và đánh giá tiến độ

- `git diff`: xem chính xác hunk/file thay đổi.
- `git diff --check`: kiểm tra whitespace.
- `git status`: theo dõi file chưa stage/commit.
- `git log --oneline --graph`: xem lịch sử nhánh.
- `php -l` và Problems trong VS Code: phát hiện lỗi cú pháp.
- Browser DevTools: kiểm tra autosave, log và polling.
- Truy vấn database: xác minh attempt, đáp án, log và trạng thái.

Đánh giá hiệu quả không chỉ dựa vào số dòng code: xem commit có đều và rõ mục đích không, tính năng có đi hết UI-controller-database không, người làm có giải thích được luồng và trường hợp biên không, có kiểm tra quyền/đầu vào không, và diff có đúng phạm vi không.

## 9. Kiểm thử chức năng

| Trường hợp | Kết quả cần thấy |
|---|---|
| Bắt đầu đề | Tạo attempt `in_progress`, snapshot chính sách và đáp án đúng. |
| Đổi đáp án | Autosave thành công; reload vẫn khôi phục lựa chọn. |
| Hết giờ | Server hoàn tất với `time_expired`. |
| Ẩn tab/mất focus | Có cảnh báo và log trong `violation_logs`. |
| Chạm ngưỡng, auto submit | Hoàn tất với `violation_limit`, chờ xem xét. |
| Chạm ngưỡng, log only | Có log, attempt tiếp tục `in_progress`. |
| Mở attempt người khác | Server từ chối. |
| Giáo viên không sở hữu đề gọi monitor/review | Server từ chối. |
| Phúc khảo lượt pending | Lưu `voided` hoặc `retake_allowed`, người duyệt và ghi chú. |
| Reload attempt đã shuffle | Cùng seed giữ nguyên thứ tự câu/đáp án. |
| Lượt mới khi shuffle bật | Seed mới có thể tạo thứ tự khác. |
| Tắt riêng một cờ shuffle | Thứ tự tương ứng giữ nguyên; cờ kia vẫn hoạt động. |
| Hủy lượt vi phạm | Vẫn hiện trên bảng điểm; bị loại khỏi thống kê điểm. |

Đã chạy PHP lint và kiểm thử tích hợp với tài khoản/đề tạm cho settings, start, autosave, ngưỡng vi phạm, monitor và review; dữ liệu tạm đã được dọn. Thuật toán shuffle được kiểm tra để xác nhận cùng seed ổn định và các cờ hoạt động độc lập.

## 10. Hướng phát triển

- Thêm migration có version cho database cũ.
- Thêm test tự động cho mất mạng, race giữa hết giờ/nộp và quyền truy cập.
- Ghi audit cho phúc khảo, cấu hình và thay đổi điểm.
- Không xem mọi sự kiện `blur` là bằng chứng vi phạm.
- Tích hợp monitor với lớp và danh sách học sinh được giao đề.
- Kiểm tra trải nghiệm/khả năng truy cập trên điện thoại.

## 11. Kết luận

Phần Thành viên 3 kết nối giao diện làm bài với giám sát và xem xét của giáo viên. Mỗi lượt có trạng thái server, autosave, deadline và shuffle ổn định; log có kiểm tra quyền; lý do nộp và phúc khảo được lưu để giải thích quyết định. Lượt bị hủy vẫn hiện trên bảng điểm nhưng không ảnh hưởng chỉ số tổng hợp. Log trình duyệt chỉ là tín hiệu hỗ trợ, không chứng minh gian lận tuyệt đối.
