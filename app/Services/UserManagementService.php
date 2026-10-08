<?php

namespace App\Services;

use App\Models\User;
use App\Services\Interfaces\UserManagementServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserManagementService implements UserManagementServiceInterface
{
    /**
     * Lấy danh sách người dùng kèm bộ lọc và phân trang.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = User::query();

        // Lọc theo từ khóa tìm kiếm (tên hoặc email)
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Lọc theo vai trò (admin hoặc user)
        if (! empty($filters['role']) && $filters['role'] !== 'all') {
            $query->where('role', $filters['role']);
        }

        // Lọc theo trạng thái
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $query->where('is_active', (bool) $filters['status']);
        }

        return $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();
    }

    /**
     * Lấy thông tin chi tiết một người dùng theo ID.
     *
     * @param int $userId
     * @return User|null
     */
    public function getUserDetails(int $userId): ?User
    {
        return User::find($userId);
    }

    /**
     * Tạo mới người dùng từ bảng quản trị.
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            return User::create([
                'name' => trim($data['name']),
                'email' => strtolower(trim($data['email'])),
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'user',
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
                'email_verified_at' => now(),
            ]);
        });
    }

    /**
     * Cập nhật thông tin người dùng.
     *
     * @param int $userId
     * @param array $data
     * @return User
     */
    public function updateUser(int $userId, array $data): User
    {
        return DB::transaction(function () use ($userId, $data) {
            $user = User::findOrFail($userId);

            $updateData = [];

            if (isset($data['name'])) {
                $updateData['name'] = trim($data['name']);
            }

            if (isset($data['email'])) {
                $updateData['email'] = strtolower(trim($data['email']));
            }

            if (! empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            if (isset($data['role'])) {
                $updateData['role'] = $data['role'];
            }

            if (isset($data['is_active'])) {
                // Không cho phép khóa tài khoản admin
                if ($user->role === 'admin' && ! (bool) $data['is_active']) {
                    throw ValidationException::withMessages([
                        'is_active' => 'Không thể vô hiệu hóa tài khoản Quản trị viên (Administrator).',
                    ]);
                }
                $updateData['is_active'] = (bool) $data['is_active'];
            }

            $user->update($updateData);

            return $user;
        });
    }

    /**
     * Khóa hoặc mở khóa tài khoản người dùng (Bảo vệ không cho phép khóa Admin).
     *
     * @param int $userId
     * @return User
     * @throws ValidationException
     */
    public function toggleUserStatus(int $userId): User
    {
        return DB::transaction(function () use ($userId) {
            $user = User::findOrFail($userId);

            // Bảo vệ an toàn: Tuyệt đối không cho phép khóa tài khoản Admin
            if ($user->role === 'admin') {
                throw ValidationException::withMessages([
                    'status' => 'Tài khoản Quản trị viên được bảo vệ đặc biệt, không thể thực hiện thao tác khóa.',
                ]);
            }

            $user->is_active = ! $user->is_active;
            $user->save();

            return $user;
        });
    }

    /**
     * Lấy số liệu thống kê tổng hợp về người dùng.
     *
     * @return array
     */
    public function getUserCounts(): array
    {
        return [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'disabled' => User::where('is_active', false)->count(),
            'admins' => User::where('role', 'admin')->count(),
            'members' => User::where('role', 'user')->count(),
        ];
    }
}
