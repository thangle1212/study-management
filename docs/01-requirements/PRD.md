# PRD — Hệ thống Quản lý Học tập và Thi trực tuyến

| Thuộc tính | Giá trị |
|---|---|
| Sản phẩm | EduTest — Study & Exam Management System |
| Phiên bản tài liệu | 1.0 |
| Ngày | 02/11/2026 |
| Trạng thái | Bản yêu cầu sản phẩm ban đầu |
| Đối tượng đọc | Product, BA, Engineering, QA, vận hành |

## 1. Tóm tắt sản phẩm

EduTest hỗ trợ quản trị viên quản lý người dùng và lớp học; giáo viên xây dựng ngân hàng câu hỏi, tổ chức bài thi và theo dõi kết quả; học sinh tham gia thi và xem kết quả của mình. Hệ thống cần bảo đảm quyền truy cập theo vai trò, tính toàn vẹn của đề thi và lượt thi, khả năng phục hồi khi mạng không ổn định, và kết quả chấm điểm nhất quán khi có nhiều lượt nộp đồng thời.

### 1.1 Bối cảnh hiện trạng đã xác minh

Những điểm dưới đây được quan sát trong mã nguồn và schema hiện tại; chúng không thay thế yêu cầu hoặc cam kết chất lượng của PRD:

- Có ba vai trò chính: `admin`, `teacher`, `student`; một tài khoản có trạng thái hoạt động/khóa.
- Có bảng lớp, giáo viên phụ trách lớp, quan hệ nhiều-nhiều giữa học sinh và lớp.
- Có ngân hàng câu hỏi thuộc giáo viên, câu hỏi, đáp án và liên kết câu hỏi với bài thi.
- Bài thi lưu thời lượng, khung thời gian, trạng thái, xáo trộn câu hỏi/đáp án và cấu hình chống gian lận.
- Lượt thi lưu trạng thái, thời điểm bắt đầu/kết thúc, ảnh chụp một số cấu hình thi, lý do nộp và trạng thái cần rà soát.
- Có lưu câu trả lời, đáp án đúng tại thời điểm bắt đầu thi, log vi phạm, lưu tự động, chấm điểm và cập nhật trạng thái lượt thi trong transaction.
- Có token CSRF cho một số thao tác POST và dùng câu truy vấn tham số hóa ở các luồng đã kiểm tra.
- Các điểm trên không khẳng định mọi route đều đã được kiểm thử, mọi quyền đã đầy đủ hoặc giao diện hiện cung cấp trọn vẹn tất cả khả năng.

## 2. Mục tiêu, chỉ số và ngoài phạm vi

### 2.1 Mục tiêu

1. Người dùng chỉ xem và thao tác dữ liệu phù hợp với vai trò và quyền sở hữu.
2. Giáo viên tạo được câu hỏi, đề thi, lịch thi và xác định đối tượng dự thi.
3. Học sinh làm bài thuận tiện trên máy tính/điện thoại; câu trả lời được bảo toàn khi mạng gián đoạn trong giới hạn đã công bố.
4. Thời gian thi và trạng thái nộp được xác định phía máy chủ, không phụ thuộc đồng hồ trình duyệt.
5. Mỗi lượt thi chỉ được chấm và hoàn tất một lần, kể cả khi có yêu cầu lặp hoặc đồng thời.
6. Giáo viên có thể giải thích, rà soát và xử lý các trường hợp cần xem xét.

### 2.2 Chỉ số thành công đề xuất

Các ngưỡng dưới đây là mục tiêu ban đầu để nghiệm thu, cần xác nhận bằng thử tải trên cấu hình triển khai thực tế:

| Chỉ số | Mục tiêu |
|---|---|
| Tính nhất quán lượt thi | 100% lượt thi hoàn tất có tối đa một kết quả cuối cùng; không nhân đôi điểm hoặc số lượt nộp |
| Toàn vẹn câu trả lời | Không mất câu trả lời đã được máy chủ xác nhận đã lưu |
| Phân quyền | 100% truy cập trái quyền trong kiểm thử được từ chối phía máy chủ |
| Ghi nhận kết quả tự động | 95% trang kết quả hiển thị trong vòng 3 giây sau khi nộp thành công ở tải định mức |
| Phục hồi mạng | Học sinh nhìn thấy trạng thái lưu gần nhất và được hướng dẫn tiếp tục/nộp lại sau khi kết nối phục hồi |
| Chất lượng đầu ra | 100% câu trả lời và điểm số mẫu khớp đáp án chuẩn trong bộ kiểm thử chấm điểm |

### 2.3 Ngoài phạm vi phiên bản đầu

- Giám sát bằng webcam, nhận diện khuôn mặt, sinh trắc học hoặc kết luận tự động rằng học sinh gian lận.
- Tích hợp hệ thống quản lý học tập bên ngoài, SSO hoặc sổ điểm bên ngoài.
- Cơ chế thu phí, chứng chỉ có chữ ký số hoặc kiểm duyệt nội dung bằng AI.
- Đảm bảo chống gian lận tuyệt đối trên thiết bị do người dự thi kiểm soát.

## 3. Người dùng và RBAC

### 3.1 Vai trò

