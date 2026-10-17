<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Khách chưa đăng nhập có thể truy cập màn hình Quên mật khẩu (Hình 7 / Image 1).
     */
    public function test_guest_can_view_forgot_password_page(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.forgot-password');
        $response->assertSee('Quên mật khẩu?');
        $response->assertSee('Nhập địa chỉ email và nhấn gửi để tiếp tục đặt lại mật khẩu mới.');
        $response->assertSee('Gửi liên kết đặt lại mật khẩu');
        $response->assertSee('Quay lại Đăng nhập');
    }

    /**
     * Người dùng đã đăng nhập bị chuyển hướng khi truy cập màn hình quên mật khẩu.
     */
    public function test_authenticated_user_is_redirected_from_forgot_password_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('password.request'));

        $response->assertRedirect(route('home'));
    }

    /**
     * Kiểm tra tính hợp lệ của trường email khi gửi yêu cầu quên mật khẩu.
     */
    public function test_forgot_password_validates_required_and_email_format(): void
    {
        // Kiểm tra để trống email
        $responseEmpty = $this->from(route('password.request'))->post(route('password.email'), [
            'email' => '',
        ]);
        $responseEmpty->assertRedirect(route('password.request'));
        $responseEmpty->assertSessionHasErrors(['email']);

        // Kiểm tra sai định dạng email
        $responseInvalid = $this->from(route('password.request'))->post(route('password.email'), [
            'email' => 'khong-phai-email',
        ]);
        $responseInvalid->assertRedirect(route('password.request'));
        $responseInvalid->assertSessionHasErrors(['email']);
    }

    /**
     * Gửi yêu cầu quên mật khẩu thành công cho email tồn tại trong hệ thống.
     */
    public function test_forgot_password_creates_token_and_returns_safe_message(): void
    {
        $user = User::factory()->create([
            'email' => 'ngoc.travel@travelplanner.com',
        ]);

        $response = $this->from(route('password.request'))->post(route('password.email'), [
            'email' => 'ngoc.travel@travelplanner.com',
        ]);

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHas('status', 'Nếu email được đăng ký, hướng dẫn đặt lại mật khẩu sẽ được gửi đến email của bạn.');

        // Kiểm tra token đặt lại mật khẩu đã được lưu trong cơ sở dữ liệu
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'ngoc.travel@travelplanner.com',
        ]);
    }

    /**
     * Chống tấn công dò tìm tài khoản (User Enumeration): hiển thị cùng thông báo chung cho email không tồn tại.
     */
    public function test_forgot_password_returns_generic_message_for_non_existent_email(): void
    {
        $response = $this->from(route('password.request'))->post(route('password.email'), [
            'email' => 'khongtontai@travelplanner.test',
        ]);

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHas('status', 'Nếu email được đăng ký, hướng dẫn đặt lại mật khẩu sẽ được gửi đến email của bạn.');
    }

    /**
     * Khách có thể xem màn hình Đặt lại mật khẩu mới với token hợp lệ (Hình 8 / Image 2).
     */
    public function test_guest_can_view_reset_password_page_with_token(): void
    {
        $response = $this->get(route('password.reset', [
            'token' => 'sample-token-123456',
            'email' => 'ngoc.travel@travelplanner.com',
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('auth.reset-password');
        $response->assertSee('Đặt lại mật khẩu mới');
        $response->assertSee('Cập nhật');
        $response->assertSee('Mật khẩu mới');
        $response->assertSee('Xác nhận mật khẩu mới');
    }

    /**
     * Kiểm tra tính hợp lệ dữ liệu đặt lại mật khẩu mới (min 8 ký tự, xác nhận khớp).
     */
    public function test_reset_password_validates_required_fields(): void
    {
        $response = $this->post(route('password.update'), [
            'token' => 'sample-token',
            'email' => 'user@travelplanner.test',
            'password' => '123',
            'password_confirmation' => '456',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * Đặt lại mật khẩu mới thành công khi cung cấp token và email chính xác.
     */
    public function test_reset_password_updates_user_password_successfully(): void
    {
        $user = User::factory()->create([
            'email' => 'ngoc.travel@travelplanner.com',
            'password' => Hash::make('oldpassword123'),
        ]);

        // Tạo token reset hợp lệ qua Password Broker
        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'ngoc.travel@travelplanner.com',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success', 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập với mật khẩu mới.');

        // Kiểm tra mật khẩu trong cơ sở dữ liệu đã được đổi sang mật khẩu mới
        $this->assertTrue(Hash::check('newpassword456', $user->fresh()->password));

        // Kiểm tra token đặt lại đã mất hiệu lực (bị xóa khỏi bảng)
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'ngoc.travel@travelplanner.com',
        ]);
    }

    /**
     * Đặt lại mật khẩu thất bại nếu mã token không hợp lệ hoặc đã hết hạn.
     */
    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'ngoc.travel@travelplanner.com',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->post(route('password.update'), [
            'token' => 'invalid-token-string',
            'email' => 'ngoc.travel@travelplanner.com',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertSessionHasErrors(['token']);
        $this->assertTrue(Hash::check('oldpassword123', $user->fresh()->password));
    }
}
