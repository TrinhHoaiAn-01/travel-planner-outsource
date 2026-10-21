<?php

namespace App\Services\Interfaces;

use App\Models\Booking;
use App\Models\Payment;

interface MoMoGatewayServiceInterface
{
    /**
     * Tạo yêu cầu thanh toán sang cổng MoMo và nhận URL thanh toán (payUrl).
     *
     * @param Booking $booking Đơn đặt phòng cần thanh toán
     * @return array Kết quả phản hồi từ MoMo gồm [success, payUrl, orderId, message]
     */
    public function createPaymentUrl(Booking $booking): array;

    /**
     * Xác thực chữ ký số HMAC-SHA256 của MoMo gửi về qua Callback hoặc IPN.
     *
     * @param array $data Dữ liệu callback từ MoMo
     * @return bool True nếu chữ ký hợp lệ và an toàn
     */
    public function verifySignature(array $data): bool;

    /**
     * Xử lý kết quả trả về từ MoMo, cập nhật giao dịch thanh toán và đơn đặt phòng.
     *
     * @param array $callbackData Dữ liệu tham số nhận từ MoMo
     * @return Payment
     */
    public function processCallback(array $callbackData): Payment;
}