| Vai trò | Mục tiêu | Quyền mặc định |
|---|---|---|
| Quản trị viên (`admin`) | Quản trị người dùng, lớp và vận hành | Quản lý tài khoản/trạng thái/vai trò; tạo, cập nhật lớp; phân công giáo viên; gán/bỏ gán học sinh; xem dữ liệu quản trị được cấp |
| Giáo viên (`teacher`) | Tạo nội dung và đánh giá kết quả | Quản lý ngân hàng/câu hỏi/đề do mình sở hữu; phân công bài thi theo quyền được cấp; xem báo cáo học sinh thuộc lớp/đề do mình phụ trách; rà soát lượt thi được đánh dấu |
| Học sinh (`student`) | Tham gia học và thi | Xem đề được phép dự thi; bắt đầu, lưu, nộp bài; xem kết quả và lịch sử của chính mình; sửa thông tin hồ sơ được cho phép |

### 3.2 Quy tắc phân quyền bắt buộc

- Mọi quyền phải được kiểm tra ở máy chủ cho từng thao tác đọc/ghi, không chỉ ẩn nút trên giao diện.
- Giáo viên chỉ thao tác với tài nguyên do mình sở hữu hoặc được phân công; không thể đổi ID trong request để truy cập tài nguyên của giáo viên khác.
- Học sinh chỉ xem/sửa lượt thi và dữ liệu cá nhân của chính mình.
- Quản trị viên có thể quản lý phân công lớp; thay đổi vai trò hoặc trạng thái không tự động xóa lịch sử học/thi.
- Quyền không rõ ràng phải mặc định từ chối và ghi nhận sự kiện phù hợp.
- Tài khoản bị khóa không được bắt đầu hoặc tiếp tục thao tác yêu cầu xác thực; chính sách cho lượt đang làm dở cần quy định rõ cho từng lần khóa.

## 4. Epics và Features

### Epic E1 — Danh tính, xác thực và RBAC

| Feature | Mô tả | Ưu tiên |
|---|---|---|
| F1.1 Đăng ký/đăng nhập/đăng xuất | Xác thực tài khoản, quản lý phiên và trạng thái hoạt động | P0 |
| F1.2 Phân quyền theo vai trò | Bảo vệ giao diện và API/hành động theo admin, teacher, student | P0 |
| F1.3 Quyền sở hữu tài nguyên | Ràng buộc truy vấn theo chủ sở hữu/lớp/học sinh | P0 |
| F1.4 Hồ sơ cá nhân | Cho phép cập nhật thông tin được phép; xác nhận mật khẩu hiện tại khi đổi mật khẩu | P1 |
| F1.5 Khôi phục mật khẩu | Token ngẫu nhiên, có hạn dùng, chỉ sử dụng một lần | P1 |
| F1.6 Ghi nhận hoạt động quản trị | Ghi ai thay đổi quyền, trạng thái, phân công lớp và thời điểm | P1 |

### Epic E2 — Quản lý lớp và phân công

| Feature | Mô tả | Ưu tiên |
|---|---|---|
| F2.1 Tạo/cập nhật lớp | Quản trị viên tạo mã lớp duy nhất và tên lớp | P0 |
| F2.2 Phân công giáo viên | Mỗi lớp có giáo viên phụ trách hợp lệ theo mô hình hiện tại | P0 |
| F2.3 Gán/bỏ gán học sinh | Học sinh có thể thuộc nhiều lớp; thao tác không xóa lịch sử thi | P0 |
| F2.4 Tra cứu danh sách lớp | Hiển thị giáo viên, số lượng thành viên và trạng thái tài khoản liên quan | P1 |
| F2.5 Phân công bài thi theo lớp | Giáo viên/quản trị chọn lớp hợp lệ làm đối tượng dự thi | P0 |

### Epic E3 — Quản lý ngân hàng câu hỏi

| Feature | Mô tả | Ưu tiên |
|---|---|---|
| F3.1 Tạo ngân hàng | Tên, môn học và chủ sở hữu giáo viên | P0 |
| F3.2 Tạo/sửa/xóa câu hỏi | Nội dung, hình ảnh tham chiếu, loại câu hỏi, độ khó, đáp án và đáp án đúng | P0 |
| F3.3 Hỗ trợ loại câu hỏi | Trắc nghiệm một đáp án và nhiều đáp án; mở rộng loại khác theo roadmap | P0/P2 |
| F3.4 Tìm kiếm/lọc câu hỏi | Theo ngân hàng, môn, độ khó, loại và từ khóa | P1 |
| F3.5 Tính toàn vẹn câu hỏi đã sử dụng | Bảo vệ câu hỏi đã gắn với đề/lượt thi; lịch sử phải dùng snapshot hoặc quy tắc bất biến đã định nghĩa | P0 |
| F3.6 Nhập câu hỏi theo lô | Nhập văn bản có cấu trúc, xem trước và xác nhận trước khi lưu | P1 |

### Epic E4 — Tạo đề thi và lập lịch

| Feature | Mô tả | Ưu tiên |
|---|---|---|
| F4.1 Tạo đề từ ngân hàng | Chọn câu hỏi, điểm/trọng số và thứ tự câu | P0 |
| F4.2 Thiết lập đề | Tên, thời lượng, thời gian mở/đóng, trạng thái nháp/công bố/lên lịch/lưu trữ | P0 |
| F4.3 Đối tượng dự thi | Gán đề cho lớp hoặc cho nhóm học sinh được phép | P0 |
| F4.4 Xáo trộn | Xáo trộn câu/đáp án theo cấu hình, cố định thứ tự cho mỗi lượt thi | P1 |
| F4.5 Chống gian lận có cấu hình | Bật/tắt, giới hạn vi phạm, hành động khi đạt ngưỡng; thông báo chính sách trước khi thi | P1 |
| F4.6 Sửa đề đã phát hành | Quy định cấm sửa cấu hình ảnh hưởng lượt thi đang chạy; cấu hình mới chỉ áp dụng cho lượt mới | P0 |

