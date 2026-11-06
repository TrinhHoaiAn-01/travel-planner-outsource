<?php

namespace App\Services\Interfaces;

use App\Models\Booking;
use App\Models\Payment;

interface ZaloPayGatewayServiceInterface
{
    /**
     * Tạo yêu cầu thanh toán sang cổng ZaloPay V2 và nhận URL thanh toán (order_url).
     *
     * @param Booking $booking Đơn đặt phòng cần thanh toán
     * @return array Kết quả gồm [success, payUrl, zpTransToken, orderId, message]
     */
    public function createPaymentUrl(Booking $booking): array;

    /**
     * Xác thực chữ ký số (Checksum) khi người dùng hoàn tất thanh toán và chuyển hướng về hệ thống.
     *
     * @param array $data Dữ liệu query string từ ZaloPay redirect về
     * @return bool True nếu chữ ký checksum hợp lệ
     */
    public function verifyRedirectChecksum(array $data): bool;

    /**
     * Xác thực chữ ký số MAC khi ZaloPay gửi Webhook Callback (IPN) ngầm tới hệ thống.
     *
     * @param string $dataJson Chuỗi JSON thô nhận từ trường 'data'
     * @param string $mac Chữ ký số nhận từ trường 'mac'
     * @return bool True nếu chữ ký MAC hợp lệ
     */
    public function verifyCallbackMac(string $dataJson, string $mac): bool;

    /**
     * Xử lý kết quả trả về từ ZaloPay (Return URL hoặc Callback IPN), cập nhật Payment và Booking.
     *
     * @param array $payload Dữ liệu đơn hàng nhận được từ ZaloPay
     * @return Payment
     */
    public function processCallback(array $payload): Payment;
}
