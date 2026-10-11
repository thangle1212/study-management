1. Project này dùng để làm gì?
Đây là giao diện EduTest, dùng để quản lý:

Ngân hàng câu hỏi
Câu hỏi và đáp án
Bài thi
Danh sách câu hỏi trong từng bài thi
Thời gian, trạng thái và mô tả bài thi
Người sử dụng chính là giáo viên hoặc quản trị viên tạo nội dung kiểm tra.

2. Cách truy cập
Nếu chạy bằng XAMPP, mở:


Từ thanh menu bên trái có hai khu vực chính:

Ngân hàng câu hỏi
Bài thi
3. Quy trình sử dụng đề xuất
Bước 1: Tạo ngân hàng câu hỏi
Từ bảng điều khiển giáo viên, mở Ngân hàng câu hỏi → Tạo ngân hàng.

Nhập tên ngân hàng và môn học. Sau khi tạo, có thể mở ngân hàng để thêm câu hỏi hoặc sửa thông tin. Trang được điều hướng qua `index.php?action=qe_banks`; dữ liệu chỉ hiển thị cho chủ sở hữu.

Bước 2: Thêm câu hỏi
Mở một ngân hàng câu hỏi, sau đó chọn Thêm câu hỏi.

Hiện có thể tạo các loại câu hỏi được hỗ trợ bởi luồng làm bài:

Trắc nghiệm một đáp án
Trắc nghiệm nhiều đáp án
Nhập nội dung, từ 2 đến 10 đáp án, chọn đáp án đúng, độ khó và tùy chọn URL hình ảnh. Có thể thêm, sửa hoặc xóa câu hỏi từ trang chi tiết ngân hàng.

Bước 3: Chỉnh sửa câu hỏi
Trong trang chi tiết ngân hàng, chọn Sửa hoặc Xóa cạnh câu hỏi. Câu hỏi thuộc đề đã công bố/lên lịch hoặc đã có lượt làm sẽ không thể sửa; câu hỏi đã liên kết với bất kỳ đề thi nào không thể xóa và ngân hàng chứa câu hỏi đó cũng không thể xóa. Điều này giữ nguyên đề và kết quả cũ.

Bước 4: Tạo đề thi
Từ bảng điều khiển của giáo viên, chọn Tạo đề thi. Chọn một ngân hàng câu hỏi của bạn, nhập thông tin đề, rồi dán toàn bộ nội dung theo dạng:

1. Nội dung câu hỏi
A) Đáp án thứ nhất
B) Đáp án thứ hai

Các câu hỏi bắt đầu bằng số thứ tự (1., 2.); đáp án có nhãn A), B), C)... trên từng dòng. Có thể dán nội dung dạng văn bản thường hoặc giữ nguyên danh sách đánh số khi sao chép từ Word/Google Docs. Đáp án in đậm, in nghiêng, gạch chân hoặc tô màu sẽ được tự nhận diện là đáp án đúng. Nếu chưa được định dạng, chọn đáp án đúng trong phần xem trước sau khi quét. Nếu có nhiều đáp án đúng, chọn loại câu hỏi nhiều đáp án.

Định dạng chỉ dùng để nhận diện đáp án khi tạo đề và không hiển thị cho học sinh lúc làm bài.

Có thể chọn trạng thái Nháp, Đã công bố, Đã lên lịch hoặc Lưu trữ. Đề lên lịch cần có ngày giờ bắt đầu và kết thúc.
Màn hình được xử lý qua `index.php?action=qe_exam_create`. Nếu chưa có ngân hàng, tạo một ngân hàng ở `index.php?action=qe_bank_create` trước; biểu mẫu tạo đề sẽ khóa nút gửi cho đến khi có ngân hàng.

Bước 5: Quản lý câu hỏi đã tạo
Câu hỏi được quét từ nội dung đề sẽ được lưu vào ngân hàng đã chọn và liên kết với đề thi. Mở ngân hàng để xem, sửa hoặc xóa câu hỏi. Để giữ nguyên đề và kết quả đã làm, câu hỏi đã được dùng trong đề/lượt thi và ngân hàng chứa các câu hỏi đó sẽ được bảo vệ khỏi việc sửa/xóa.
Bước 6: Xem hoặc chỉnh sửa bài thi
Chọn Xem để xem thông tin tổng quan bài thi.
Chọn Sửa để thay đổi tên, thời gian, mô tả, trạng thái hoặc ngày mở bài thi.
Các màn hình tương ứng là exam/show.php và exam/edit.php.

4. Ý nghĩa các trạng thái
Nháp: bài thi hoặc câu hỏi đang được xây dựng, chưa sử dụng.
Đã công bố/Hoạt động: nội dung đã sẵn sàng để sử dụng.
Đã lên lịch: bài thi sẽ mở trong khoảng thời gian đã cài đặt.
Lưu trữ: không còn sử dụng nhưng vẫn giữ lại dữ liệu.