### Epic E5 — Làm bài thi và khả năng phục hồi

| Feature | Mô tả | Ưu tiên |
|---|---|---|
| F5.1 Kiểm tra điều kiện dự thi | Xác minh trạng thái đề, thời gian, đối tượng, tài khoản và lượt thi trước khi bắt đầu | P0 |
| F5.2 Bắt đầu lượt thi | Tạo lượt thi duy nhất và chụp cấu hình cần thiết | P0 |
| F5.3 Đồng hồ phía máy chủ | Máy chủ là nguồn chuẩn cho thời lượng và thời điểm đóng bài | P0 |
| F5.4 Lưu tự động | Gửi câu trả lời định kỳ và/hoặc khi thay đổi; chỉ báo “đã lưu” sau xác nhận từ máy chủ | P0 |
| F5.5 Nộp thủ công/tự động | Nộp chủ động, hết giờ hoặc theo hành động vi phạm được cấu hình | P0 |
| F5.6 Tiếp tục sau mất mạng | Tải lại trạng thái đã lưu và đồng bộ phần chưa gửi theo quy tắc minh bạch | P1 |
| F5.7 Nhật ký vi phạm | Ghi sự kiện hợp lệ gắn với lượt thi; không coi sự kiện phía trình duyệt là bằng chứng tuyệt đối | P1 |

### Epic E6 — Chấm điểm, kết quả và rà soát

| Feature | Mô tả | Ưu tiên |
|---|---|---|
| F6.1 Chấm trắc nghiệm tự động | Đối chiếu tập đáp án học sinh với đáp án đúng đã được đóng băng cho lượt thi | P0 |
| F6.2 Tổng hợp điểm | Tính theo trọng số/quy tắc công bố trước; làm tròn nhất quán | P0 |
| F6.3 Kết quả học sinh | Hiển thị điểm, trạng thái nộp và phản hồi theo cài đặt của giáo viên | P0 |
| F6.4 Báo cáo giáo viên | Thống kê theo đề/lớp/học sinh; quyền xem bị giới hạn theo phân công | P1 |
| F6.5 Rà soát vi phạm | Đánh dấu cần rà soát và cho phép giáo viên ghi chú/quyết định theo chính sách | P1 |
| F6.6 Lịch sử không bị sửa ngược | Giữ điểm, đáp án, cấu hình đề và sự kiện phục vụ kiểm tra sau kỳ thi | P0 |

## 5. User Stories và tiêu chí Gherkin

### E1 — Danh tính và phân quyền

#### US-1.1 Đăng nhập đúng vai trò

**Là** người dùng, **tôi muốn** đăng nhập bằng tài khoản của mình, **để** truy cập đúng không gian được cấp quyền.

```gherkin
Feature: Đăng nhập và phân quyền

  Scenario: Đăng nhập thành công
    Given tài khoản tồn tại và đang hoạt động
    When người dùng gửi email và mật khẩu hợp lệ
    Then hệ thống tạo phiên đăng nhập mới
    And chuyển người dùng đến giao diện phù hợp với vai trò hiện tại

  Scenario: Tài khoản bị khóa
    Given tài khoản đang ở trạng thái bị khóa
    When người dùng gửi thông tin đăng nhập đúng
    Then hệ thống từ chối tạo phiên sử dụng
    And hiển thị thông báo có thể hiểu được mà không tiết lộ dữ liệu nhạy cảm

  Scenario: Học sinh gọi thao tác dành riêng cho giáo viên
    Given người dùng đã đăng nhập với vai trò student
    When người dùng gửi trực tiếp request tạo ngân hàng câu hỏi
    Then máy chủ trả về trạng thái từ chối phù hợp
    And không tạo hoặc thay đổi dữ liệu
```

#### US-1.2 Bảo vệ tài nguyên theo quyền sở hữu

**Là** người dùng, **tôi muốn** chỉ truy cập dữ liệu được phép, **để** thông tin của lớp và người khác được bảo vệ.

```gherkin
Feature: Phân quyền và quyền sở hữu

  Scenario: Giáo viên xem tài nguyên của mình
    Given giáo viên A sở hữu một ngân hàng câu hỏi
    When giáo viên A mở ngân hàng đó
    Then hệ thống hiển thị nội dung ngân hàng

  Scenario: Giáo viên sửa ID để truy cập tài nguyên người khác
    Given ngân hàng thuộc giáo viên B
    When giáo viên A gửi request sửa ngân hàng với ID của ngân hàng đó
    Then hệ thống từ chối request
    And dữ liệu của giáo viên B không thay đổi

  Scenario: Học sinh yêu cầu lượt thi của học sinh khác
    Given lượt thi thuộc học sinh B
    When học sinh A yêu cầu xem hoặc sửa lượt thi đó
    Then hệ thống từ chối truy cập ở phía máy chủ
```

### E2 — Lớp học

#### US-2.1 Gán học sinh vào lớp

**Là** quản trị viên, **tôi muốn** gán học sinh đang hoạt động vào một lớp, **để** giáo viên có thể phân công nội dung học và thi.

