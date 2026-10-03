# Báo cáo ngày 04/10 - Thành viên 3

## Phạm vi phụ trách

Thành viên 3 phụ trách giao diện làm bài thi và giám sát các hành vi vi phạm trong lúc thi.

## Chức năng đã thực hiện

### Luồng sinh viên

- Trang chuẩn bị hiển thị tên đề, số câu hỏi, thời lượng và quy chế giám sát.
- Khi bắt đầu, hệ thống tạo lượt thi và lưu snapshot thời lượng cùng cấu hình chống gian lận.
- Đồng hồ đếm ngược dựa trên thời điểm bắt đầu ở database; tải lại trang không làm đặt lại giờ.
- Đáp án được tự lưu khi thay đổi và định kỳ mỗi 5 giây; đáp án đã lưu được khôi phục khi mở lại lượt thi.
- Hết giờ, hệ thống tự hoàn tất và chấm điểm lượt thi.
- Ghi nhận rời tab, cửa sổ mất focus, mở menu chuột phải, copy/cut/paste và một số phím tắt khi giám sát được bật.
- Khi số vi phạm chạm ngưỡng và cấu hình chọn tự nộp, server hoàn tất lượt thi và lưu lý do nộp.

### Luồng giáo viên

- Cấu hình thời lượng, bật/tắt giám sát, ngưỡng vi phạm và hành động khi vượt ngưỡng.
- Màn giám sát cập nhật trạng thái lượt thi và số vi phạm khoảng 5 giây một lần.
- Xem nhật ký vi phạm theo lượt thi, gồm loại vi phạm, chi tiết và thời điểm ghi nhận.
- Xem xét lượt bị tự nộp do vi phạm: hủy kết quả hoặc cho phép làm lại.

## Các file chính

- `controllers/ExamController.php`: bắt đầu lượt thi, autosave, chấm/nộp, ghi log, cấu hình và phúc khảo.
- `index.php`: đăng ký route cho các chức năng thi và giám sát.
- `views/exams/prepare.php`, `views/exams/take.php`: chuẩn bị và làm bài của sinh viên.
- `views/exams/statistics.php`, `views/exams/monitoring.php`: cấu hình và giám sát của giáo viên.
- `views/exams/attempt-detail.php`, `views/exams/result.php`: log, phúc khảo và kết quả.
- `db.sql`: cấu hình chống gian lận, snapshot theo lượt thi, lý do nộp và thông tin phúc khảo.

## Kiểm tra

Đã kiểm tra PHP lint và chạy kiểm thử tích hợp qua ứng dụng với dữ liệu tạm: cấu hình giáo viên, bắt đầu thi, autosave, tự nộp tại ngưỡng vi phạm, polling giám sát và xử lý phúc khảo. Dữ liệu kiểm thử tạm đã được xóa.

Trình duyệt không cho trang web vô hiệu hóa tổ hợp `Alt+Tab` ở cấp hệ điều hành. Hệ thống ghi nhận việc tab bị ẩn hoặc cửa sổ mất focus thay vì chặn tổ hợp này.

## Lịch sử commit

Các commit phần Thành viên 3 được push lên nhánh `Dung`:

- `5a8dba9` - Bổ sung cấu hình chống gian lận và phúc khảo.
- `e0855aa` - Lưu snapshot cấu hình theo lượt thi.
- `2eab5af` - Bổ sung API autosave và giám sát lượt thi.
- `5196df8` - Thêm trang chuẩn bị và giao diện thi sinh viên.
- `0569efb` - Thêm màn giám sát và phúc khảo cho giáo viên.
- `7973b13` - Sửa dữ liệu đề thi trong màn làm bài.

Thay đổi hiện được giữ trên nhánh `Dung`; chưa merge vào `main`.