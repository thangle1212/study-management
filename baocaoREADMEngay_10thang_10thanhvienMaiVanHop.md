# Module chấm điểm tự động và báo cáo thống kê

## 1. Module này giải quyết vấn đề gì?

Trong một hệ thống thi trực tuyến, việc lưu lại đáp án thôi là chưa đủ. Sau khi học sinh nộp bài, hệ thống phải trả lời được ba câu hỏi:

1. Học sinh được bao nhiêu điểm và vì sao?
2. Giáo viên nhìn vào kết quả đó thì biết được điều gì về chất lượng bài kiểm tra và mức độ hiểu bài của học sinh?
3. Học sinh xem lại bài của mình như thế nào để biết chỗ nào đúng, chỗ nào sai và lần sau cần cải thiện gì?

Từ ba câu hỏi đó, nhóm em xây dựng module gồm hai phần:

- Chấm điểm tự động ở phía server.
- Báo cáo thống kê cho giáo viên và học sinh.

Điều nhóm em hướng tới không phải chỉ là làm cho một con số điểm xuất hiện trên màn hình. Quan trọng hơn là con số đó phải có căn cứ, có thể giải thích được và giúp người dùng học hoặc dạy tốt hơn.

---

## 2. Cách em phân tích hệ thống

Trước khi viết hoặc sửa code, em đi theo luồng sử dụng thực tế:

```text
Học sinh bắt đầu bài
        ↓
Chọn đáp án và lưu bài
        ↓
Nộp bài / hết giờ / bị tự động nộp
        ↓
Server chấm điểm
        ↓
Lưu kết quả
        ↓
Giáo viên xem báo cáo
        ↓
Học sinh xem lại lịch sử và câu đúng sai
```

Sau đó em đối chiếu từng bước với ba lớp của hệ thống:

- **Giao diện:** người dùng nhìn thấy và thao tác gì?
- **Controller:** hệ thống xử lý nghiệp vụ như thế nào?
- **Database:** dữ liệu nào được lưu và dùng lại ra sao?

Em không chỉ kiểm tra trường hợp làm bài bình thường, mà còn đặt ra các câu hỏi:

- Nếu học sinh chọn nhiều đáp án thì chấm thế nào?
- Nếu học sinh không trả lời một câu thì sao?
- Nếu bài tự nộp do hết giờ thì dữ liệu có bị thiếu không?
- Nếu học sinh làm cùng một đề nhiều lần thì thống kê có bị đếm nhầm không?
- Nếu giáo viên sửa đáp án trong lúc học sinh đang làm thì kết quả có bị thay đổi không?
- Nếu một câu hỏi bị thêm hai lần vào đề thì điểm có bị cộng hai lần không?

Nhờ cách phân tích này, em hiểu rằng một module chấm điểm không chỉ nằm ở đoạn code cộng điểm. Nó liên quan đến toàn bộ quá trình từ lúc bắt đầu lượt thi đến khi người dùng đọc báo cáo.

---

## 3. Ý 1 - Chấm điểm tự động

### 3.1. Cách xác định một câu trả lời đúng

Module hiện tại hỗ trợ hai loại câu hỏi:

- Câu hỏi một đáp án.
- Câu hỏi nhiều đáp án.

Quy tắc chấm là:

- Câu một đáp án phải chọn đúng đáp án duy nhất.
- Câu nhiều đáp án phải chọn đủ tất cả đáp án đúng và không chọn thừa đáp án sai.
- Bỏ trống, chọn thiếu hoặc chọn thừa đều không được tính điểm.

Ví dụ câu hỏi có hai đáp án đúng là A và C:

| Lựa chọn của học sinh | Kết quả |
|---|---|
| A, C | Đúng |
| A | Sai |
| C | Sai |
| A, B, C | Sai |
| Không chọn | Sai |

Sau khi xác định câu đúng, hệ thống cộng `score_weight` của câu đó vào tổng điểm.

### 3.2. Vì sao phải chấm ở server?

Em chọn chấm điểm ở server thay vì tin vào kết quả từ trình duyệt vì trình duyệt là nơi người dùng có thể can thiệp. Server mới là nơi kiểm tra lại:

- Lượt thi có đúng là của học sinh đó không.
- Lượt thi còn đang được làm hay đã nộp rồi.
- Đáp án gửi lên có thuộc đúng đề thi không.
- Đáp án đúng thực sự trong database là gì.

Nhờ vậy, điểm số được tạo ra từ dữ liệu mà server kiểm soát, không phải từ một giá trị do trình duyệt tự gửi lên.