```gherkin
Feature: Quản lý lớp học

  Scenario: Gán học sinh hợp lệ
    Given quản trị viên đã đăng nhập
    And lớp tồn tại
    And học sinh đang hoạt động
    And học sinh chưa thuộc lớp đó
    When quản trị viên xác nhận gán học sinh
    Then quan hệ học sinh-lớp được tạo đúng một lần
    And hệ thống thông báo thành công

  Scenario: Gán học sinh đã có trong lớp
    Given học sinh đã thuộc lớp
    When quản trị viên gửi lại yêu cầu gán, kể cả bằng request lặp
    Then hệ thống không tạo quan hệ trùng
    And thông báo học sinh đã thuộc lớp

  Scenario: Bỏ gán học sinh
    Given học sinh thuộc lớp và có thể đã có lịch sử làm bài
    When quản trị viên bỏ học sinh khỏi lớp
    Then quan hệ lớp học bị gỡ
    And lịch sử lượt thi, câu trả lời và điểm số được giữ nguyên
```

### E3 — Ngân hàng câu hỏi

#### US-3.1 Quản lý câu hỏi và đáp án

**Là** giáo viên, **tôi muốn** tạo câu hỏi có đáp án đúng và độ khó, **để** dùng lại câu hỏi trong nhiều bài thi.

```gherkin
Feature: Ngân hàng câu hỏi

  Scenario: Tạo câu hỏi trắc nghiệm hợp lệ
    Given giáo viên sở hữu ngân hàng câu hỏi
    And câu hỏi có nội dung và số lượng đáp án hợp lệ
    And số đáp án đúng phù hợp với loại câu hỏi
    When giáo viên lưu câu hỏi
    Then câu hỏi và các đáp án được lưu cùng ngân hàng
    And đáp án đúng không bị hiển thị cho học sinh trước khi được phép công bố

  Scenario: Câu hỏi đang được dùng trong đề thi
    Given câu hỏi đã được liên kết với đề thi đã phát hành hoặc có lượt thi
    When giáo viên yêu cầu sửa nội dung hoặc đáp án làm thay đổi chấm điểm
    Then hệ thống chặn sửa ảnh hưởng dữ liệu lịch sử
    Or tạo phiên bản mới mà không thay đổi đề và lượt thi cũ
```

### E4 — Tạo và công bố đề

#### US-4.1 Lập lịch và giao đề cho lớp

**Là** giáo viên, **tôi muốn** tạo đề có thời gian và lớp dự thi, **để** học sinh đúng đối tượng có thể làm bài trong khoảng cho phép.

```gherkin
Feature: Tổ chức thi

  Scenario: Công bố đề hợp lệ cho lớp
    Given giáo viên có quyền phụ trách lớp
    And đề có ít nhất một câu hợp lệ
    And thời điểm bắt đầu nhỏ hơn thời điểm kết thúc
    When giáo viên công bố đề cho lớp
    Then đề được phân công cho lớp
    And học sinh trong lớp chỉ thấy đề trong thời gian được phép

  Scenario: Khoảng thời gian không hợp lệ
    Given giáo viên đang tạo hoặc chỉnh sửa lịch thi
    When thời điểm kết thúc bằng hoặc trước thời điểm bắt đầu
    Then hệ thống từ chối lưu
    And chỉ rõ trường thời gian cần sửa

  Scenario: Cập nhật cài đặt trong khi có lượt thi
    Given có học sinh đang làm lượt thi được bắt đầu theo cấu hình cũ
    When giáo viên cập nhật thời lượng hoặc đáp án của đề
    Then lượt thi hiện tại tiếp tục dùng cấu hình đã chụp tại thời điểm bắt đầu
    And cấu hình mới chỉ áp dụng cho lượt thi bắt đầu sau đó
```

### E5 — Làm bài và khôi phục

#### US-5.1 Lưu tự động và tiếp tục sau gián đoạn

**Là** học sinh, **tôi muốn** câu trả lời được lưu và khôi phục sau sự cố mạng, **để** không mất toàn bộ bài làm.

```gherkin
Feature: Lưu và khôi phục bài thi

  Scenario: Lưu câu trả lời thành công
    Given học sinh có lượt thi đang hoạt động
    When trình duyệt gửi câu trả lời hợp lệ
    Then máy chủ lưu câu trả lời gắn với đúng học sinh và lượt thi
    And giao diện chỉ báo đã lưu sau khi nhận xác nhận của máy chủ

  Scenario: Mất mạng khi đang trả lời
    Given một số câu trả lời đã được máy chủ xác nhận lưu
    When thiết bị mất kết nối trước lần lưu tiếp theo
    Then giao diện thông báo trạng thái ngoại tuyến
    And không hiển thị sai rằng câu trả lời mới đã được lưu trên máy chủ
    When kết nối được khôi phục
    Then hệ thống đồng bộ lại theo thứ tự phiên bản
    And báo rõ các câu trả lời được lưu hoặc cần người dùng xử lý xung đột

  Scenario: Hết giờ khi thiết bị ngoại tuyến
    Given thời hạn thi được xác định bởi máy chủ
    And thiết bị học sinh đang ngoại tuyến khi hết giờ
    When thiết bị kết nối lại và gửi bài
    Then máy chủ từ chối câu trả lời gửi sau hạn hoặc áp dụng chính sách gia hạn đã công bố
    And lưu thời điểm/lý do kết thúc theo đồng hồ máy chủ
```

#### US-5.2 Chống gian lận thời gian và thao tác không hợp lệ

