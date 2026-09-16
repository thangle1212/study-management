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
Vào Ngân hàng câu hỏi → Tạo ngân hàng câu hỏi.

Nhập:

Tên ngân hàng
Mô tả
Danh mục
Trạng thái
Cài đặt số lượng câu hỏi hoặc thời gian nếu có
Sau đó chọn Tạo ngân hàng câu hỏi.

Màn hình này nằm ở question-bank/create.php.

Bước 2: Thêm câu hỏi
Mở một ngân hàng câu hỏi, sau đó chọn Thêm câu hỏi.

Có thể tạo các loại câu hỏi:

Trắc nghiệm một đáp án
Trắc nghiệm nhiều đáp án
Điền khuyết
Tự luận
Người dùng nhập:

Nội dung câu hỏi
Đáp án
Đáp án đúng
Giải thích
Độ khó
Trạng thái
Các ô nội dung câu hỏi và đáp án sẽ tự động kéo dài theo văn bản. Chức năng này nằm ở question-create.php và question-edit.php.

Bước 3: Chỉnh sửa câu hỏi
Trong danh sách câu hỏi, chọn Sửa câu hỏi.

Màn hình question-edit.php cho phép cập nhật chi tiết câu hỏi, đáp án và đáp án đúng.

Bước 4: Tạo bài thi
Vào Bài thi → Tạo bài thi.

Nhập:

Tên bài thi
Ngân hàng câu hỏi
Thời gian làm bài
Số câu hỏi
Trạng thái
Ngày bắt đầu
Ngày kết thúc
Mô tả bài thi
Màn hình này nằm ở exam/create.php.

Bước 5: Thêm câu hỏi vào bài thi
Trong danh sách bài thi, chọn Câu hỏi.

Tại exam/questions.php, người dùng có thể:

Xem các câu hỏi trong bài thi
Thêm câu hỏi từ ngân hàng câu hỏi
Sắp xếp thứ tự câu hỏi
Xóa câu hỏi khỏi bài thi
Kiểm tra điểm của từng câu
Bước 6: Xem hoặc chỉnh sửa bài thi
Chọn Xem để xem thông tin tổng quan bài thi.
Chọn Sửa để thay đổi tên, thời gian, mô tả, trạng thái hoặc ngày mở bài thi.
Các màn hình tương ứng là exam/show.php và exam/edit.php.

4. Ý nghĩa các trạng thái
Nháp: bài thi hoặc câu hỏi đang được xây dựng, chưa sử dụng.
Đã công bố/Hoạt động: nội dung đã sẵn sàng để sử dụng.
Đã lên lịch: bài thi sẽ mở trong khoảng thời gian đã cài đặt.
Lưu trữ: không còn sử dụng nhưng vẫn giữ lại dữ liệu.