### 3.3. Những trường hợp đặc biệt em đã xử lý

Trong quá trình hoàn thiện module, em đã bổ sung các kiểm tra:

- Không cho bắt đầu đề có câu điền khuyết hoặc tự luận khi hệ thống chưa có cách chấm tự động tương ứng.
- Câu một đáp án phải có đúng một đáp án đúng.
- Không cho cùng một câu xuất hiện lặp trong một đề làm điểm bị cộng nhiều lần.
- Khi hết giờ hoặc vượt giới hạn vi phạm, các câu chưa trả lời vẫn được lưu là chưa đúng để báo cáo không bị thiếu.
- Đáp án đúng được chụp lại khi học sinh bắt đầu lượt thi. Nếu giáo viên sửa đáp án sau đó, lượt thi đang làm vẫn chấm theo đáp án tại thời điểm bắt đầu.

### 3.4. Các file chính

- [`controllers/ExamController.php`](./controllers/ExamController.php): xử lý lưu đáp án, nộp bài và chấm điểm.
- [`views/exams/take.php`](./views/exams/take.php): giao diện làm bài, tự lưu và nộp bài.
- [`views/exams/result.php`](./views/exams/result.php): hiển thị điểm sau khi nộp.
- [`views/exams/attempt-detail.php`](./views/exams/attempt-detail.php): hiển thị chi tiết từng câu.
- [`db.sql`](./db.sql): cấu trúc các bảng `exam_attempts`, `attempt_answers`, `attempt_correct_answers` và `exam_questions`.

---

## 4. Ý 2 - Báo cáo dành cho giáo viên

### 4.1. Từ bảng điểm đến thông tin có ích cho việc dạy học

Ban đầu, một bảng điểm chỉ cho biết ai được bao nhiêu điểm. Nhưng với giáo viên, như vậy vẫn chưa đủ. Giáo viên cần biết:

- Có bao nhiêu lượt thi đã diễn ra?
- Mặt bằng điểm của các lượt thi như thế nào?
- Câu hỏi nào có nhiều người sai?
- Có lượt thi nào bị vi phạm cần xem xét không?
- Nếu học sinh làm lại nhiều lần thì phổ điểm nên tính thế nào?

Vì vậy báo cáo giáo viên được chia thành nhiều lớp thông tin:

### 4.2. Các chỉ số tổng quan

Trang báo cáo có các KPI:

- Số lượt tham gia.
- Điểm trung bình.
- Điểm cao nhất và thấp nhất.
- Tỷ lệ đạt.
- Số lượt đang chờ xem xét vì vi phạm.

Tỷ lệ đạt được tính theo số câu đúng. Một lượt thi được xem là đạt nếu số câu đúng lớn hơn hoặc bằng một nửa tổng số câu trong đề.

Ví dụ:

- Đề có 10 câu: cần ít nhất 5 câu đúng.
- Đề có 25 câu: cần ít nhất 13 câu đúng.

### 4.3. Phân tích câu hỏi

Thay vì chỉ gom tất cả câu đúng và sai vào một biểu đồ chung, báo cáo còn hiển thị:

- Tổng số câu đúng và tỷ lệ đúng.
- Tổng số câu sai và tỷ lệ sai.
- Top những câu hỏi có tỷ lệ sai cao nhất.

Phần “Top câu hỏi có tỷ lệ sai cao nhất” giúp giáo viên nhận ra nội dung nào học sinh đang yếu. Đây là phần có giá trị giảng dạy hơn việc chỉ nhìn một con số đúng/sai tổng quát, vì nó gợi ý trực tiếp cho giáo viên nên chữa phần kiến thức nào trên lớp.

### 4.4. Phổ điểm và số lượt làm bài

Một học sinh có thể làm cùng một đề nhiều lần, vì vậy “số sinh viên” và “số lượt làm” không phải là một.

Báo cáo cho phép giáo viên chọn cách tính phổ điểm:

- **Lần đầu:** lấy điểm ở lần hoàn thành đầu tiên của mỗi sinh viên.
- **Lần cao nhất:** lấy điểm cao nhất của mỗi sinh viên.
- **Tất cả lượt:** tính toàn bộ các lượt đã hoàn thành.

Bảng danh sách vẫn giữ từng lượt để giáo viên tra cứu chi tiết, còn biểu đồ phổ điểm có lựa chọn riêng để tránh hiểu nhầm dữ liệu.

### 4.5. Tìm kiếm và lọc bảng điểm

Bảng danh sách nộp bài có:

