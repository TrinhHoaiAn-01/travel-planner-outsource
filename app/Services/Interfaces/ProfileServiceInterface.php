<?php

namespace App\Services\Interfaces;

use App\Models\User;
use Illuminate\Http\UploadedFile;

interface ProfileServiceInterface
{
    /**
     * Lấy thông tin hồ sơ của người dùng cùng các chỉ số thống kê (chuyến đi, yêu thích, đánh giá).
     *
     * @param User $user
     * @return array<string, mixed>
     */
    public function getProfile(User $user): array;

    /**
     * Cập nhật thông tin hồ sơ cá nhân của người dùng.
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User;

    /**
     * Cập nhật ảnh đại diện của người dùng (từ tệp tải lên hoặc đường dẫn URL).
     *
     * @param User $user
     * @param UploadedFile|string|null $avatar
     * @return string|null
     */
    public function updateAvatar(User $user, UploadedFile|string|null $avatar): ?string;

    /**
     * Thay đổi mật khẩu của tài khoản người dùng đang đăng nhập.
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @return bool
     */
    public function changePassword(User $user, array $data): bool;
}
