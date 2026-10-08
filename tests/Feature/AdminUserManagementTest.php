<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@travelplanner.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'name' => 'Thành Viên',
            'email' => 'user@travelplanner.test',
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    /**
     * Khách vãng lai chưa đăng nhập bị chuyển hướng về trang đăng nhập.
     */
    public function test_guest_cannot_access_admin_users(): void
    {
        $response = $this->get(route('admin.users.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng vai trò thông thường (user) không có quyền truy cập trang quản trị người dùng (HTTP 403).
     */
    public function test_regular_user_cannot_access_admin_users(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.users.index'));
        $response->assertForbidden();
    }

    /**
     * Quản trị viên (Admin) truy cập trang quản lý người dùng thành công và thấy giao diện tái hiện chuẩn ảnh 100%.
     */
    public function test_admin_can_view_users_index_page_matching_screenshot(): void
    {
        $this->seed(AdminUserDemoSeeder::class);

        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Quản Lý Người Dùng');
        $response->assertSee('+ Thêm Người Dùng');
        $response->assertSee('Admin');
        $response->assertSee('Người dùng');
        $response->assertSee('Tìm kiếm theo tên hoặc email...');
        $response->assertSee('Tất cả vai trò');
        $response->assertSee('Nguyễn Bảo Ngọc');
        $response->assertSee('ngoc.travel@travelplanner.com');
        $response->assertSee('Trần Hoàng Long');
        $response->assertSee('long.tran@travelplanner.com');
        $response->assertSee('Hệ Thống Admin');
        $response->assertSee('admin@travelplanner.com');
        $response->assertSee('Lê Thu Hà (Tạm khóa)');
        $response->assertSee('ha.le@travelplanner.com');
        $response->assertSee('Administrator');
        $response->assertSee('Member');
        $response->assertSee('Bảo vệ');
        $response->assertSee('Khóa');
        $response->assertSee('Mở khóa');
        $response->assertSee('Hoạt động (Active)');
        $response->assertSee('Đã khóa (Disabled)');
    }

    /**
     * Admin thêm mới tài khoản người dùng thành công.
     */
    public function test_admin_can_create_new_user_successfully(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Phạm Quốc Cường',
            'email' => 'cuong.pham@travelplanner.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Phạm Quốc Cường',
            'email' => 'cuong.pham@travelplanner.com',
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    /**
     * Không thể tạo người dùng với email đã tồn tại.
     */
    public function test_cannot_create_user_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'trung.email@travelplanner.com',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Người Dùng Khác',
            'email' => 'trung.email@travelplanner.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Không thể tạo người dùng khi xác nhận mật khẩu không trùng khớp.
     */
    public function test_cannot_create_user_when_password_confirmation_fails(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Sai Mật Khẩu',
            'email' => 'saimatkhau@travelplanner.com',
            'password' => 'password123',
            'password_confirmation' => 'khongkhop123',
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Admin cập nhật thông tin người dùng thành công.
     */
    public function test_admin_can_update_user_information(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Tên Cũ',
            'email' => 'tencu@travelplanner.com',
            'role' => 'user',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $targetUser->id), [
            'name' => 'Tên Đã Đổi',
            'email' => 'tencu@travelplanner.com',
            'role' => 'user',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Tên Đã Đổi',
        ]);
    }

    /**
     * Admin có thể khóa và mở khóa tài khoản thành viên (Member).
     */
    public function test_admin_can_lock_and_unlock_member_account(): void
    {
        $member = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        // Thao tác Khóa
        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle-status', $member->id));
        $response->assertRedirect(route('admin.users.index'));
        $this->assertFalse($member->fresh()->is_active);

        // Thao tác Mở khóa
        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle-status', $member->id));
        $response->assertRedirect(route('admin.users.index'));
        $this->assertTrue($member->fresh()->is_active);
    }

    /**
     * Bảo vệ an toàn: Admin không thể tự khóa tài khoản Admin.
     */
    public function test_admin_cannot_lock_admin_account(): void
    {
        $otherAdmin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle-status', $otherAdmin->id));
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');
        $this->assertTrue($otherAdmin->fresh()->is_active);
    }

    /**
     * Tìm kiếm và lọc danh sách người dùng theo từ khóa và vai trò.
     */
    public function test_admin_can_filter_users_by_search_and_role(): void
    {
        User::factory()->create(['name' => 'Trần Văn A', 'email' => 'a.tran@test.com', 'role' => 'user']);
        User::factory()->create(['name' => 'Nguyễn Văn B', 'email' => 'b.nguyen@test.com', 'role' => 'admin']);

        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'Trần Văn']));
        $response->assertOk();
        $response->assertSee('Trần Văn A');
        $response->assertDontSee('Nguyễn Văn B');

        $roleResponse = $this->actingAs($this->admin)->get(route('admin.users.index', ['role' => 'admin']));
        $roleResponse->assertOk();
        $roleResponse->assertSee('Nguyễn Văn B');
        $roleResponse->assertDontSee('Trần Văn A');
    }
}