**Là** giáo viên, **tôi muốn** thời gian và log vi phạm không thể bị quyết định bởi trình duyệt, **để** giảm cơ hội thao túng lượt thi.

```gherkin
Feature: Toàn vẹn thời gian và chống gian lận

  Scenario: Đồng hồ thiết bị bị chỉnh lùi
    Given lượt thi đã bắt đầu với thời lượng được máy chủ ghi nhận
    When học sinh chỉnh lùi đồng hồ hoặc thay đổi giờ trên thiết bị
    Then thời điểm hết giờ vẫn được tính từ dữ liệu máy chủ
    And request sau hạn không thể kéo dài lượt thi

  Scenario: Sửa ID hoặc gửi đáp án ngoài đề
    Given học sinh có một lượt thi hợp lệ
    When học sinh gửi answer ID không thuộc câu hỏi/đề của lượt thi
    Then máy chủ từ chối dữ liệu không hợp lệ
    And không thay đổi điểm hoặc câu trả lời đã lưu

  Scenario: Vi phạm đạt ngưỡng
    Given đề bật chống gian lận và có giới hạn vi phạm
    When máy chủ xác nhận số vi phạm đạt giới hạn
    Then hệ thống áp dụng hành động đã công bố cho đề
    And lưu lý do kết thúc và log để giáo viên rà soát nếu chính sách yêu cầu
```

### E6 — Nộp và chấm điểm

#### US-6.1 Nộp bài chỉ một lần

**Là** học sinh, **tôi muốn** nộp bài an toàn kể cả khi bấm nhiều lần, **để** nhận một kết quả cuối cùng nhất quán.

```gherkin
Feature: Chấm điểm và hoàn tất lượt thi

  Scenario: Nộp bài hợp lệ
    Given lượt thi của học sinh đang ở trạng thái in_progress
    When học sinh nộp câu trả lời trước hạn
    Then máy chủ lưu câu trả lời cuối cùng và chấm theo đáp án đã chụp
    And chuyển lượt thi sang trạng thái completed
    And chỉ tạo một kết quả cuối cùng

  Scenario: Hai request nộp đến đồng thời
    Given một lượt thi đang ở trạng thái in_progress
    When trình duyệt gửi hai request nộp đồng thời
    Then chỉ một request được quyền hoàn tất lượt thi
    And request còn lại nhận phản hồi đã nộp hoặc không còn hợp lệ
    And điểm số không bị tính hai lần

  Scenario: Học sinh gửi lại sau khi không nhận được phản hồi
    Given máy chủ đã hoàn tất lượt thi nhưng phản hồi bị mất trên mạng
    When học sinh gửi lại cùng yêu cầu nộp
    Then hệ thống trả về trạng thái/kết quả đã hoàn tất một cách an toàn
    And không tạo thêm lượt thi hoặc thay đổi điểm đã chốt

  Scenario: Câu hỏi nhiều đáp án
    Given câu hỏi yêu cầu chọn nhiều đáp án đúng
    When học sinh chọn một tập đáp án
    Then chỉ tập đáp án khớp quy tắc chấm đã công bố mới được tính đúng
    And câu bỏ trống được chấm theo chính sách điểm đã công bố
```

## 6. Edge Cases và yêu cầu xử lý

### 6.1 Bảo mật và toàn vẹn dữ liệu

| ID | Trường hợp biên/đe dọa | Hành vi bắt buộc |
|---|---|---|
| SEC-01 | SQL injection qua email, từ khóa tìm kiếm, mã lớp, ID đề hoặc dữ liệu đáp án | Dùng prepared statement cho mọi dữ liệu đầu vào; không nối input trực tiếp vào SQL; payload như `' OR 1=1 --` phải được xem là dữ liệu thường hoặc bị validation từ chối, không làm lộ/đổi dữ liệu |
| SEC-02 | Đổi `user_id`, `class_id`, `exam_id`, `attempt_id` trong request | Kiểm tra vai trò, quyền sở hữu và quan hệ đối tượng ở máy chủ; trả 403/404 theo quy ước; không rò rỉ dữ liệu đối tượng khác |
| SEC-03 | CSRF trên thao tác tạo/sửa/xóa/gán/nộp bài | Mọi mutation qua POST và kiểm tra token CSRF; token thiếu/sai bị từ chối; GET không làm thay đổi dữ liệu |
| SEC-04 | XSS trong tên lớp, nội dung câu hỏi, đáp án, tên người dùng hoặc ghi chú rà soát | Escape theo ngữ cảnh khi render; không render HTML người dùng như markup thực thi; kiểm thử payload script ở bảng, alert và thuộc tính |
| SEC-05 | Gửi role/status/điểm hoặc `is_correct` giả từ client | Chỉ nhận các trường được phép; vai trò, đáp án chuẩn, điểm và trạng thái cuối do máy chủ quyết định |
| SEC-06 | Tài khoản bị khóa hoặc đổi vai trò khi đang dùng phiên | Kiểm tra trạng thái/role ở thao tác nhạy cảm; thu hồi hoặc giới hạn phiên theo chính sách; không cho tài khoản đã khóa tiếp tục tạo/nộp request trái quyền |
| SEC-07 | Đoán mã lớp hoặc reset token | Mã tham gia không thay thế xác thực; giới hạn tần suất thử; token khôi phục đủ ngẫu nhiên, có hạn dùng, dùng một lần và không ghi token vào log |
| SEC-08 | Nội dung câu hỏi chứa HTML, URL ảnh nguy hiểm hoặc dữ liệu quá dài | Giới hạn độ dài/kích thước, whitelist scheme cho URL nếu có; từ chối loại dữ liệu không hỗ trợ; thông báo lỗi không chứa SQL/stack trace |
| SEC-09 | Giáo viên đổi nội dung/đáp án sau khi học sinh bắt đầu | Lượt thi dùng snapshot/version bất biến; không chấm lại lịch sử bằng đáp án mới nếu chưa có quy trình điều chỉnh điểm được audit |

