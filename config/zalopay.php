<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cấu hình Cổng thanh toán ZaloPay Sandbox (API V2)
    |--------------------------------------------------------------------------
    | Thông tin tài khoản thử nghiệm chính thức được cung cấp bởi ZaloPay Developer.
    | Tài liệu: https://docs.zalopay.vn/vi/docs/guides/intro
    */

    // Định danh ứng dụng Merchant trong môi trường thử nghiệm
    'app_id' => (int) env('ZALOPAY_APP_ID', 2553),

    // Khóa bí mật Key1 dùng để tạo đơn hàng và sinh chữ ký mac
    'key1' => env('ZALOPAY_KEY1', 'PcY4iZIKFCIdgZvA6ueMcMHHUbRLYjPL'),

    // Khóa bí mật Key2 dùng để xác thực dữ liệu phản hồi (Callback / IPN)
    'key2' => env('ZALOPAY_KEY2', 'kLtgPl8YESDmyABkQgeZByOUJsbcpNI2'),

    // Cổng API tạo đơn hàng thử nghiệm ZaloPay V2
    'endpoint' => env('ZALOPAY_ENDPOINT', 'https://sb-openapi.zalopay.vn/v2/create'),

    // Cổng API truy vấn trạng thái giao dịch ZaloPay V2
    'query_endpoint' => env('ZALOPAY_QUERY_ENDPOINT', 'https://sb-openapi.zalopay.vn/v2/query'),
];
