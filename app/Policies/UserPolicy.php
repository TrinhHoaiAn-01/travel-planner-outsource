<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Xác định xem người dùng hiện tại có quyền xem hồ sơ của tài khoản mục tiêu hay không.
     * Quy tắc BR-02: Mỗi tài khoản chỉ được quản lý thông tin hồ sơ của chính tài khoản đó (hoặc quản trị viên).
     */
    public function view(User $currentUser, User $targetUser): bool
    {
        return $currentUser->id === $targetUser->id || $currentUser->isAdmin();
    }

    /**
     * Xác định xem người dùng hiện tại có quyền cập nhật hồ sơ của tài khoản mục tiêu hay không.
     * Quy tắc BR-02: Mỗi tài khoản chỉ được quản lý thông tin hồ sơ và mật khẩu của chính tài khoản đó.
     */
    public function update(User $currentUser, User $targetUser): bool
    {
        return $currentUser->id === $targetUser->id;
    }

    /**
     * Xác định xem người dùng hiện tại có quyền đổi mật khẩu của tài khoản mục tiêu hay không.
     */
    public function changePassword(User $currentUser, User $targetUser): bool
    {
        return $currentUser->id === $targetUser->id;
    }

    /**
     * Xác định quyền quản lý danh sách người dùng hệ thống (Admin).
     */
    public function viewAny(User $currentUser): bool
    {
        return $currentUser->isAdmin();
    }

    /**
     * Xác định quyền tạo tài khoản người dùng mới (Admin).
     */
    public function create(User $currentUser): bool
    {
        return $currentUser->isAdmin();
    }

    /**
     * Xác định quyền xóa người dùng (Admin, không thể tự xóa chính mình).
     */
    public function delete(User $currentUser, User $targetUser): bool
    {
        return $currentUser->isAdmin() && $currentUser->id !== $targetUser->id;
    }

    /**
     * Xác định quyền khóa/mở khóa tài khoản (Admin, không thể tự khóa chính mình).
     */
    public function toggleStatus(User $currentUser, User $targetUser): bool
    {
        return $currentUser->isAdmin() && $currentUser->id !== $targetUser->id;
    }

    /**
     * Xác định quyền quản trị người dùng tổng quát.
     */
    public function manage(User $currentUser): bool
    {
        return $currentUser->isAdmin();
    }
}