### 6.2 Thời gian thi

| ID | Trường hợp biên | Hành vi bắt buộc |
|---|---|---|
| TIME-01 | Học sinh sửa giờ thiết bị hoặc JavaScript timer | Tất cả kiểm tra hạn dùng thời gian máy chủ/DB; đồng hồ giao diện chỉ để hiển thị |
| TIME-02 | Nộp đúng thời điểm kết thúc | Định nghĩa một quy tắc biên duy nhất, ví dụ request được máy chủ tiếp nhận trước `end_time` được tính hợp lệ; dùng cùng quy tắc cho nộp, lưu và API |
| TIME-03 | Độ lệch múi giờ, DST hoặc giờ máy chủ | Lưu thời gian nhất quán (UTC hoặc chính sách DB thống nhất), hiển thị múi giờ rõ; kiểm thử chuyển đổi giờ |
| TIME-04 | Bắt đầu ngay trước giờ đóng nhưng thời lượng dài hơn thời gian còn lại | Xác định rõ deadline là min(thời lượng cá nhân, giờ đóng đề) hay quy tắc khác; hiển thị trước khi bắt đầu và cưỡng chế trên máy chủ |
| TIME-05 | Trình duyệt ngủ nền, reload hoặc bị đóng | Khi mở lại, tính thời gian còn lại từ thời điểm bắt đầu phía máy chủ, không đếm bù dựa trên số tick client |
| TIME-06 | Job/cron/submit tự động không chạy đúng lúc | Bất kỳ request tiếp theo cũng phải phát hiện quá hạn và hoàn tất theo chính sách; job nền không phải cơ chế duy nhất bảo đảm kết thúc |
| TIME-07 | Thời lượng hoặc lịch thi được cập nhật giữa lượt | Lượt hiện hành tiếp tục dùng snapshot; chỉ lượt bắt đầu sau đó nhận cài đặt mới |

### 6.3 Sự cố mạng và lưu câu trả lời

| ID | Trường hợp biên | Hành vi bắt buộc |
|---|---|---|
| NET-01 | Mất mạng trước khi request lưu đến máy chủ | Hiển thị chưa đồng bộ/đang thử lại; không xác nhận đã lưu |
| NET-02 | Máy chủ lưu thành công nhưng phản hồi bị mất | Request lặp phải idempotent; cùng lượt/câu không tạo bản ghi trùng hoặc ghi đè câu trả lời mới hơn |
| NET-03 | Retry gửi câu trả lời cũ sau câu trả lời mới | Gắn sequence/version hoặc thời điểm hợp lệ và bỏ qua payload cũ; hiển thị trạng thái đồng bộ |
| NET-04 | Reload khi còn câu trả lời chỉ lưu cục bộ | Phục hồi bản nháp nếu chính sách bảo mật cho phép; phân biệt rõ dữ liệu cục bộ với dữ liệu đã xác nhận máy chủ |
| NET-05 | Học sinh mở cùng lượt trên hai tab/thiết bị | Quy định một phiên hoạt động hoặc dùng phiên bản lạc quan; phát hiện xung đột và yêu cầu chọn/đồng bộ thay vì âm thầm mất dữ liệu |
| NET-06 | Hết giờ trong lúc request lưu đang bay | Máy chủ áp dụng thứ tự và deadline thống nhất; câu trả lời đến sau deadline không được lùi thời gian; trả trạng thái cuối rõ ràng |
| NET-07 | Mạng chập chờn liên tục | Retry có giới hạn/backoff, không tạo bão request; cung cấp hướng dẫn liên hệ/hỗ trợ trước khi hết giờ |
| NET-08 | Request lưu/nộp trả lỗi 5xx | Giữ bài nháp chưa xác nhận, cho phép thử lại an toàn; không xóa local state trước khi nhận xác nhận cuối |

### 6.4 Concurrency và tính nhất quán

| ID | Trường hợp biên | Hành vi bắt buộc |
|---|---|---|
| CON-01 | Nhiều học sinh nộp cùng lúc | Xử lý độc lập theo attempt; khóa hàng/conditional update ở DB; tối ưu truy vấn và đo tải ở số đồng thời mục tiêu |
| CON-02 | Hai request cùng nộp một attempt | Chỉ một lần chuyển `in_progress` → `completed`; request cạnh tranh không chấm hoặc cộng điểm lần hai |
| CON-03 | Autosave và submit xảy ra đồng thời | Serialize theo attempt hoặc dùng version/CAS; dữ liệu autosave không được ghi đè sau khi đã hoàn tất |
| CON-04 | Gán cùng học sinh vào lớp đồng thời | Ràng buộc unique ở DB là lớp bảo vệ cuối; xử lý duplicate key thành thông báo phù hợp, không tạo quan hệ lặp |
| CON-05 | Hai admin tạo mã lớp giống nhau cùng lúc | Unique constraint ở DB; validation trước thao tác chỉ để UX, lỗi xung đột sau insert phải được xử lý |
| CON-06 | Giáo viên sửa đề trong khi học sinh bắt đầu lượt thi | Giao dịch tạo attempt phải chụp một cấu hình nhất quán; không trộn cài đặt cũ/mới |
| CON-07 | Nộp xảy ra cùng lúc với request vi phạm chạm ngưỡng | Khóa attempt và áp dụng đúng một lý do kết thúc theo quy tắc ưu tiên đã định nghĩa; giữ nguyên log hợp lệ |
| CON-08 | Deadlock/timeout trong transaction chấm điểm | Rollback toàn bộ; không để attempt completed một phần; retry an toàn với giới hạn và log lỗi vận hành |
| CON-09 | Câu trả lời đến trong lúc attempt vừa hoàn tất | Conditional write yêu cầu `status = in_progress`; dữ liệu đến muộn nhận lỗi trạng thái cuối, không thay đổi điểm |