- Tìm kiếm theo tên sinh viên.
- Lọc tất cả trạng thái.
- Lọc các lượt đã nộp bình thường.
- Lọc các lượt có vi phạm hoặc đang chờ xem xét.

Nhờ vậy, giáo viên không phải tìm thủ công trong một bảng dài khi cần xem riêng một sinh viên hoặc các bài cần xử lý.

### 4.6. File chính

- [`controllers/ExamController.php`](./controllers/ExamController.php), hàm `statistics()`.
- [`views/exams/statistics.php`](./views/exams/statistics.php).
- [`views/exams/monitoring.php`](./views/exams/monitoring.php).
- [`views/exams/attempt-detail.php`](./views/exams/attempt-detail.php).

---

## 5. Ý 3 - Báo cáo dành cho học sinh

### 5.1. Tách số đề và số lượt làm

Ở phía học sinh, em tách rõ hai khái niệm:

- **Số đề đã tham gia:** mỗi đề chỉ tính một lần.
- **Tổng lượt làm:** tính tất cả các lần đã hoàn thành, kể cả làm lại cùng một đề.

Ví dụ học sinh làm một đề 6 lần thì hệ thống hiển thị:

```text
Số đề đã tham gia: 1
Tổng lượt làm: 6
```

Cách hiển thị này tránh gây hiểu nhầm rằng học sinh đã làm 6 đề khác nhau.

### 5.2. Các chỉ số học sinh quan tâm

Trang thống kê cá nhân có:

- Điểm trung bình.
- Tỷ lệ chính xác.
- Thời gian trung bình hoàn thành một lượt.
- Tổng số câu đúng và sai.
- Số đề đã tham gia.
- Tổng số lượt làm.

Học sinh cũng có thể lọc theo từng đề. Khi chọn một đề cụ thể, biểu đồ và bảng lịch sử chỉ hiển thị dữ liệu của đề đó.

### 5.3. Biểu đồ tiến bộ

Các đề có thể có tổng điểm khác nhau, nên biểu đồ không chỉ dùng điểm thô. Điểm được quy đổi về phần trăm để so sánh dễ hơn:

```text
Tỷ lệ điểm = Điểm đạt được / Tổng điểm của đề × 100
```

Biểu đồ sử dụng đường ít cong hơn để tránh tạo cảm giác số liệu vượt quá giá trị thật. Nhãn phần trăm cũng được hiển thị ngay trên từng điểm dữ liệu, không bắt buộc học sinh phải rê chuột mới thấy điểm.

### 5.4. Lịch sử làm bài

Bảng lịch sử có các thông tin:

- Lần thi.
- Tên đề.
- Điểm.
- Số câu đúng.
- Số câu sai.
- Thời lượng làm bài.
- Thời gian hoàn thành.
- Trạng thái nộp bài.
- Link xem chi tiết.

Nếu bài bị tự động nộp do vi phạm, bảng hiển thị rõ:

```text
Vi phạm - Tự nộp
```

Nếu hết giờ, bảng hiển thị:

```text
Hết giờ
```

Học sinh có thể mở chi tiết để xem từng câu mình chọn, đáp án đúng và trạng thái đúng/sai.

---

## 6. Em dùng AI và vibe coding như thế nào?

Phần lớn module này được em thực hiện bằng **vibe coding** với sự hỗ trợ của AI. Em không code tay toàn bộ từ đầu. Tuy nhiên, em cũng không xem đoạn code AI tạo ra là kết quả cuối cùng.

Quy trình của em là:

1. Đọc code hiện tại và mô tả lại luồng cho AI.
2. Nêu yêu cầu bằng tình huống cụ thể, chẳng hạn:
   - “Nếu học sinh chọn thừa một đáp án thì phải bị tính sai.”
   - “Nếu một học sinh làm cùng đề 6 lần thì thống kê phải phân biệt số đề và số lượt.”
   - “Nếu hết giờ mà chưa lưu câu cuối thì báo cáo vẫn phải có câu đó.”
3. Nhờ AI gợi ý cách sửa hoặc tạo bản nháp code.
4. Đọc lại diff và kiểm tra code có đúng với nghiệp vụ không.
5. Chạy kiểm tra cú pháp.
6. Kiểm tra giao diện với dữ liệu thật hoặc dữ liệu mẫu.
7. Thử các trường hợp đặc biệt và sửa tiếp nếu phát hiện điểm chưa hợp lý.

AI hỗ trợ em nhiều ở:

