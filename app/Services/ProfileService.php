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
        $avatar = $data['avatar'] ?? $data['avatar_url'] ?? null;

        return $this->saveProfile($user, $avatar, $data['edit_version'], function (User $user) use ($data): void {
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

        });
    }

    /**
     * Cập nhật ảnh đại diện của người dùng (từ tệp tải lên hoặc đường dẫn URL).
     *
     * @param User $user
     * @param UploadedFile|string|null $avatar
     * @return string|null
     */
    public function updateAvatar(User $user, UploadedFile|string|null $avatar, ?string $version = null): ?string
    {
        if ($avatar === null || $avatar === '') {
            return null;
        }

        $updated = $this->saveProfile($user, $avatar, $version);
        $user->setRawAttributes($updated->getAttributes(), true);

        return $updated->avatar;
    }

    private function saveProfile(User $user, UploadedFile|string|null $avatar, ?string $version, ?\Closure $change = null): User
    {
        $newPath = null;
        $disk = Storage::disk('public');

        try {
            return DB::transaction(function () use ($user, $avatar, $version, $change, $disk, &$newPath) {
                $current = User::query()->lockForUpdate()->findOrFail($user->id);
                if ($version !== null) {
                    $current->requireEditVersion($version);
                }

                if ($avatar instanceof UploadedFile) {
                    $fileName = 'avatar_' . $current->id . '_' . Str::uuid() . '.' . $avatar->extension();
                    $newPath = $avatar->storeAs('avatars', $fileName, 'public');
                    if (! $newPath) {
                        throw ValidationException::withMessages(['avatar' => 'Không thể lưu ảnh đại diện. Vui lòng thử lại.']);
                    }

                    $previous = $current->avatar;
                    $current->avatar = '/storage/' . $newPath;
                    if ($previous && preg_match('#^/storage/(avatars/avatar_' . $current->id . '_[A-Za-z0-9_-]+\.[A-Za-z0-9]+)$#D', $previous, $matches)) {
                        DB::afterCommit(fn () => rescue(fn () => $disk->delete($matches[1])));
                    }
                } elseif (is_string($avatar) && $avatar !== '') {
                    $current->avatar = trim($avatar);
                }

                $change?->__invoke($current);
                $current->renewEditVersion();
                $current->save();

                return $current;
            });
        } catch (\Throwable $exception) {
            if ($newPath) {
                $disk->delete($newPath);
            }

            throw $exception;
        }
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

        // Ghi nhật ký đổi mật khẩu thành công (không lưu mật khẩu vào metadata)
        app(\App\Services\Interfaces\ActivityLogServiceInterface::class)->logActivity(
            $user->id,
            'password_change',
            'User',
            $user->id
        );

        return true;
    }
}
