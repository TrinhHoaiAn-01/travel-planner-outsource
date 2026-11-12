<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Xác định người dùng có quyền xem đánh giá hay không.
     * Quy tắc BR-07: Review đã duyệt hoặc do chính mình tạo hoặc là Admin mới được xem.
     */
    public function view(User $user, Review $review): bool
    {
        if ($review->status === Review::STATUS_APPROVED) {
            return true;
        }

        return (int) $user->id === (int) $review->user_id || $user->isAdmin();
    }

    /**
     * Xác định người dùng có quyền tạo đánh giá mới hay không.
     * Quy tắc BR-01: Người dùng phải đăng nhập và tài khoản hoạt động.
     */
    public function create(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Xác định người dùng có quyền chỉnh sửa đánh giá hay không.
     * Quy tắc BR-06: User chỉ được sửa Review do chính mình tạo (hoặc Admin quản trị).
     */
    public function update(User $user, Review $review): bool
    {
        return (int) $user->id === (int) $review->user_id || $user->isAdmin();
    }

    /**
     * Xác định người dùng có quyền xóa đánh giá hay không.
     * Quy tắc BR-06 & BR-14: User chỉ được xóa Review do chính mình tạo hoặc Admin có quyền xóa.
     */
    public function delete(User $user, Review $review): bool
    {
        return (int) $user->id === (int) $review->user_id || $user->isAdmin();
    }

    /**
     * Xác định quyền kiểm duyệt đánh giá (chỉ dành cho Admin).
     * Quy tắc BR-14: Admin có quyền duyệt, ẩn hoặc xóa Review.
     */
    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }
}
