<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Interfaces\PaymentServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService implements PaymentServiceInterface
{
    /**
     * Lấy thông tin chi tiết một giao dịch thanh toán.
     */
    public function getPaymentDetails(int $paymentId): Payment
    {
        return Payment::with(['booking.user', 'booking.roomType.destination', 'booking.trip'])
            ->findOrFail($paymentId);
    }

    /**
     * Tạo bản ghi thanh toán mới trong hệ thống.
     */
    public function createPayment(array $data): Payment
    {
        return Payment::create($data);
    }

    /**
     * Kiểm tra số tiền thanh toán có khớp chính xác với giá trị đơn đặt phòng không (BR-21).
     */
    public function validatePaymentAmount(Booking $booking, float $amount): bool
    {
        // Kiểm tra sai lệch số tiền (cho phép sai số làm tròn thập phân < 0.01)
        if (abs((float) $amount - (float) $booking->total_price) >= 0.01) {
            throw ValidationException::withMessages([
                'amount' => 'Số tiền thanh toán không khớp với tổng tiền đơn đặt phòng trong cơ sở dữ liệu (Quy tắc chống gian lận BR-21).',
            ]);
        }

        return true;
    }

    /**
     * Xử lý thực hiện giao dịch thanh toán cho đơn đặt phòng.
     */
    public function processPayment(Booking $booking, array $paymentData): Payment
    {
        // 1. Kiểm tra trạng thái đơn: Không thể thanh toán nếu đơn đã bị hủy
        if ($booking->status === 'cancelled') {
            throw ValidationException::withMessages([
                'payment' => 'Không thể thực hiện thanh toán cho đơn đặt phòng đã bị hủy.',
            ]);
        }

        // 2. Kiểm tra số tiền khớp với Database (BR-21)
        $amount = (float) $paymentData['amount'];
        $this->validatePaymentAmount($booking, $amount);

        // 3. Sinh mã giao dịch duy nhất
        $transactionId = 'TXN-' . strtoupper(Str::random(8)) . '-' . time();

        // 4. Xác định trạng thái thanh toán ban đầu theo phương thức
        // Ví dụ: Phương thức thanh toán tiền mặt tại chỗ sẽ ở trạng thái pending, các cổng trực tuyến là completed
        $paymentMethod = $paymentData['payment_method'];
        $status = ($paymentMethod === 'cash') ? 'pending' : 'completed';

        return DB::transaction(function () use ($booking, $amount, $paymentMethod, $transactionId, $status) {
            // 1. Khóa dòng Booking với lockForUpdate() để tránh Race Condition và Deadlock 1213 trên InnoDB
            $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->firstOrFail();

            // 2. Chống thanh toán trùng lặp khi người dùng click nhanh (Double Submit)
            if (($lockedBooking->status === 'confirmed' || $lockedBooking->status === 'completed') && $status === 'completed') {
                $existingPayment = Payment::where('booking_id', $lockedBooking->id)
                    ->where('status', 'completed')
                    ->latest('id')
                    ->first();
                if ($existingPayment) {
                    return $existingPayment;
                }
            }

            // 3. Cập nhật trạng thái Booking trước khi tạo Payment để khóa theo đúng thứ tự cha - con
            if ($status === 'completed') {
                $lockedBooking->update([
                    'status' => 'confirmed',
                ]);
            }

            // 4. Tạo bản ghi giao dịch thanh toán
            $payment = Payment::create([
                'booking_id' => $lockedBooking->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'transaction_id' => $transactionId,
                'status' => $status,
                'payment_date' => now(),
            ]);

            return $payment;
        }, 5);
    }

    /**
     * Xử lý callback hoặc webhook từ cổng thanh toán.
     */
    public function handlePaymentCallback(array $callbackData): Payment
    {
        $transactionId = $callbackData['transaction_id'] ?? null;
        $status = $callbackData['status'] ?? 'completed';

        return DB::transaction(function () use ($transactionId, $status) {
            $payment = Payment::where('transaction_id', $transactionId)->lockForUpdate()->firstOrFail();

            // Khóa dòng booking liên kết
            $lockedBooking = Booking::where('id', $payment->booking_id)->lockForUpdate()->firstOrFail();

            if ($status === 'completed') {
                $lockedBooking->update([
                    'status' => 'confirmed',
                ]);
            }

            $payment->update([
                'status' => $status,
                'payment_date' => now(),
            ]);

            return $payment;
        }, 5);
    }

    /**
     * Cập nhật trạng thái giao dịch thanh toán và đồng bộ trạng thái đơn đặt phòng.
     */
    public function updatePaymentStatus(int $paymentId, string $status): Payment
    {
        return DB::transaction(function () use ($paymentId, $status) {
            $payment = Payment::where('id', $paymentId)->lockForUpdate()->firstOrFail();
            $lockedBooking = Booking::where('id', $payment->booking_id)->lockForUpdate()->firstOrFail();

            $payment->update([
                'status' => $status,
            ]);

            // Đồng bộ trạng thái đơn đặt phòng tương ứng
            if ($status === 'completed' && $lockedBooking->status !== 'completed') {
                $lockedBooking->update([
                    'status' => 'confirmed',
                ]);
            } elseif ($status === 'refunded') {
                $lockedBooking->update([
                    'status' => 'cancelled',
                ]);
            }

            return $payment;
        }, 5);
    }

    /**
     * Lấy danh sách giao dịch thanh toán phục vụ quản trị (phân trang, lọc trạng thái, tìm kiếm).
     */
    public function getAllPayments(?string $status = null, ?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Payment::with(['booking.user', 'booking.roomType.destination'])
            ->latest('id');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhere('payment_method', 'like', "%{$search}%")
                    ->orWhereHas('booking', function ($bq) use ($search) {
                        $bq->where('booking_code', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($uq) use ($search) {
                                $uq->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                    });
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Lấy danh sách các giao dịch thanh toán thuộc về một đơn đặt phòng.
     */
    public function getPaymentsByBooking(int $bookingId): Collection
    {
        return Payment::where('booking_id', $bookingId)
            ->latest('id')
            ->get();
    }
}
