<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserManagementService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserManagementService();
    }

    /**
     * Kiểm thử lấy danh sách người dùng có phân trang và lọc theo từ khóa, vai trò.
     */
    public function test_get_users_returns_paginated_users_and_filters_by_search_and_role(): void
    {
        User::factory()->create([
            'name' => 'Nguyễn Bảo Ngọc',
            'email' => 'ngoc.travel@travelplanner.com',
            'role' => 'user',
        ]);

        User::factory()->create([
            'name' => 'Hệ Thống Admin',
            'email' => 'admin@travelplanner.com',
            'role' => 'admin',
        ]);

        $searchResult = $this->userService->getUsers(['search' => 'Bảo Ngọc']);
        $this->assertSame(1, $searchResult->total());
        $this->assertSame('Nguyễn Bảo Ngọc', $searchResult->first()->name);

        $roleResult = $this->userService->getUsers(['role' => 'admin']);
        $this->assertSame(1, $roleResult->total());
        $this->assertSame('Hệ Thống Admin', $roleResult->first()->name);
    }

    /**
     * Kiểm thử tạo người dùng mới tự động mã hóa mật khẩu.
     */
    public function test_create_user_hashes_password_and_sets_attributes(): void
    {
        $user = $this->userService->createUser([
            'name' => 'Trần Hoàng Long',
            'email' => 'long.tran@travelplanner.com',
            'password' => 'secret12345',
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Trần Hoàng Long',
            'email' => 'long.tran@travelplanner.com',
            'role' => 'user',
        ]);
        $this->assertTrue(Hash::check('secret12345', $user->password));
    }

    /**
     * Kiểm thử cập nhật thông tin người dùng.
     */
    public function test_update_user_updates_attributes(): void
    {
        $user = User::factory()->create([
            'name' => 'Lê Thu Hà',
            'email' => 'ha.le@travelplanner.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        $updated = $this->userService->updateUser($user->id, [
            'name' => 'Lê Thu Hà (Đã đổi tên)',
            'is_active' => false,
        ]);

        $this->assertSame('Lê Thu Hà (Đã đổi tên)', $updated->name);
        $this->assertFalse($updated->is_active);
    }

    /**
     * Kiểm thử khóa/mở khóa tài khoản thành viên (Member).
     */
    public function test_toggle_user_status_toggles_is_active_for_member(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $locked = $this->userService->toggleUserStatus($user->id);
        $this->assertFalse($locked->is_active);

        $unlocked = $this->userService->toggleUserStatus($user->id);
        $this->assertTrue($unlocked->is_active);
    }

    /**
     * Kiểm thử an toàn: Tuyệt đối không cho phép khóa tài khoản Admin.
     */
    public function test_toggle_user_status_throws_validation_exception_for_admin(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);
        $this->userService->toggleUserStatus($admin->id);
    }

    /**
     * Kiểm thử lấy số lượng thống kê người dùng.
     */
    public function test_get_user_counts_returns_correct_statistics(): void
    {
        User::factory()->create(['role' => 'admin', 'is_active' => true]);
        User::factory()->create(['role' => 'user', 'is_active' => true]);
        User::factory()->create(['role' => 'user', 'is_active' => false]);

        $counts = $this->userService->getUserCounts();
        $this->assertSame(3, $counts['total']);
        $this->assertSame(2, $counts['active']);
        $this->assertSame(1, $counts['disabled']);
        $this->assertSame(1, $counts['admins']);
        $this->assertSame(2, $counts['members']);
    }
}
