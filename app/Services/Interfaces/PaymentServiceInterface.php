<?php

namespace App\Services\Interfaces;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PaymentServiceInterface
{
    /**
     * Lấy thông tin chi tiết một giao dịch thanh toán.
     */
    public function getPaymentDetails(int $paymentId): Payment;

    /**
     * Tạo bản ghi thanh toán mới trong hệ thống.
     */
    public function createPayment(array $data): Payment;

    /**
     * Kiểm tra số tiền thanh toán có khớp chính xác với giá trị đơn đặt phòng không (BR-21).
     */
    public function validatePaymentAmount(Booking $booking, float $amount): bool;

    /**
     * Xử lý thực hiện giao dịch thanh toán cho đơn đặt phòng.
     */
    public function processPayment(Booking $booking, array $paymentData): Payment;

    /**
     * Xử lý callback hoặc webhook từ cổng thanh toán.
     */
    public function handlePaymentCallback(array $callbackData): Payment;

    /**
     * Cập nhật trạng thái giao dịch thanh toán và đồng bộ trạng thái đơn đặt phòng.
     */
    public function updatePaymentStatus(int $paymentId, string $status): Payment;

    /**
     * Lấy danh sách giao dịch thanh toán phục vụ quản trị (phân trang, lọc trạng thái, tìm kiếm).
     */
    public function getAllPayments(?string $status = null, ?string $search = null, int $perPage = 10): LengthAwarePaginator;

    /**
     * Lấy danh sách các giao dịch thanh toán thuộc về một đơn đặt phòng.
     */
    public function getPaymentsByBooking(int $bookingId): Collection;
}
