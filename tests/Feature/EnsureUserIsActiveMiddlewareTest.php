<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureUserIsActiveMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Khách chưa đăng nhập khi truy cập tuyến bảo vệ sẽ bị chuyển hướng đến trang đăng nhập.
     */
    public function test_guest_accessing_protected_route_is_redirected_to_login(): void
    {
        $response = $this->get(route('trips.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Khách gửi yêu cầu JSON khi chưa đăng nhập sẽ nhận phản hồi HTTP 401 Unauthenticated.
     */
    public function test_guest_json_request_receives_401_unauthenticated(): void
    {
        $response = $this->getJson(route('trips.index'));

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Người dùng đang hoạt động (is_active = true) truy cập các tuyến bảo vệ thành công.
     */
    public function test_active_user_can_access_protected_routes(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('trips.index'));

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Người dùng bị khóa (is_active = false) sẽ bị tự động đăng xuất và chuyển hướng về trang đăng nhập kèm thông báo.
     */
    public function test_inactive_user_is_logged_out_and_redirected_to_login(): void
    {
        $inactiveUser = User::factory()->create([
            'is_active' => false,
        ]);

        $response = $this->actingAs($inactiveUser)->get(route('trips.index'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.');
        $this->assertGuest();
    }

    /**
     * Yêu cầu JSON từ tài khoản bị khóa (is_active = false) nhận HTTP 403 Forbidden và bị đăng xuất.
     */
    public function test_inactive_user_json_request_is_logged_out_and_receives_403_forbidden(): void
    {
        $inactiveUser = User::factory()->create([
            'is_active' => false,
        ]);

        $response = $this->actingAs($inactiveUser)->getJson(route('trips.index'));

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
            ]);

        $this->assertGuest();
    }

    /**
     * Kiểm thử phương thức tiện ích isActive trên User model.
     */
    public function test_user_is_active_helper_method(): void
    {
        $activeUser = new User(['is_active' => true]);
        $inactiveUser = new User(['is_active' => false]);

        $this->assertTrue($activeUser->isActive());
        $this->assertFalse($inactiveUser->isActive());
    }
}
