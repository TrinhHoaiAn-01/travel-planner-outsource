<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\Trip;
use App\Models\User;

class ExpensePolicy
{
    /**
     * Xác định người dùng có quyền xem danh sách khoản chi của chuyến đi hay không.
     * Quy tắc BR-01 & BR-04: User chỉ được xem chi phí của chuyến đi do mình sở hữu.
     */
    public function viewAny(User $user, Trip $trip): bool
    {
        return $user->id === $trip->user_id;
    }

    /**
     * Xác định người dùng có quyền xem chi tiết khoản chi hay không.
     * Quy tắc BR-04: Chi phí phải thuộc chuyến đi của người dùng.
     */
    public function view(User $user, Expense $expense): bool
    {
        return $user->id === $expense->trip->user_id;
    }

    /**
     * Xác định người dùng có quyền thêm khoản chi vào chuyến đi hay không.
     * Quy tắc BR-04: Chỉ chủ sở hữu chuyến đi mới được thêm chi phí.
     */
    public function create(User $user, Trip $trip): bool
    {
        return $user->id === $trip->user_id;
    }

    /**
     * Xác định người dùng có quyền chỉnh sửa khoản chi hay không.
     * Quy tắc BR-04: User chỉ được sửa khoản chi thuộc chuyến đi của chính mình.
     */
    public function update(User $user, Expense $expense): bool
    {
        return $user->id === $expense->trip->user_id;
    }

    /**
     * Xác định người dùng có quyền xóa khoản chi hay không.
     * Quy tắc BR-04: User chỉ được xóa khoản chi thuộc chuyến đi của chính mình.
     */
    public function delete(User $user, Expense $expense): bool
    {
        return $user->id === $expense->trip->user_id;
    }
}
