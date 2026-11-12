<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cấu hình Cổng thanh toán VNPay Sandbox (Phiên bản API 2.1.0)
    |--------------------------------------------------------------------------
    | Thông tin tài khoản Merchant thử nghiệm được VNPay cấp chính thức.
    | Tài liệu: https://sandbox.vnpayment.vn/apis/docs/thanh-toan-pay/pay.html
    */

    // Terminal ID / Mã Website (vnp_TmnCode)
    'tmn_code' => env('VNPAY_TMN_CODE', 'UTSQQSRL'),

    // Chuỗi bí mật tạo mã băm kiểm tra toàn vẹn Checksum (vnp_HashSecret)
    'hash_secret' => env('VNPAY_HASH_SECRET', 'VZWQDFXHAPZUJZHIAGELBGWXFDOZVKFB'),

    // Đường dẫn cổng thanh toán môi trường thử nghiệm (vnp_Url)
    'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),

    // Phiên bản chuẩn API VNPay
    'version' => '2.1.0',

    // Lệnh thanh toán mặc định
    'command' => 'pay',

    // Đơn vị tiền tệ
    'curr_code' => 'VND',

    // Ngôn ngữ giao diện cổng thanh toán (vn hoặc en)
    'locale' => 'vn',

    // Thời gian hết hạn của giao dịch thanh toán (phút)
    'expire_minutes' => 15,
];
