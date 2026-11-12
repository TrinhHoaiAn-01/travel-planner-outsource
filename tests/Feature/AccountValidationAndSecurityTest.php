<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountValidationAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Quản Trị Viên',
            'email' => 'admin.sec@travelplanner.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->member = User::factory()->create([
            'name' => 'Thành Viên An Ninh',
            'email' => 'member.sec@travelplanner.test',
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    /**
     * Đăng ký tài khoản tự động loại bỏ thẻ XSS độc hại và chuẩn hóa chữ thường cho email.
     */
    public function test_registration_strips_xss_tags_and_normalizes_email(): void
    {
        $response = $this->post(route('register'), [
            'name' => '<script>alert("XSS")</script>Nguyễn An Ninh',
            'email' => '  User.XSS@TravelPlanner.TEST  ',
            'password' => 'MatKhauBaoMat@123',
            'password_confirmation' => 'MatKhauBaoMat@123',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'name' => 'Nguyễn An Ninh',
            'email' => 'user.xss@travelplanner.test',
            'role' => 'user',
        ]);

        $this->assertDatabaseMissing('users', [
            'name' => '<script>alert("XSS")</script>Nguyễn An Ninh',
        ]);
    }

    /**
     * Đăng ký tài khoản chặn đứng tấn công leo thang đặc quyền (Privilege Escalation).
     */
    public function test_registration_blocks_privilege_escalation(): void
    {
        $this->post(route('register'), [
            'name' => 'Attacker Escalation',
            'email' => 'attacker.sec@travelplanner.test',
            'password' => 'MatKhauBaoMat@123',
            'password_confirmation' => 'MatKhauBaoMat@123',
            'role' => 'admin',
            'is_active' => false,
        ]);

        $user = User::where('email', 'attacker.sec@travelplanner.test')->first();
        $this->assertNotNull($user);
        $this->assertSame('user', $user->role);
        $this->assertTrue((bool) $user->is_active);
    }

    /**
     * Cập nhật hồ sơ cá nhân ngăn chặn Parameter Tampering (không thể tự đổi vai trò admin hoặc is_active).
     */
    public function test_profile_update_blocks_parameter_tampering(): void
    {
        $response = $this->actingAs($this->member)->put(route('profile.update'), [
            'name' => 'Tên Mới Sau Kiểm Tra',
            'email' => 'member.sec@travelplanner.test',
            'role' => 'admin',
            'is_active' => false,
            'id' => 99999,
        ]);

        $response->assertRedirect(route('profile.show'));

        $this->member->refresh();
        $this->assertSame('user', $this->member->role);
        $this->assertTrue((bool) $this->member->is_active);
        $this->assertNotEquals(99999, $this->member->id);
    }

    /**
     * Cập nhật hồ sơ kiểm tra định dạng số điện thoại di động Việt Nam.
     */
    public function test_profile_update_validates_vietnamese_phone_format(): void
    {
        // 1. Số điện thoại hợp lệ (10 số, bắt đầu bằng 09, 03, 07, 08, 05)
        $validPhones = ['0912345678', '0387654321', '0765432198', '0855667788', '0987 654 321'];
        foreach ($validPhones as $phone) {
            $response = $this->actingAs($this->member)->put(route('profile.update'), [
                'name' => 'Nguyễn Kiểm Thử',
                'email' => 'member.sec@travelplanner.test',
                'phone' => $phone,
            ]);

            $response->assertSessionHasNoErrors();
        }

        // 2. Số điện thoại không hợp lệ (sai tiền tố mạng, sai số lượng chữ số, chứa chữ cái)
        $invalidPhones = ['0123456789', '123456789', '091234567899', '09123abcde', 'abcdefghij'];
        foreach ($invalidPhones as $invalidPhone) {
            $response = $this->actingAs($this->member)->put(route('profile.update'), [
                'name' => 'Nguyễn Kiểm Thử',
                'email' => 'member.sec@travelplanner.test',
                'phone' => $invalidPhone,
            ]);

            $response->assertSessionHasErrors(['phone']);
        }
    }

    /**
     * Cập nhật hồ sơ loại bỏ thẻ mã độc XSS trong các trường văn bản (Bio, City).
     */
    public function test_profile_update_strips_xss_tags_from_text_fields(): void
    {
        $response = $this->actingAs($this->member)->put(route('profile.update'), [
            'name' => '<b onmouseover=alert(1)>Nguyễn An Toàn</b>',
            'email' => 'member.sec@travelplanner.test',
            'city' => '<script>window.location="evil.com"</script>Đà Nẵng',
            'bio' => '<iframe src="evil.com"></iframe>Yêu thích du lịch tự túc',
        ]);

        $response->assertRedirect(route('profile.show'));

        $this->member->refresh();
        $this->assertSame('Nguyễn An Toàn', $this->member->name);
        $this->assertSame('Đà Nẵng', $this->member->city);
        $this->assertSame('Yêu thích du lịch tự túc', $this->member->bio);
    }

    /**
     * Đổi mật khẩu kiểm tra nghiêm ngặt mật khẩu hiện tại bằng rule current_password.
     */
    public function test_change_password_enforces_current_password_and_different_rules(): void
    {
        $this->member->update(['password' => Hash::make('OldPassword@123')]);

        // 1. Nhập sai mật khẩu hiện tại
        $responseWrongCurrent = $this->actingAs($this->member)->post(route('profile.password.update'), [
            'current_password' => 'SaiMatKhauHienTai@999',
            'password' => 'MatKhauMoi@456',
            'password_confirmation' => 'MatKhauMoi@456',
        ]);
        $responseWrongCurrent->assertSessionHasErrors(['current_password']);

        // 2. Mật khẩu mới trùng với mật khẩu hiện tại
        $responseSamePassword = $this->actingAs($this->member)->post(route('profile.password.update'), [
            'current_password' => 'OldPassword@123',
            'password' => 'OldPassword@123',
            'password_confirmation' => 'OldPassword@123',
        ]);
        $responseSamePassword->assertSessionHasErrors(['password']);

        // 3. Mật khẩu mới quá ngắn (< 8 ký tự)
        $responseShortPassword = $this->actingAs($this->member)->post(route('profile.password.update'), [
            'current_password' => 'OldPassword@123',
            'password' => '12345',
            'password_confirmation' => '12345',
        ]);
        $responseShortPassword->assertSessionHasErrors(['password']);

        // 4. Đổi mật khẩu thành công
        $responseSuccess = $this->actingAs($this->member)->post(route('profile.password.update'), [
            'current_password' => 'OldPassword@123',
            'password' => 'MatKhauMoi@456',
            'password_confirmation' => 'MatKhauMoi@456',
        ]);
        $responseSuccess->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('MatKhauMoi@456', $this->member->fresh()->password));
    }

    /**
     * Upload ảnh đại diện từ chối tệp không phải ảnh và tệp vượt quá giới hạn 2MB.
     */
    public function test_avatar_upload_rejects_non_image_and_oversized_files(): void
    {
        Storage::fake('public');

        // 1. Tệp văn bản hoặc script nguy hiểm giả mạo ảnh
        $fakePhpScript = UploadedFile::fake()->create('shell.php', 100, 'application/x-php');
        $responseScript = $this->actingAs($this->member)->post(route('profile.avatar'), [
            'avatar' => $fakePhpScript,
        ]);
        $responseScript->assertSessionHasErrors(['avatar']);

        // 2. Tệp ảnh quá dung lượng cho phép (> 2048 KB)
        $oversizedImage = UploadedFile::fake()->image('big-avatar.jpg')->size(3000);
        $responseOversized = $this->actingAs($this->member)->post(route('profile.avatar'), [
            'avatar' => $oversizedImage,
        ]);
        $responseOversized->assertSessionHasErrors(['avatar']);

        // 3. Tệp ảnh hợp lệ (< 2048 KB)
        $validImage = UploadedFile::fake()->image('valid-avatar.png', 400, 400)->size(500);
        $responseValid = $this->actingAs($this->member)->post(route('profile.avatar'), [
            'avatar' => $validImage,
        ]);
        $responseValid->assertSessionHasNoErrors();
    }

    /**
     * Quản trị viên tạo tài khoản mới kiểm tra định dạng số điện thoại và làm sạch mã độc XSS.
     */
    public function test_admin_create_user_validates_phone_and_strips_tags(): void
    {
        // 1. Số điện thoại sai định dạng bị từ chối
        $responseInvalidPhone = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Nguyễn Văn Test',
            'email' => 'admin.created@travelplanner.test',
            'password' => 'AdminPass@123',
            'password_confirmation' => 'AdminPass@123',
            'role' => 'user',
            'phone' => '0123999888',
        ]);
        $responseInvalidPhone->assertSessionHasErrors(['phone']);

        // 2. Tạo thành công và tự động loại bỏ thẻ script
        $responseSuccess = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => '<script>alert(1)</script>Nguyễn Hợp Lệ',
            'email' => 'Admin.Created@TravelPlanner.TEST',
            'password' => 'AdminPass@123',
            'password_confirmation' => 'AdminPass@123',
            'role' => 'user',
            'phone' => '0912334455',
            'city' => '<b>TP. Hồ Chí Minh</b>',
            'bio' => '<i>Người dùng hệ thống</i>',
        ]);

        $responseSuccess->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Nguyễn Hợp Lệ',
            'email' => 'admin.created@travelplanner.test',
            'phone' => '0912334455',
            'city' => 'TP. Hồ Chí Minh',
            'bio' => 'Người dùng hệ thống',
        ]);
    }

    /**
     * Quản trị viên cập nhật tài khoản kiểm tra trùng lặp email và định dạng số điện thoại.
     */
    public function test_admin_update_user_validations(): void
    {
        $targetUser = User::factory()->create([
            'email' => 'target.user@travelplanner.test',
            'role' => 'user',
        ]);

        // 1. Trùng email với tài khoản khác
        $responseDuplicateEmail = $this->actingAs($this->admin)->patch(route('admin.users.update', $targetUser->id), [
            'name' => 'Target User Sửa',
            'email' => $this->member->email, // Email đã thuộc về member
            'role' => 'user',
        ]);
        $responseDuplicateEmail->assertSessionHasErrors(['email']);

        // 2. Cập nhật thành công với cùng email của chính nó và số điện thoại chuẩn VN
        $responseSuccess = $this->actingAs($this->admin)->patch(route('admin.users.update', $targetUser->id), [
            'name' => 'Target User Sau Cập Nhật',
            'email' => 'target.user@travelplanner.test',
            'role' => 'user',
            'phone' => '0933112233',
        ]);

        $responseSuccess->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Target User Sau Cập Nhật',
            'phone' => '0933112233',
        ]);
    }
}
