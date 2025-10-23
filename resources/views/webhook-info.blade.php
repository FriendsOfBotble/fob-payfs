<x-core::alert type="info">
    <strong>Webhook URL:</strong> <code>{{ route('payfs.webhook') }}</code>
    <br><br>
    <strong>Hướng dẫn cấu hình webhook trên PayFS:</strong>
    <ol class="mb-0 mt-2">
        <li>Đăng nhập vào <a href="https://my.payfs.vn" target="_blank">PayFS Dashboard</a></li>
        <li>Vào mục <strong>Webhook</strong> → <strong>Cấu hình Webhook</strong></li>
        <li>Nhập Webhook URL ở trên vào ô <strong>Webhook URL</strong></li>
        <li>Chọn phương thức xác thực: <strong>API Key</strong> (bắt buộc) hoặc <strong>Signature</strong> (khuyến nghị)</li>
        <li>Lưu cấu hình và copy API Key/Secret từ PayFS vào các trường tương ứng ở trên</li>
    </ol>
</x-core::alert>
