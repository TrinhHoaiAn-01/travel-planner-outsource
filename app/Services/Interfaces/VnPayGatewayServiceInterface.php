<?php

namespace App\Services\Interfaces;

use App\Models\Booking;
use App\Models\Payment;

interface VnPayGatewayServiceInterface
{
    /**
     * Tạo liên kết thanh toán sang cổng VNPay Sandbox kèm chữ ký số HMAC-SHA512.
     *
     * @param Booking $booking Đơn đặt phòng cần thanh toán
     * @param string|null $ipAddress Địa chỉ IP của người dùng thực hiện giao dịch
     * @return array Kết quả gồm [success, payUrl, txnRef, message]
     */
    public function createPaymentUrl(Booking $booking, ?string $ipAddress = null): array;

    /**
     * Xác thực chữ ký số HMAC-SHA512 của dữ liệu VNPay phản hồi qua Return URL hoặc IPN Webhook.
     *
     * @param array $data Dữ liệu tham số nhận từ cổng VNPay
     * @return bool True nếu chữ ký số hợp lệ và đảm bảo tính toàn vẹn
     */
    public function verifySignature(array $data): bool;

    /**
     * Xử lý kết quả giao dịch từ VNPay, cập nhật trạng thái thanh toán và đơn đặt phòng.
     *
     * @param array $callbackData Dữ liệu tham số nhận từ cổng VNPay
     * @return Payment
     */
    public function processCallback(array $callbackData): Payment;
}
