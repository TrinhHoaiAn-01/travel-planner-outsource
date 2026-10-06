<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Khách vãng lai có thể mở giao diện đăng nhập và thấy đầy đủ các thành phần theo thiết kế.
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);

        // Tiêu đề và lời chào
        $response->assertSee('Đăng nhập');
        $response->assertSee('Chào mừng bạn quay trở lại');

        // Các trường form
        $response->assertSee('Email');
        $response->assertSee('Mật khẩu');
        $response->assertSee('CAPTCHA');
        $response->assertSee('Nhập mã CAPTCHA');

        // Các tùy chọn và nút
        $response->assertSee('Ghi nhớ đăng nhập');
        $response->assertSee('Quên mật khẩu?');
        $response->assertSee('Đăng nhập với Facebook');
        $response->assertSee('Đăng nhập với Google');
        $response->assertSee('Chưa có tài khoản?');
        $response->assertSee('Đăng ký ngay');
    }

    /**
     * Người dùng đã đăng nhập sẽ bị chuyển hướng nếu truy cập trang đăng nhập.
     */
    public function test_authenticated_user_is_redirected_away_from_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertStatus(302);
    }

    /**
     * Đăng nhập thành công với thông tin hợp lệ và mã CAPTCHA chính xác.
     */
    public function test_user_can_authenticate_with_valid_credentials_and_correct_captcha(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('Secret@123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->withSession(['auth_captcha' => '7KX9P'])->post('/login', [
            'email' => 'user@example.com',
            'password' => 'Secret@123',
            'captcha' => '7KX9P',
            'remember' => '1',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Quản trị viên sau khi đăng nhập được tự động chuyển hướng đến Admin Dashboard.
     */
    public function test_admin_user_is_redirected_to_admin_dashboard_after_login(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('AdminPass@123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->withSession(['auth_captcha' => 'ADMN9'])->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'AdminPass@123',
            'captcha' => 'ADMN9',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * Báo lỗi khi mã CAPTCHA không chính xác theo quy chuẩn Bảng 6.
     */
    public function test_user_cannot_login_with_incorrect_captcha(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('Secret@123'),
        ]);

        $response = $this->from('/login')
            ->withSession(['auth_captcha' => '7KX9P'])
            ->post('/login', [
                'email' => 'user@example.com',
                'password' => 'Secret@123',
                'captcha' => 'WRONG',
            ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'captcha' => 'Mã CAPTCHA không chính xác.',
        ]);
        $this->assertGuest();
    }

    /**
     * Báo lỗi khi định dạng email không hợp lệ.
     */
    public function test_user_cannot_login_with_invalid_email_format(): void
    {
        $response = $this->from('/login')
            ->withSession(['auth_captcha' => '7KX9P'])
            ->post('/login', [
                'email' => 'invalid-email-address',
                'password' => 'Secret@123',
                'captcha' => '7KX9P',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'email' => 'Vui lòng nhập email hợp lệ.',
        ]);
        $this->assertGuest();
    }

    /**
     * Báo lỗi khi trường mật khẩu để trống.
     */
    public function test_user_cannot_login_with_empty_password(): void
    {
        $response = $this->from('/login')
            ->withSession(['auth_captcha' => '7KX9P'])
            ->post('/login', [
                'email' => 'user@example.com',
                'password' => '',
                'captcha' => '7KX9P',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'password' => 'Vui lòng nhập mật khẩu.',
        ]);
        $this->assertGuest();
    }

    /**
     * Báo lỗi khi sai mật khẩu.
     */
    public function test_user_cannot_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('CorrectPassword'),
        ]);

        $response = $this->from('/login')
            ->withSession(['auth_captcha' => '7KX9P'])
            ->post('/login', [
                'email' => 'user@example.com',
                'password' => 'WrongPassword',
                'captcha' => '7KX9P',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ]);
        $this->assertGuest();
    }

    /**
     * Từ chối đăng nhập khi tài khoản bị khóa (is_active = false) và hiển thị thông báo rõ ràng.
     */
    public function test_inactive_or_locked_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'locked@example.com',
            'password' => Hash::make('Secret@123'),
            'is_active' => false,
        ]);

        $response = $this->from('/login')
            ->withSession(['auth_captcha' => '7KX9P'])
            ->post('/login', [
                'email' => 'locked@example.com',
                'password' => 'Secret@123',
                'captcha' => '7KX9P',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
        ]);
        $this->assertGuest();
    }

    /**
     * Làm mới mã CAPTCHA qua yêu cầu AJAX thành công và trả về mã mới.
     */
    public function test_captcha_can_be_refreshed_via_ajax(): void
    {
        $response = $this->postJson('/captcha/refresh');

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'captcha']);
        $this->assertSame(5, strlen($response->json('captcha')));
        $this->assertSame($response->json('captcha'), session('auth_captcha'));
    }

    /**
     * Người dùng đăng xuất thành công và kết thúc phiên làm việc.
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
