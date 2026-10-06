<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;

class TripPolicy
{
    /**
     * Xác định người dùng có quyền xem danh sách chuyến đi hay không.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Xác định người dùng có quyền xem chi tiết chuyến đi cụ thể hay không.
     * Quy tắc BR-03: User chỉ được xem/thao tác trên chuyến đi do chính mình sở hữu.
     */
    public function view(User $user, Trip $trip): bool
    {
        return $user->id === $trip->user_id;
    }

    /**
     * Xác định người dùng có quyền tạo chuyến đi mới hay không.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Xác định người dùng có quyền cập nhật chuyến đi hay không.
     * Quy tắc BR-03: User chỉ được sửa chuyến đi do chính mình sở hữu.
     */
    public function update(User $user, Trip $trip): bool
    {
        return $user->id === $trip->user_id;
    }

    /**
     * Xác định người dùng có quyền xóa chuyến đi hay không.
     * Quy tắc BR-03: User chỉ được xóa chuyến đi do chính mình sở hữu.
     */
    public function delete(User $user, Trip $trip): bool
    {
        return $user->id === $trip->user_id;
    }
}
