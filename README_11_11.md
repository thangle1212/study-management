I. Mục tiêu và phạm vi thực hiện
Trong dự án study-management, em phụ trách phần quản lý ngân hàng câu hỏi, liên kết ngân hàng với chức năng tạo đề thi và xử lý lỗi đánh dấu đáp án đúng.
Mục tiêu là xây dựng chức năng phù hợp với hệ thống hiện có, đồng thời sử dụng AI Agent để hỗ trợ lập trình và áp dụng các công cụ để kiểm soát chất lượng code.
II. Ý 1 — Xây dựng hệ thống với sự trợ giúp của AI Agent (3 điểm)
Em thực hiện quy trình phát triển theo các bước:
1.	Phân tích: Tìm hiểu cấu trúc dự án, luồng xử lý và cơ sở dữ liệu liên quan.
2.	Xác định yêu cầu: Chia nhỏ công việc quản lý ngân hàng, liên kết tạo đề và sửa lỗi đáp án.
3.	Hỗ trợ lập trình: Sử dụng AI Agent để phân tích code, đề xuất giải pháp và hỗ trợ viết hoặc sửa code.
4.	Kiểm soát: Đọc lại code, đối chiếu yêu cầu và kiểm tra ảnh hưởng đến các chức năng liên quan.
5.	Hoàn thiện: Chạy thử, sửa lỗi và rà soát thay đổi trước khi commit.
Em chịu trách nhiệm về yêu cầu, quyết định áp dụng thay đổi và kiểm tra kết quả; AI Agent đóng vai trò hỗ trợ, không thay thế việc hiểu và làm chủ mã nguồn.
III. Ý 2a — Kiểm soát tính năng và làm chủ mã nguồn
Em phân tích luồng xử lý giữa các thành phần:
•	Router: Điều hướng yêu cầu đến chức năng tương ứng.
•	Controller: Tiếp nhận và xử lý yêu cầu.
•	Model: Thao tác với dữ liệu.
•	View: Hiển thị giao diện ngân hàng câu hỏi và tạo đề.
•	Database: Lưu ngân hàng, câu hỏi, đáp án và các liên kết liên quan.
IV. Ý 2b — Kỹ năng lập trình và công cụ hỗ trợ
Các kỹ năng và công cụ được vận dụng trong quá trình phát triển gồm:
. Phân tích cấu trúc và luồng chương trình
Em lần theo luồng từ router đến database. Ví dụ, khi giáo viên mở danh sách ngân hàng, index.php nhận action=qe_banks rồi gọi QuestionBankController::index(). Controller kiểm tra quyền và gọi model để lấy dữ liệu; sau đó dữ liệu được hiển thị ở giao diện danh sách.

Các điểm tham khảo: route qe_banks, QuestionBankController::index, QuestionBank::getBanksForOwner.

2. Làm backend CRUD
Em triển khai các thao tác tạo, xem, sửa, xóa ngân hàng và câu hỏi. Ví dụ, khi tạo câu hỏi, hệ thống ghi câu hỏi và các đáp án vào database; khi sửa câu hỏi, hệ thống cập nhật nội dung và đáp án.

Có thể chỉ vào QuestionBankController.php để trình bày các action, và QuestionBank.php để trình bày các thao tác lưu dữ liệu.

3. Kiểm tra dữ liệu, quyền và bảo vệ dữ liệu
Em kiểm tra dữ liệu người dùng gửi lên trước khi lưu. Ví dụ, nội dung câu hỏi không được để trống; câu hỏi trắc nghiệm phải có từ 2 đến 10 đáp án; URL hình ảnh phải hợp lệ; câu một đáp án phải chọn đúng một đáp án đúng. Các thao tác sửa/xóa cũng kiểm tra quyền giáo viên và CSRF.

Các ví dụ nằm ở validateQuestionInput và QuestionBankController::deleteQuestion.

Em cũng kiểm tra việc câu hỏi đã được dùng trong đề hay chưa. Câu thuộc đề đã công bố/lên lịch hoặc đã có lượt làm sẽ không được sửa; câu đã liên kết với đề sẽ không được xóa để tránh làm thay đổi đề và kết quả cũ.

Có thể chỉ vào QuestionBank::updateQuestion, QuestionBank::deleteQuestion và isQuestionLockedForEdit.

4. Debug và kiểm thử
Sau khi sửa, em dùng PHP CLI lint để kiểm tra cú pháp. Em cũng chạy smoke test trên database SQLite thử nghiệm để kiểm tra việc tạo, đọc, sửa, xóa ngân hàng/câu hỏi và xác nhận các câu đã dùng trong đề được bảo vệ. Với phần tạo đề, em kiểm tra thêm tình huống chọn đáp án đúng thủ công để bảo đảm lựa chọn không bị mất khi giao diện quét lại nội dung.

Nên trình bày đúng mức: đây là các kiểm thử đã chạy, không phải khẳng định đã kiểm thử mọi chức năng trên production.

5. Rà soát thay đổi bằng Git
Em dùng git status để xem file nào thay đổi, git diff để đọc nội dung thay đổi trước khi commit, và git log --oneline để kiểm tra lịch sử. Ví dụ, phần CRUD ngân hàng câu hỏi được ghi nhận trong commit 6163f64 với thông điệp “thêm CRUD phần ngân hàng câu hỏi”.Về skill chuyên biệt như Graphify, em chỉ ghi nhận nếu đã thực sự sử dụng trong dự án.
Có thể minh họa bằng các lệnh:
git status --short --branch
git diff
git diff --cached
git log --oneline -5
V. Ý 2c — Kiểm soát code bằng log, diff và kiểm thử
Em sử dụng các công cụ để theo dõi và đánh giá thay đổi:
Công cụ	Mục đích
git status	Kiểm tra trạng thái file
git diff	Xem nội dung code đã thay đổi
git diff --cached	Rà soát thay đổi chuẩn bị commit
git log --oneline	Theo dõi lịch sử commit
VIII. Kết luận
Qua dự án, em vận dụng kỹ năng phân tích hệ thống, lập trình backend, debug, kiểm thử và quản lý mã nguồn. Việc kết hợp AI Agent với Git diff, Git log và các phương pháp kiểm tra giúp em hiểu rõ hơn quá trình phát triển và kiểm soát code.