## 7. Quy tắc nghiệp vụ cốt lõi

1. **RBAC:** role chỉ do quản trị viên có quyền thay đổi; mọi endpoint tự kiểm tra vai trò và quyền trên tài nguyên.
2. **Lớp:** theo schema hiện tại, lớp có một giáo viên phụ trách và học sinh có thể tham gia nhiều lớp.
3. **Mã lớp:** duy nhất, không phân biệt chữ hoa/thường theo quy tắc chuẩn hóa sản phẩm; không dùng như bằng chứng xác thực duy nhất.
4. **Câu hỏi:** loại câu hỏi quyết định số đáp án đúng hợp lệ; câu hỏi/đáp án dùng trong lượt thi cần được snapshot hoặc khóa phiên bản.
5. **Đề thi:** đề chỉ được bắt đầu khi đã công bố, trong thời gian cho phép, còn hiệu lực và học sinh thuộc đối tượng được giao.
6. **Lượt thi:** mỗi học sinh chỉ có số lượt tối đa theo cấu hình đề; phiên bản đầu nên mặc định một lượt nhưng cần xác nhận với Product.
7. **Thời hạn:** máy chủ là nguồn chuẩn; đồng hồ client chỉ hỗ trợ hiển thị.
8. **Chấm điểm:** tính trên dữ liệu máy chủ và đáp án chuẩn bất biến của lượt thi; quy tắc bỏ trống, nhiều đáp án, trọng số và làm tròn phải được công bố trước.
9. **Nộp:** trạng thái kết quả là idempotent; sau khi hoàn tất, autosave và submit lặp không sửa kết quả.
10. **Chống gian lận:** log từ client là tín hiệu hỗ trợ rà soát, không phải kết luận duy nhất; học sinh được thông báo hành vi nào được ghi nhận và hậu quả cấu hình.
11. **Lưu trữ:** khóa/xóa quan hệ người dùng không được âm thầm xóa lịch sử điểm thi cần bảo toàn.

## 8. Yêu cầu phi chức năng

### Bảo mật và quyền riêng tư

- Dùng truy vấn tham số hóa; kiểm tra role, quyền sở hữu và trạng thái ở máy chủ.
- Dùng CSRF token cho thao tác thay đổi trạng thái; dùng hash mật khẩu an toàn; tái tạo session ID sau đăng nhập.
- Escape đầu ra HTML; không log mật khẩu, token khôi phục, CSRF token hoặc nội dung nhạy cảm không cần thiết.
- Giới hạn độ dài/tần suất request và kích thước payload; lỗi cho người dùng không tiết lộ SQL, đường dẫn máy chủ hoặc stack trace.
- Chỉ thu thập dữ liệu cần cho hoạt động học/thi; quy định thời gian lưu và quyền xem log.

### Tính sẵn sàng và khả năng phục hồi

- Thao tác tạo attempt, lưu câu trả lời theo lô và hoàn tất/chấm điểm phải có ranh giới transaction phù hợp.
- Có sao lưu và quy trình phục hồi DB được kiểm thử.
- Lỗi lưu phải hiển thị rõ; không được trả phản hồi thành công giả.
- Có log vận hành cho lỗi transaction, xung đột nộp bài, timeout và độ trễ xử lý.

### Khả năng sử dụng và tiếp cận

- Tối ưu cho màn hình điện thoại và máy tính; thao tác chính dùng được bằng bàn phím.
- Có trạng thái đang lưu/đã lưu/mất kết nối/lỗi đồng bộ dễ nhận biết, không chỉ dựa vào màu sắc.
- Thông báo lỗi gắn với trường nhập; giữ dữ liệu nhập hợp lệ sau validation failure.
- Cảnh báo trước khi rời trang hoặc nộp bài khi còn câu chưa lưu/chưa trả lời, nhưng không chặn nộp thủ công.

### Hiệu năng và vận hành

- Đặt mục tiêu tải đồng thời cho mỗi kỳ thi trước khi phát hành; dùng kiểm thử tải để xác nhận.
- Phân trang danh sách lớn; index cho khóa ngoại, attempt, class membership và truy vấn theo người dùng.
- Theo dõi tỷ lệ autosave thất bại, nộp trùng, deadlock, thời gian chấm và lỗi 5xx.

## 9. Luồng nghiệp vụ chính

