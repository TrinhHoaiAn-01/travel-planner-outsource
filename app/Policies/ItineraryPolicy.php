<?php

namespace App\Policies;

use App\Models\ItineraryItem;
use App\Models\Trip;
use App\Models\User;

class ItineraryPolicy
{
    /**
     * Xác định người dùng có quyền xem mục lịch trình hay không.
     * Quy tắc BR-04: User chỉ được xem lịch trình trong Trip mà User có quyền sở hữu.
     */
    public function view(User $user, ItineraryItem $itineraryItem): bool
    {
        return (int) $user->id === (int) $itineraryItem->trip->user_id || $user->isAdmin();
    }

    /**
     * Xác định người dùng có quyền thêm mục lịch trình vào chuyến đi hay không.
     * Quy tắc BR-04: Chỉ chủ sở hữu chuyến đi mới được thêm lịch trình.
     */
    public function create(User $user, Trip $trip): bool
    {
        return (int) $user->id === (int) $trip->user_id;
    }

    /**
     * Xác định người dùng có quyền cập nhật mục lịch trình hay không.
     * Quy tắc BR-04: User chỉ được sửa mục lịch trình thuộc chuyến đi của chính mình.
     */
    public function update(User $user, ItineraryItem $itineraryItem): bool
    {
        return (int) $user->id === (int) $itineraryItem->trip->user_id;
    }

    /**
     * Xác định người dùng có quyền xóa mục lịch trình hay không.
     * Quy tắc BR-04: User chỉ được xóa mục lịch trình thuộc chuyến đi của chính mình.
     */
    public function delete(User $user, ItineraryItem $itineraryItem): bool
    {
        return (int) $user->id === (int) $itineraryItem->trip->user_id;
    }
}
