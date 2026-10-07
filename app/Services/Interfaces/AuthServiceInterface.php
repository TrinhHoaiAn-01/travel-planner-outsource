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

    /**
     * Xác thực thông tin đăng nhập và tạo phiên làm việc cho người dùng.
     *
     * @param array<string, mixed> $credentials Thông tin đăng nhập gồm email và password
     * @param bool $remember Tùy chọn ghi nhớ đăng nhập
     * @return bool
     */
    public function authenticate(array $credentials, bool $remember = false): bool;

    /**
     * Đăng xuất người dùng hiện tại và vô hiệu hóa phiên làm việc.
     *
     * @return void
     */
    public function logoutUser(): void;

    /**
     * Tạo mã CAPTCHA ngẫu nhiên mới và lưu vào session.
     *
     * @return string
     */
    public function refreshCaptcha(): string;

    /**
     * Kiểm tra tính chính xác của mã CAPTCHA người dùng nhập.
     *
     * @param string $inputCaptcha
     * @return bool
     */
    public function validateCaptcha(string $inputCaptcha): bool;

    /**
     * Gửi liên kết đặt lại mật khẩu cho tài khoản người dùng qua email.
     *
     * @param array<string, mixed> $data Mảng chứa email người dùng
     * @return string Trạng thái hoặc thông báo kết quả
     */
    public function sendPasswordResetLink(array $data): string;

    /**
     * Đặt lại mật khẩu mới cho tài khoản người dùng bằng token hợp lệ.
     *
     * @param array<string, mixed> $data Mảng chứa token, email, password, password_confirmation
     * @return string Trạng thái hoặc thông báo kết quả
     */
    public function resetPassword(array $data): string;
}
