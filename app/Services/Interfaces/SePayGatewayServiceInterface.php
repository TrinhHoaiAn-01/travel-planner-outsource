<?php

namespace App\Services\Interfaces;

use App\Models\Booking;
use App\Models\Payment;

interface SePayGatewayServiceInterface
{
    /**
     * Lấy thông tin thanh toán chuyển khoản ngân hàng gồm tài khoản, nội dung và mã VietQR.
     *
     * @param Booking $booking Đơn đặt phòng cần thanh toán
     * @return array Chi tiết chuyển khoản ngân hàng
     */
    public function getPaymentDetails(Booking $booking): array;

    /**
     * Khởi tạo hoặc lấy bản ghi Payment ở trạng thái chờ thanh toán (pending) cho đơn đặt phòng.
     *
     * @param Booking $booking Đơn đặt phòng
     * @return Payment
     */
    public function createOrGetPayment(Booking $booking): Payment;

    /**
     * Tạo đường dẫn hình ảnh mã VietQR từ SePay theo chuẩn NAPAS 247.
     *
     * @param float $amount Số tiền thanh toán
     * @param string $transferContent Nội dung chuyển khoản
     * @return string URL hình ảnh VietQR
     */
    public function generateQrCodeUrl(float $amount, string $transferContent): string;

    /**
     * Xác thực API Key / Token từ Webhook do máy chủ SePay gửi đến.
     *
     * @param string|null $authHeader Giá trị tiêu đề Authorization hoặc API Token
     * @return bool True nếu hợp lệ
     */
    public function verifyWebhookApiKey(?string $authHeader): bool;

    /**
     * Trích xuất mã đặt phòng (Booking Code) từ nội dung biến động số dư ngân hàng.
     *
     * @param string $content Nội dung chuyển khoản
     * @return string|null Mã đặt phòng nếu tìm thấy
     */
    public function extractBookingCode(string $content): ?string;

    /**
     * Xử lý dữ liệu Webhook từ SePay, kiểm tra số tiền và cập nhật trạng thái đơn đặt phòng.
     *
     * @param array $payload Dữ liệu webhook JSON gửi từ SePay
     * @return array Kết quả xử lý gồm [success, message, payment]
     */
    public function processWebhook(array $payload): array;
}

