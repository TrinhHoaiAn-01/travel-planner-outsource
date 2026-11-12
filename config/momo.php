<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MoMo Payment Gateway Configuration (API V2)
    |--------------------------------------------------------------------------
    | Cấu hình tích hợp cổng thanh toán MoMo Sandbox dành cho nhà phát triển.
    | Thông tin tài khoản thử nghiệm chính thức được cung cấp bởi MoMo Developers.
    | Tài liệu: https://developers.momo.vn/v3/docs/payment/api/wallet/onetime
    */

    // Mã đối tác (Partner Code) được MoMo cấp cho môi trường Sandbox
    'partner_code' => env('MOMO_PARTNER_CODE', 'MOMOBKUN20180529'),

    // Khóa truy cập (Access Key) dùng để xác thực yêu cầu
    'access_key' => env('MOMO_ACCESS_KEY', 'klm05TvNBzhg7h7j'),

    // Khóa bí mật (Secret Key) dùng để sinh chữ ký số HMAC-SHA256
    'secret_key' => env('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2Ac'),

    // Cổng tiếp nhận yêu cầu thanh toán trực tuyến (Sandbox API Endpoint)
    'endpoint' => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),

    // Đường dẫn chuyển hướng sau khi người dùng hoàn tất thanh toán trên MoMo
    'redirect_url' => env('MOMO_REDIRECT_URL', null),

    // Đường dẫn Webhook (IPN) để máy chủ MoMo thông báo kết quả giao dịch
    'ipn_url' => env('MOMO_IPN_URL', null),
];