1. Admin tạo/kích hoạt tài khoản giáo viên và học sinh.
2. Admin tạo lớp, phân công giáo viên, gán học sinh vào một hoặc nhiều lớp.
3. Giáo viên tạo ngân hàng, câu hỏi/đáp án và kiểm tra nội dung.
4. Giáo viên tạo đề, chọn câu hỏi, thiết lập thời lượng/lịch/đối tượng/quy tắc, xem trước rồi công bố.
5. Học sinh đăng nhập, kiểm tra điều kiện dự thi và bắt đầu lượt thi.
6. Hệ thống chụp cấu hình, tạo thứ tự đề của lượt thi và bắt đầu đồng hồ phía máy chủ.
7. Học sinh trả lời; hệ thống lưu tự động và thông báo trạng thái đồng bộ.
8. Học sinh nộp hoặc hệ thống kết thúc theo deadline/chính sách đã công bố.
9. Máy chủ khóa lượt thi, lưu câu cuối hợp lệ, chấm trong transaction, đóng băng kết quả.
10. Học sinh xem kết quả theo chính sách công bố; giáo viên xem báo cáo và rà soát trường hợp cần thiết.

## 10. Tiêu chí nghiệm thu phiên bản MVP

- [ ] Admin, teacher và student chỉ truy cập các chức năng được cấp quyền.
- [ ] Giáo viên không thể đọc/sửa ngân hàng, đề hoặc báo cáo của giáo viên khác bằng cách đổi ID.
- [ ] Admin tạo lớp với mã duy nhất, đổi giáo viên phụ trách, gán và bỏ gán học sinh; học sinh có thể thuộc nhiều lớp.
- [ ] Giáo viên tạo câu hỏi single-choice/multiple-choice với validation đáp án đúng.
- [ ] Giáo viên tạo đề có thời lượng, lịch, trạng thái và đối tượng dự thi hợp lệ.
- [ ] Học sinh không thể bắt đầu đề nháp, ngoài lịch, đã lưu trữ hoặc không được giao.
- [ ] Thời lượng và deadline được kiểm tra phía máy chủ, kể cả khi client chỉnh đồng hồ.
- [ ] Autosave báo thành công chỉ sau khi dữ liệu được máy chủ xác nhận.
- [ ] Sau reload/mất mạng, dữ liệu đã xác nhận lưu được phục hồi; retry không tạo bản ghi hoặc kết quả trùng.
- [ ] Hai request submit đồng thời chỉ tạo một kết quả cuối cùng.
- [ ] Điểm cho bộ dữ liệu chuẩn được tính đúng; đáp án chuẩn của lượt cũ không đổi khi giáo viên sửa câu hỏi/đề về sau.
- [ ] Payload SQLi/XSS/CSRF và IDOR trong bộ kiểm thử không làm lộ hoặc thay đổi dữ liệu ngoài quyền.
- [ ] Các trường hợp TIME, NET, CON trong mục 6 có kiểm thử tự động hoặc kịch bản kiểm thử tích hợp tương ứng.

## 11. Phụ thuộc, giả định và câu hỏi cần chốt

### Phụ thuộc

- Cơ sở dữ liệu MySQL/InnoDB để hỗ trợ transaction, khóa hàng và ràng buộc unique/foreign key.
- Đồng hồ hệ điều hành/DB được đồng bộ; timezone lưu trữ và timezone hiển thị được quy định thống nhất.
- Hạ tầng email hợp lệ cho đăng ký/xác minh/khôi phục nếu bật các luồng này.
- Kịch bản kiểm thử tải phản ánh số học sinh dự thi đồng thời thực tế.

### Giả định ban đầu

- Một lớp có một giáo viên phụ trách; học sinh có thể thuộc nhiều lớp.
- Hai loại câu hỏi MVP là trắc nghiệm một đáp án và nhiều đáp án.
- Điểm tự động chỉ áp dụng cho loại câu hỏi có thể chấm xác định; loại tự luận cần quy trình chấm thủ công riêng.
- Log chống gian lận không tự chứng minh hành vi gian lận.

### Câu hỏi cần Product xác nhận trước khi khóa phạm vi

1. Một học sinh được thi lại bao nhiêu lần? Có cho phép giáo viên mở lại lượt thi không?
2. Hạn nộp là thời lượng cá nhân, giờ đóng đề hay thời điểm nào đến trước?
3. Mất mạng có được gia hạn thời gian? Nếu có, ai duyệt và giới hạn bao lâu?
4. Khi nào học sinh được xem đáp án đúng/giải thích: ngay sau nộp, sau khi đề đóng hay sau khi giáo viên công bố?
5. Đề được giao cho cả lớp hay có thể loại trừ/chọn một nhóm học sinh trong lớp?
6. Điểm thành phần, trọng số, điểm âm, bỏ trống và quy tắc làm tròn được cấu hình thế nào?
7. Log chống gian lận nào được bật mặc định và quy trình khiếu nại/rà soát là gì?
8. Dữ liệu lịch sử thi và log cần được lưu bao lâu; ai có quyền xuất dữ liệu?

## 12. Thứ tự triển khai đề xuất

1. **P0 — Tính đúng và an toàn:** RBAC/ownership, quy tắc lớp/đối tượng dự thi, đề và lượt thi, thời gian máy chủ, autosave, nộp idempotent, chấm điểm transaction, bảo toàn lịch sử.
2. **P1 — Vận hành và trải nghiệm:** báo cáo, rà soát vi phạm, tìm kiếm/lọc ngân hàng, khôi phục mạng nâng cao, audit log, quản trị lưu trữ.
3. **P2 — Mở rộng:** thêm dạng câu hỏi, nhập/xuất hàng loạt, tích hợp LMS/SSO, công cụ phân tích học tập nâng cao.

