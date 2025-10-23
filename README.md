# PayFS

Plugin này cho phép bạn tích hợp PayFS để tự động xác thực thanh toán qua phương thức chuyển khoản ngân hàng.

## Yêu cầu tối thiểu

- Botble core 7.0.5 hoặc cao hơn.
- **QUAN TRỌNG 1: Plugin hỗ trợ tự động quy đổi từ các loại tiền tệ khác sang VND. Tuy nhiên, khuyến nghị sử dụng tiền Việt (Đồng, VND) làm mặc định khi sử dụng phương thức thanh toán PayFS để đảm bảo chính xác nhất.**
- **QUAN TRỌNG 2: Yêu cầu bắt buộc cần phải có tiền tố mã đơn hàng để sử dụng được tính năng này. Vui lòng điền đúng theo mô tả bên dưới của biểu mẫu khi bạn thiết lập phương thức.**

## Cài đặt

### Cài đặt thông qua bảng quản trị

Vào **Bảng quản trị (Admin)** và chọn **Plugins**. Bấm vào nút "Thêm mới (Add new)", tìm kiếm plugin **PayFS** và sau đó bấm vào "Cài đặt (Install)".

### Cài đặt thủ công

1. Bạn có thể tải về các bản phát hành tại đây hoặc trên [Botble Marketplace](https://marketplace.botble.com/products/friendsofbotble/fob-payfs).
2. Giải nén file nén vào thư mục `platform/plugins`.
3. Vào **Bảng quản trị (Admin)**, chọn **Plugins**, và bấm vào nút **Kích hoạt (Activate)**.

## Cách sử dụng

1. Vào **Bảng quản trị (Admin)**, chọn **Thanh toán (Payments)**, và bấm vào **Phương thức thanh toán (Payment Methods)**.
2. Kích hoạt **PayFS** bằng cách điền đầy đủ thông tin vào biểu mẫu:
   - Chọn ngân hàng
   - Nhập số tài khoản
   - Nhập tên chủ tài khoản
   - Nhập tiền tố mã thanh toán (ví dụ: SDH)
   - Nhập API Key từ PayFS dashboard
   - Nhập Webhook Secret (tùy chọn, khuyến nghị sử dụng để bảo mật tốt hơn)
3. Sao chép "Webhook URL" hiển thị trong biểu mẫu.
4. Truy cập vào tài khoản [PayFS Dashboard](https://payfs.vn) của bạn.
5. Vào phần **Webhook Settings** và tạo webhook mới:
   - Dán URL webhook đã sao chép
   - Cấu hình xác thực webhook:
     - **Xác thực cơ bản**: Sử dụng API Key (X-Client-API-Key)
     - **Xác thực nâng cao** (khuyến nghị): Thêm Webhook Secret để xác thực HMAC-SHA256
6. Lưu cấu hình và tiến hành sử dụng như bình thường.
7. Khách hàng sẽ nhận được mã QR code và thông tin chuyển khoản khi thanh toán.
8. Hệ thống tự động xác nhận thanh toán khi nhận được webhook từ PayFS.

## Tính năng

- ✅ Tích hợp PayFS webhook với xác thực 2 lớp (API Key + HMAC signature)
- ✅ Tạo mã QR code tự động cho chuyển khoản ngân hàng (VietQR)
- ✅ Hỗ trợ 30+ ngân hàng Việt Nam
- ✅ Tự động quy đổi tiền tệ sang VND
- ✅ Kiểm tra trạng thái thanh toán real-time
- ✅ Bảo vệ chống xử lý trùng lặp (idempotency)
- ✅ Ghi log chi tiết để debug

## Lịch sử thay đổi

Vui lòng xem [LỊCH SỬ THAY ĐỔI](CHANGELOG.md) để biết chi tiết.

## Bảo mật

Nếu bạn phát hiện bất kỳ vấn đề liên quan đến bảo mật nào, vui lòng gửi email tới friendsofbotble@gmail.com thay vì sử dụng issues.

## Credits

- [Friends Of Botble](https://github.com/FriendsOfBotble)
- [All Contributors](../../contributors)

## Giấy phép

MIT License (MIT). Vui lòng xem chi tiết trong phần [thông tin giấy phép](LICENSE).
