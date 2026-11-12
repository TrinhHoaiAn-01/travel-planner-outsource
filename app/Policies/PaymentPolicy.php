<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Xác định người dùng có quyền xem danh sách thanh toán hay không.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Xác định người dùng có quyền xem chi tiết giao dịch thanh toán hay không (BR-11, BR-12).
     */
    public function view(User $user, Payment $payment): bool
    {
        // Chủ sở hữu của Booking liên quan hoặc Admin hệ thống có quyền xem
        return (int) $user->id === (int) $payment->booking->user_id || $user->role === 'admin';
    }

    /**
     * Xác định quyền cập nhật trạng thái thanh toán (chỉ dành cho Admin - BR-13).
     */
    public function update(User $user, Payment $payment): bool
    {
        return $user->role === 'admin';
    }
}
