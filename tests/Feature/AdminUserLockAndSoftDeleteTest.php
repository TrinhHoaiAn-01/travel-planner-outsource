<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserLockAndSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Quản Trị Viên',
            'email' => 'admin.secure@travelplanner.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->member = User::factory()->create([
            'name' => 'Nguyễn Văn A',
            'email' => 'member.test@travelplanner.test',
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    /**
     * Quản trị viên có thể khóa tài khoản thành viên thông thường.
     */
    public function test_admin_can_lock_regular_user(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle-status', $this->member->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->member->refresh();
        $this->assertFalse((bool) $this->member->is_active);
    }

    /**
     * Quản trị viên có thể mở khóa tài khoản thành viên đang bị khóa.
     */
    public function test_admin_can_unlock_locked_user(): void
    {
        $this->member->update(['is_active' => false]);

        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle-status', $this->member->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->member->refresh();
        $this->assertTrue((bool) $this->member->is_active);
    }

    /**
     * Tài khoản bị khóa trong phiên sẽ bị middleware EnsureUserIsActive đăng xuất ngay lập tức.
     */
    public function test_locked_user_is_logged_out_by_middleware(): void
    {
        $this->member->update(['is_active' => false]);

        $response = $this->actingAs($this->member)->get(route('profile.show'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.');
        $this->assertGuest();
    }

    /**
     * Quản trị viên không thể tự khóa tài khoản của chính mình.
     */
    public function test_admin_cannot_lock_self(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle-status', $this->admin->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');

        $this->admin->refresh();
        $this->assertTrue((bool) $this->admin->is_active);
    }

    /**
     * Quản trị viên có thể xóa mềm (Soft Delete) tài khoản người dùng thành viên.
     */
    public function test_admin_can_soft_delete_member_user(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->member->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'Xóa mềm tài khoản người dùng thành công.');

        // Bản ghi vẫn tồn tại trong CSDL nhưng có deleted_at khác null
        $this->assertSoftDeleted('users', [
            'id' => $this->member->id,
            'email' => $this->member->email,
        ]);

        // Truy vấn thông thường không tìm thấy bản ghi đã xóa mềm
        $this->assertNull(User::find($this->member->id));

        // Truy vấn withTrashed vẫn tìm thấy bản ghi
        $this->assertNotNull(User::withTrashed()->find($this->member->id));
    }

    /**
     * Tài khoản đã xóa mềm không thể đăng nhập vào hệ thống.
     */
    public function test_soft_deleted_user_cannot_login(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'email' => 'deleted.user@travelplanner.test',
            'password' => bcrypt($password),
            'role' => 'user',
            'is_active' => true,
        ]);

        $user->delete(); // Soft delete

        $response = $this->post(route('login'), [
            'email' => 'deleted.user@travelplanner.test',
            'password' => $password,
            'captcha' => session('captcha_code') ?? '1234',
        ]);

        $this->assertGuest();
    }

    /**
     * Middleware EnsureUserIsActive chặn và đăng xuất tài khoản đã bị xóa mềm.
     */
    public function test_soft_deleted_user_with_active_session_is_blocked(): void
    {
        $user = User::factory()->create([
            'email' => 'session.user@travelplanner.test',
            'role' => 'user',
            'is_active' => true,
        ]);

        $user->delete(); // Xóa mềm

        $response = $this->actingAs($user)->get(route('trips.index'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Xóa mềm bảo toàn dữ liệu liên kết khóa ngoại (Trip, Booking, Review).
     */
    public function test_soft_delete_preserves_foreign_key_relations(): void
    {
        // Tạo chuyến đi của người dùng (trường name theo ERD)
        $trip = Trip::create([
            'user_id' => $this->member->id,
            'name' => 'Chuyến đi Đà Nẵng',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-05',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        // Thực hiện xóa mềm người dùng
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->member->id));
        $response->assertSessionHas('success');

        // Chuyến đi vẫn tồn tại an toàn trong cơ sở dữ liệu
        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'user_id' => $this->member->id,
            'name' => 'Chuyến đi Đà Nẵng',
        ]);
    }

    /**
     * Quản trị viên không thể tự xóa tài khoản của chính mình.
     */
    public function test_admin_cannot_soft_delete_self(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'Không thể tự xóa tài khoản của chính mình.');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Không thể xóa tài khoản Quản trị viên hoạt động cuối cùng của hệ thống.
     */
    public function test_admin_cannot_delete_last_active_admin(): void
    {
        // Chỉ có 1 admin trong hệ thống ($this->admin)
        $service = app(\App\Services\Interfaces\UserManagementServiceInterface::class);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->expectExceptionMessage('Hệ thống phải duy trì ít nhất một Quản trị viên đang hoạt động, không thể xóa tài khoản này.');

        // Giả lập một admin khác thực thi service để xóa admin cuối cùng
        $service->deleteUser($this->admin->id);
    }

    /**
     * Quản trị viên có thể khôi phục tài khoản người dùng đã bị xóa mềm.
     */
    public function test_admin_can_restore_soft_deleted_user(): void
    {
        $this->member->delete();
        $this->assertSoftDeleted('users', ['id' => $this->member->id]);

        $response = $this->actingAs($this->admin)->post(route('admin.users.restore', $this->member->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->member->refresh();
        $this->assertNull($this->member->deleted_at);
        $this->assertNotNull(User::find($this->member->id));
    }

    /**
     * Lọc danh sách người dùng theo trạng thái xóa mềm (trashed).
     */
    public function test_admin_can_filter_trashed_users(): void
    {
        $this->member->delete();

        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['status' => 'trashed']));

        $response->assertOk();
        $response->assertSee($this->member->name);
        $response->assertSee('Đã xóa mềm');
    }
}
