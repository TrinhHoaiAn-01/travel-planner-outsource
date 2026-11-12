<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Xác định người dùng có quyền xem danh sách đơn đặt phòng hay không.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active ?? true;
    }

    /**
     * Xác định người dùng có quyền xem chi tiết đơn đặt phòng hay không (BR-11).
     */
    public function view(User $user, Booking $booking): bool
    {
        // Chủ sở hữu đơn hoặc Quản trị viên hệ thống có quyền xem
        return (int) $user->id === (int) $booking->user_id || $user->role === 'admin';
    }

    /**
     * Xác định người dùng có quyền hủy đơn đặt phòng hay không (BR-11).
     */
    public function cancel(User $user, Booking $booking): bool
    {
        // Chỉ chính chủ sở hữu đơn mới có quyền yêu cầu hủy
        if ((int) $user->id !== (int) $booking->user_id) {
            return false;
        }

        // Đơn đã hủy không thể hủy lại
        if ($booking->status === 'cancelled') {
            return false;
        }

        return true;
    }

    /**
     * Xác định người dùng có quyền thanh toán cho đơn đặt phòng hay không (BR-11, BR-21).
     */
    public function pay(User $user, Booking $booking): bool
    {
        // Chỉ chính chủ sở hữu đơn mới có quyền thanh toán
        if ((int) $user->id !== (int) $booking->user_id) {
            return false;
        }

        // Đơn đã hủy không được phép thanh toán
        if ($booking->status === 'cancelled') {
            return false;
        }

        return true;
    }
}
