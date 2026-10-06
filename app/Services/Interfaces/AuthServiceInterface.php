<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Đăng ký người dùng mới vào hệ thống.
     *
     * @param array<string, mixed> $data Dữ liệu người dùng đã được xác thực
     * @return User
     */
    public function registerUser(array $data): User;

    /**
     * Xác thực email cho tài khoản người dùng.
     *
     * @param User $user
     * @return bool
     */
    public function verifyEmail(User $user): bool;

    /**
     * Gửi lại liên kết xác thực email cho người dùng.
     *
     * @param User $user
     * @return void
     */
    public function resendVerificationEmail(User $user): void;
}
