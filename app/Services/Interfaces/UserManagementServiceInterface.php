<?php

namespace App\Services\Interfaces;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserManagementServiceInterface
{
    /**
     * Lấy danh sách người dùng kèm bộ lọc và phân trang.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsers(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Lấy thông tin chi tiết một người dùng theo ID.
     *
     * @param int $userId
     * @return User|null
     */
    public function getUserDetails(int $userId): ?User;

    /**
     * Tạo mới người dùng từ bảng quản trị.
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User;

    /**
     * Cập nhật thông tin người dùng.
     *
     * @param int $userId
     * @param array $data
     * @return User
     */
    public function updateUser(int $userId, array $data): User;

    /**
     * Khóa hoặc mở khóa tài khoản người dùng (Bảo vệ không cho phép khóa Admin).
     *
     * @param int $userId
     * @return User
     */
    public function toggleUserStatus(int $userId): User;

    /**
     * Lấy số liệu thống kê tổng hợp về người dùng.
     *
     * @return array
     */
    public function getUserCounts(): array;
}
