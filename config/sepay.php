<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cấu hình Cổng thanh toán Tự động SePay (VietQR / Internet Banking)
    |--------------------------------------------------------------------------
    | Tích hợp biến động số dư và quét mã VietQR tự động qua nền tảng SePay.
    | Tài liệu: https://sepay.vn/docs
    */

    // API Key / Token dùng để xác thực Webhook gửi từ máy chủ SePay
    'api_key' => env('SEPAY_API_KEY', 'sepay_demo_api_key_travel_planner'),

    // Số tài khoản ngân hàng thụ hưởng nhận tiền
    'account_number' => env('SEPAY_ACCOUNT_NUMBER', '0000000002'),

    // Tên viết tắt hoặc mã ngân hàng thụ hưởng (MBBank, TPBank, Vietcombank, ACB, ICB, VPBank, ...)
    'bank_name' => env('SEPAY_BANK_NAME', 'MBBank'),

    // Tên chủ sở hữu tài khoản ngân hàng thụ hưởng (viết hoa không dấu)
    'account_holder' => env('SEPAY_ACCOUNT_HOLDER', 'TRAVEL PLANNER TEST'),

    // Tiền tố nhận diện mã đơn trong nội dung chuyển khoản (Ví dụ: TPB BK12345678)
    'pattern_prefix' => env('SEPAY_PATTERN_PREFIX', 'TPB'),

    // Cổng tạo link hình ảnh VietQR tự động của SePay
    'qr_image_endpoint' => env('SEPAY_QR_ENDPOINT', 'https://qr.sepay.vn/img'),
];

