<?php

namespace App\Services;

use App\Models\User;
use App\Services\Interfaces\ProfileServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProfileService implements ProfileServiceInterface
{
    /**
     * Lấy thông tin hồ sơ của người dùng cùng các chỉ số thống kê (chuyến đi, yêu thích, đánh giá).
     *
     * @param User $user
     * @return array<string, mixed>
     */
    public function getProfile(User $user): array
    {
        // Đếm tổng số chuyến đi người dùng đã tạo
        $tripsCount = $user->trips()->count();

        // Đếm tổng số địa điểm yêu thích
        $favoritesCount = $user->favorites()->count();

        // Đếm tổng số đánh giá đã thực hiện
        $reviewsCount = $user->reviews()->count();

        return [
            'user' => $user,
            'tripsCount' => $tripsCount,
            'favoritesCount' => $favoritesCount,
            'reviewsCount' => $reviewsCount,
        ];
    }

    /**
     * Cập nhật thông tin hồ sơ cá nhân của người dùng.
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            // Xử lý tệp ảnh tải lên nếu có
            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $this->updateAvatar($user, $data['avatar']);
            } elseif (! empty($data['avatar_url'])) {
                $user->avatar = trim((string) $data['avatar_url']);
            }

            // Cập nhật họ tên
            if (isset($data['name'])) {
                $user->name = trim((string) $data['name']);
            }

            // Cập nhật email nếu có thay đổi
            if (isset($data['email'])) {
                $newEmail = strtolower(trim((string) $data['email']));
                if ($newEmail !== strtolower((string) $user->email)) {
                    $user->email = $newEmail;
                    // Nếu đổi email thì hủy trạng thái đã xác thực để yêu cầu xác thực lại
                    $user->email_verified_at = null;
                }
            }

            // Cập nhật số điện thoại
            if (array_key_exists('phone', $data)) {
                $user->phone = ! empty($data['phone']) ? trim((string) $data['phone']) : null;
            }

            // Cập nhật thành phố sinh sống
            if (array_key_exists('city', $data)) {
                $user->city = ! empty($data['city']) ? trim((string) $data['city']) : null;
            }

            // Cập nhật giới thiệu ngắn (bio)
            if (array_key_exists('bio', $data)) {
                $user->bio = ! empty($data['bio']) ? trim((string) $data['bio']) : null;
            }

            $user->save();

            return $user;
        });
    }

    /**
     * Cập nhật ảnh đại diện của người dùng (từ tệp tải lên hoặc đường dẫn URL).
     *
     * @param User $user
     * @param UploadedFile|string|null $avatar
     * @return string|null
     */
    public function updateAvatar(User $user, UploadedFile|string|null $avatar): ?string
    {
        if ($avatar instanceof UploadedFile) {
            // Đặt tên tệp ngẫu nhiên an toàn, chống ghi đè và path traversal
            $extension = $avatar->getClientOriginalExtension() ?: 'png';
            $fileName = 'avatar_' . $user->id . '_' . time() . '_' . Str::random(8) . '.' . $extension;

            // Xóa ảnh cũ nếu đã từng tải lên lưu trữ cục bộ
            if ($user->avatar && str_starts_with($user->avatar, '/storage/avatars/')) {
                $oldPath = str_replace('/storage/', '', $user->avatar);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Lưu tệp vào disk public
            $avatar->storeAs('avatars', $fileName, 'public');

            $user->avatar = '/storage/avatars/' . $fileName;
            $user->save();

            return $user->avatar;
        }

        if (is_string($avatar) && ! empty($avatar)) {
            $user->avatar = trim($avatar);
            $user->save();

            return $user->avatar;
        }

        return null;
    }

    /**
     * Thay đổi mật khẩu của tài khoản người dùng đang đăng nhập.
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @return bool
     * @throws ValidationException
     */
    public function changePassword(User $user, array $data): bool
    {
        // Kiểm tra tính chính xác của mật khẩu hiện tại
        if (! Hash::check((string) $data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Mật khẩu hiện tại không chính xác.',
            ]);
        }

        // Mã hóa và lưu mật khẩu mới
        $user->password = Hash::make((string) $data['password']);
        $user->save();

        return true;
    }
}
