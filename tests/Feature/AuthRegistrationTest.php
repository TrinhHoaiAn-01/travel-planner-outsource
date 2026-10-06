<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Khách vãng lai có thể mở giao diện đăng ký tài khoản và thấy đầy đủ các thành phần.
     */
    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);

        // Kiểm tra khối thông tin bên trái theo ảnh mẫu
        $response->assertSee('Lên kế hoạch');
        $response->assertSee('cho hành trình của bạn');
        $response->assertSee('Khám phá điểm đến, quản lý lịch trình và lưu lại những khoảnh khắc đáng nhớ.');
        $response->assertSee('Tìm kiếm điểm đến dễ dàng');
        $response->assertSee('Lập kế hoạch linh hoạt');
        $response->assertSee('Lưu giữ trải nghiệm yêu thích');

        // Kiểm tra biểu mẫu đăng ký bên phải
        $response->assertSee('Đăng ký tài khoản');
        $response->assertSee('Họ và tên');
        $response->assertSee('Email');
        $response->assertSee('Mật khẩu');
        $response->assertSee('Xác nhận mật khẩu');
        $response->assertSee('Đăng ký');
        $response->assertSee('Đã có tài khoản?');
        $response->assertSee('Đăng nhập');
    }

    /**
     * Người dùng đã đăng nhập sẽ bị chuyển hướng nếu truy cập trang đăng ký.
     */
    public function test_authenticated_user_cannot_view_registration_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertStatus(302);
    }

    /**
     * Đăng ký thành công với thông tin hợp lệ: băm mật khẩu, phân quyền mặc định user và chuyển hướng xác thực email.
     */
    public function test_user_can_register_with_valid_information(): void
    {
        Event::fake([Registered::class]);

        $response = $this->post('/register', [
            'name' => 'Nguyễn Hoài Nam',
            'email' => 'hoainam@example.com',
            'password' => 'Matkhau@123',
            'password_confirmation' => 'Matkhau@123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('verification.notice'));

        // Kiểm tra dữ liệu được tạo trong cơ sở dữ liệu
        $this->assertDatabaseHas('users', [
            'name' => 'Nguyễn Hoài Nam',
            'email' => 'hoainam@example.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        // Đảm bảo mật khẩu được băm an toàn
        $user = User::where('email', 'hoainam@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('Matkhau@123', $user->password));

        // Kiểm tra người dùng đã được đăng nhập
        $this->assertAuthenticatedAs($user);

        // Kiểm tra sự kiện Registered được kích hoạt
        Event::assertDispatched(Registered::class);
    }

    /**
     * Báo lỗi khi trường Họ và tên để trống hoặc chỉ chứa khoảng trắng.
     */
    public function test_user_cannot_register_with_blank_or_whitespace_name(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => '   ',
            'email' => 'valid@example.com',
            'password' => 'Matkhau@123',
            'password_confirmation' => 'Matkhau@123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'name' => 'Vui lòng nhập họ và tên.',
        ]);

        $this->assertDatabaseMissing('users', ['email' => 'valid@example.com']);
    }

    /**
     * Báo lỗi khi Họ và tên vượt quá 40 ký tự.
     */
    public function test_user_cannot_register_with_name_exceeding_40_characters(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => str_repeat('A', 41),
            'email' => 'valid@example.com',
            'password' => 'Matkhau@123',
            'password_confirmation' => 'Matkhau@123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'name' => 'Họ và tên không được vượt quá 40 ký tự.',
        ]);
    }

    /**
     * Báo lỗi khi định dạng email không hợp lệ.
     */
    public function test_user_cannot_register_with_invalid_email_format(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Trần Văn Nam',
            'email' => 'invalid-email-format',
            'password' => 'Matkhau@123',
            'password_confirmation' => 'Matkhau@123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'email' => 'Vui lòng nhập email hợp lệ.',
        ]);
    }

    /**
     * Báo lỗi khi email đã tồn tại trong hệ thống.
     */
    public function test_user_cannot_register_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Trần Văn Nam',
            'email' => 'existing@example.com',
            'password' => 'Matkhau@123',
            'password_confirmation' => 'Matkhau@123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'email' => 'Email này đã được sử dụng. Vui lòng sử dụng email khác.',
        ]);
    }

    /**
     * Báo lỗi khi mật khẩu ngắn hơn 8 ký tự.
     */
    public function test_user_cannot_register_with_password_shorter_than_8_characters(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Trần Văn Nam',
            'email' => 'nam@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'password' => 'Mật khẩu phải có ít nhất 8 ký tự.',
        ]);
    }

    /**
     * Báo lỗi khi xác nhận mật khẩu không khớp.
     */
    public function test_user_cannot_register_when_password_confirmation_does_not_match(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Trần Văn Nam',
            'email' => 'nam@example.com',
            'password' => 'Matkhau@123',
            'password_confirmation' => 'KhongKhop@123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'password_confirmation' => 'Mật khẩu xác nhận không khớp.',
        ]);
    }

    /**
     * Người đăng ký không thể tự cấp quyền Admin (bảo mật hệ thống).
     */
    public function test_registered_user_cannot_escalate_privilege_to_admin(): void
    {
        $this->post('/register', [
            'name' => 'Attacker User',
            'email' => 'attacker@example.com',
            'password' => 'Matkhau@123',
            'password_confirmation' => 'Matkhau@123',
            'role' => 'admin',
        ]);

        $user = User::where('email', 'attacker@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('user', $user->role);
    }

    /**
     * Người dùng chưa xác thực email có thể mở màn hình thông báo xác thực.
     */
    public function test_unverified_user_can_view_email_verification_notice(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);
        $response->assertSee('Xác thực tài khoản email');
        $response->assertSee('Gửi lại email xác thực');
    }

    /**
     * Người dùng có thể xác thực email thành công thông qua liên kết hợp lệ.
     */
    public function test_user_can_verify_email_with_valid_hash(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $hash = sha1($user->getEmailForVerification());

        $response = $this->actingAs($user)->get("/email/verify/{$user->id}/{$hash}");

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    /**
     * Từ chối xác thực email với mã băm không hợp lệ (mã lỗi 403 Forbidden).
     */
    public function test_user_cannot_verify_email_with_invalid_hash(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get("/email/verify/{$user->id}/invalid-hash-string");

        $response->assertStatus(403);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    /**
     * Người dùng có thể yêu cầu gửi lại email xác thực.
     */
    public function test_user_can_resend_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->post('/email/verification-notification');

        $response->assertStatus(302);
        $response->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
