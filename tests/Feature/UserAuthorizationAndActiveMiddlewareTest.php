<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UserAuthorizationAndActiveMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Đăng ký các tuyến tạm thời để kiểm thử RoleMiddleware
        Route::middleware(['web', 'role:admin'])->get('/test-role-admin', function () {
            return response()->json(['message' => 'Admin route accessible']);
        });

        Route::middleware(['web', 'role:user'])->get('/test-role-user', function () {
            return response()->json(['message' => 'User route accessible']);
        });

        Route::middleware(['web', 'role:admin,manager'])->get('/test-role-multiple', function () {
            return response()->json(['message' => 'Multiple roles route accessible']);
        });
    }

    /**
     * Khách chưa đăng nhập truy cập tuyến admin sẽ bị chuyển hướng đến trang đăng nhập.
     */
    public function test_guest_accessing_admin_dashboard_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Khách gửi yêu cầu JSON đến tuyến admin nhận phản hồi 401 Unauthenticated.
     */
    public function test_guest_json_request_to_admin_receives_401(): void
    {
        $response = $this->getJson(route('admin.dashboard'));

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Người dùng thông thường (role = user) truy cập Admin Dashboard bị chặn với HTTP 403 Forbidden.
     */
    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    /**
     * Người dùng thông thường gửi yêu cầu JSON đến tuyến Admin nhận 403 Forbidden.
     */
    public function test_regular_user_json_request_to_admin_receives_403(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson(route('admin.dashboard'));

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Bạn không có quyền truy cập vào khu vực quản trị hệ thống.',
            ]);
    }

    /**
     * Quản trị viên (role = admin, is_active = true) truy cập Admin Dashboard thành công với HTTP 200 OK.
     */
    public function test_admin_user_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    /**
     * Quản trị viên bị khóa (role = admin, is_active = false) truy cập Admin Dashboard sẽ bị đăng xuất và chuyển về login.
     */
    public function test_inactive_admin_user_is_logged_out_and_redirected(): void
    {
        $inactiveAdmin = User::factory()->create([
            'role' => 'admin',
            'is_active' => false,
        ]);

        $response = $this->actingAs($inactiveAdmin)->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.');
        $this->assertGuest();
    }

    /**
     * RoleMiddleware cho phép đúng vai trò được cấu hình.
     */
    public function test_role_middleware_allows_authorized_role(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->getJson('/test-role-admin')
            ->assertStatus(200)
            ->assertJson(['message' => 'Admin route accessible']);

        $this->actingAs($user)->getJson('/test-role-user')
            ->assertStatus(200)
            ->assertJson(['message' => 'User route accessible']);

        $this->actingAs($admin)->getJson('/test-role-multiple')
            ->assertStatus(200)
            ->assertJson(['message' => 'Multiple roles route accessible']);
    }

    /**
     * RoleMiddleware chặn người dùng có vai trò không phù hợp với HTTP 403 Forbidden.
     */
    public function test_role_middleware_forbids_unauthorized_role(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/test-role-admin');

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Bạn không có quyền thực hiện hành động này.',
            ]);
    }
}
