<?php

namespace App\Services;

use App\Models\User;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    /**
     * Đăng ký tài khoản người dùng mới vào hệ thống.
     *
     * @param array<string, mixed> $data Dữ liệu đã được kiểm thực qua RegisterRequest
     * @return User
     */
    public function registerUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Chuẩn hóa và làm sạch dữ liệu đầu vào (cắt bỏ khoảng trắng thừa)
            $cleanedName = trim((string) $data['name']);
            $cleanedEmail = strtolower(trim((string) $data['email']));

            // Tạo người dùng mới với vai trò mặc định là user và trạng thái hoạt động
            // Tuyệt đối không cho phép người dùng tự cấp quyền admin khi đăng ký
            $user = User::create([
                'name' => $cleanedName,
                'email' => $cleanedEmail,
                'password' => Hash::make($data['password']),
                'role' => 'user',
                'is_active' => true,
            ]);

            // Kích hoạt sự kiện Registered để gửi email thông báo xác thực cho tài khoản
            event(new Registered($user));

            return $user;
        });
    }

    /**
     * Xác thực email cho người dùng nếu chưa được xác thực.
     *
     * @param User $user
     * @return bool
     */
    public function verifyEmail(User $user): bool
    {
        // Nếu tài khoản đã xác thực email trước đó thì không cần xử lý lại
        if ($user->hasVerifiedEmail()) {
            return true;
        }

        // Đánh dấu email đã được xác thực và kích hoạt sự kiện Verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            return true;
        }

        return false;
    }

    /**
     * Gửi lại liên kết xác thực email cho người dùng.
     *
     * @param User $user
     * @return void
     */
    public function resendVerificationEmail(User $user): void
    {
        // Chỉ gửi lại email nếu tài khoản chưa được xác thực
        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
    }
}