- Tìm vị trí code liên quan.
- Gợi ý câu SQL.
- Viết phần giao diện.
- Phát hiện trường hợp biên.
- Rà soát lại thay đổi.

Nhưng những phần em phải hiểu và tự giải thích là:

- Vì sao phải chấm điểm ở server.
- Vì sao cần snapshot đáp án đúng.
- Vì sao phải phân biệt đề thi và lượt thi.
- Vì sao phổ điểm cần có nhiều cách tính.
- Vì sao phân tích câu hỏi sai nhiều có ích cho giáo viên.

Nói đơn giản, AI giúp em đi nhanh hơn trong quá trình viết code, còn việc xác định hệ thống cần làm gì và kiểm tra kết quả có đúng hay không vẫn là trách nhiệm của em.

---

## 7. Em kiểm soát chất lượng code như thế nào?

Để kiểm soát code, em thực hiện các việc sau:

- Kiểm tra quyền xem kết quả ở phía server.
- Dùng prepared statement cho truy vấn có dữ liệu đầu vào.
- Kiểm tra dữ liệu đáp án trước khi lưu.
- Kiểm tra trạng thái lượt thi trước khi chấm.
- Dùng transaction khi hoàn tất bài.
- Không để học sinh xem dữ liệu của người khác.
- Kiểm tra lỗi cú pháp bằng `php -l`.
- Kiểm tra Problems panel trong VS Code.
- Dùng `git diff --check`.
- Đọc lại diff sau mỗi lần sửa.
- Thử các trường hợp chọn đúng, chọn sai, bỏ trống, chọn nhiều đáp án, hết giờ, vi phạm và làm lại nhiều lần.

Các trường hợp em đặc biệt quan tâm là những trường hợp nhìn giao diện có vẻ bình thường nhưng có thể làm sai dữ liệu phía sau. Ví dụ:

- Một request nộp bài được gửi hai lần.
- Một câu hỏi bị lặp trong đề.
- Đáp án bị sửa trong lúc học sinh đang thi.
- Một lượt tự nộp nhưng chưa kịp lưu đáp án cuối.
- Một học sinh làm cùng đề nhiều lần khiến biểu đồ bị đếm nhầm.

---

## 8. Công cụ em đã sử dụng

- **VS Code:** đọc code, chỉnh sửa, xem Problems và quản lý workspace.
- **PHP CLI:** kiểm tra cú pháp PHP.
- **MariaDB/MySQL và phpMyAdmin:** xem bảng, dữ liệu và kiểm tra truy vấn.
- **Git:** xem diff và theo dõi thay đổi.
- **Browser DevTools:** kiểm tra request, response và lỗi JavaScript.
- **Chart.js:** tạo biểu đồ thống kê.
- **AI/Copilot SDK:** hỗ trợ phân tích code, gợi ý giải pháp, viết bản nháp và rà soát trường hợp thiếu.

---

## 9. Những gì em rút ra được

Qua module này, em nhận ra rằng viết một tính năng không chỉ là làm cho nó “chạy được”. Một tính năng tốt còn phải:

- Đúng với nghiệp vụ.
- Có thể giải thích được.
- Không làm sai dữ liệu ở các trường hợp đặc biệt.
- Có giao diện giúp người dùng hiểu thông tin.
- Có cách kiểm tra để phát hiện lỗi.

Trước đây em thường nghĩ báo cáo chỉ là đưa dữ liệu lên bảng hoặc biểu đồ. Sau khi làm module này, em hiểu rằng cách chọn số liệu và cách đặt tên cho số liệu cũng quan trọng không kém việc viết câu SQL. Nếu không phân biệt “số đề” với “số lượt”, hoặc không nói rõ phổ điểm tính theo lần đầu hay lần cao nhất, người xem có thể hiểu sai hoàn toàn kết quả.

---

## 10. Hướng phát triển tiếp theo

Module hiện tại đã đáp ứng việc chấm tự động cho câu hỏi một đáp án và nhiều đáp án. Những phần có thể phát triển thêm:

- Chấm tự động câu điền khuyết với quy tắc chuẩn hóa câu trả lời.
- Thêm quy trình chấm và phúc khảo câu tự luận.
- Xuất báo cáo CSV hoặc Excel.
- Cho giáo viên chọn cách tính KPI theo lượt hoặc theo sinh viên.
- Thêm bộ test tự động cho các trường hợp chấm điểm.
- Ghi lại lịch sử thay đổi đáp án và cấu hình đề.
- Cho phép giáo viên lưu hoặc xuất báo cáo phân tích câu hỏi để dùng khi chữa bài.



