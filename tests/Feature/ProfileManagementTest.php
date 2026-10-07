<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Destination;
use App\Models\Favorite;
use App\Models\Review;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Quy tắc BR-01: Khách chưa đăng nhập bị chuyển hướng về màn hình đăng nhập khi truy cập Hồ sơ.
     */
    public function test_guest_is_redirected_to_login_when_accessing_profile(): void
    {
        $response = $this->get(route('profile.show'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng đã đăng nhập có thể xem thông tin hồ sơ và số liệu thống kê (Hình 10 / Image 4).
     */
    public function test_authenticated_user_can_view_profile_page_with_statistics(): void
    {
        $city = City::factory()->create(['name' => 'Đà Nẵng']);
        $destination = Destination::factory()->create(['city_id' => $city->id]);

        $user = User::factory()->create([
            'name' => 'Nguyễn Bảo Ngọc',
            'email' => 'ngoc.travel@travelplanner.com',
            'city' => 'Đà Nẵng',
            'phone' => '0912 345 678',
            'bio' => 'Đam mê du lịch tự túc, nhiếp ảnh phong cảnh.',
            'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb',
            'email_verified_at' => now(),
        ]);

        // Tạo chuyến đi mẫu
        Trip::factory()->count(2)->create(['user_id' => $user->id]);

        // Tạo mục yêu thích mẫu
        Favorite::factory()->create([
            'user_id' => $user->id,
            'destination_id' => $destination->id,
        ]);

        // Tạo đánh giá mẫu
        Review::factory()->create([
            'user_id' => $user->id,
            'destination_id' => $destination->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertViewIs('profile.show');
        $response->assertSee('Nguyễn Bảo Ngọc');
        $response->assertSee('ngoc.travel@travelplanner.com');
        $response->assertSee('Đà Nẵng, Việt Nam');
        $response->assertSee('Explorer Member');
        $response->assertSee('Đã xác minh Email');
        $response->assertSee('Chuyến đi');
        $response->assertSee('Yêu thích');
        $response->assertSee('Đánh giá');
        $response->assertSee('Đổi mật khẩu');
        $response->assertSee('Đăng xuất');
        $response->assertSee('Lưu thay đổi');
    }

    /**
     * Cập nhật thông tin cá nhân thành công (Họ tên, số điện thoại, thành phố, bio, avatar url).
     */
    public function test_user_can_update_profile_information(): void
    {
        $user = User::factory()->create([
            'name' => 'Tên Ban Đầu',
            'email' => 'ban-dau@travelplanner.test',
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Nguyễn Bảo Ngọc Cập Nhật',
            'email' => 'ban-dau@travelplanner.test',
            'phone' => '0987 654 321',
            'city' => 'Hà Nội',
            'avatar_url' => 'https://images.unsplash.com/photo-custom',
            'bio' => 'Giới thiệu bản thân mới cập nhật.',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success', 'Cập nhật thông tin cá nhân thành công.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nguyễn Bảo Ngọc Cập Nhật',
            'phone' => '0987 654 321',
            'city' => 'Hà Nội',
            'avatar' => 'https://images.unsplash.com/photo-custom',
            'bio' => 'Giới thiệu bản thân mới cập nhật.',
        ]);
    }

    /**
     * Tải lên và cập nhật ảnh đại diện cá nhân thành công từ tệp hình ảnh.
     */
    public function test_user_can_upload_avatar_image_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $fakeImage = UploadedFile::fake()->image('avatar.jpg', 300, 300);

        $response = $this->actingAs($user)
            ->postJson(route('profile.avatar'), [
                'avatar' => $fakeImage,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Cập nhật ảnh đại diện thành công.',
        ]);

        $user->refresh();
        $this->assertNotNull($user->avatar);
        $this->assertStringStartsWith('/storage/avatars/', $user->avatar);

        // Kiểm tra tệp thực sự tồn tại trong disk public
        $storedFilePath = str_replace('/storage/', '', $user->avatar);
        Storage::disk('public')->assertExists($storedFilePath);
    }

    /**
     * Thay đổi mật khẩu tài khoản thành công khi nhập đúng mật khẩu hiện tại (Hình 9 / Image 3).
     */
    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPassword123'),
        ]);

        $response = $this->actingAs($user)->post(route('profile.password.update'), [
            'current_password' => 'CurrentPassword123',
            'password' => 'NewPassword456',
            'password_confirmation' => 'NewPassword456',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success', 'Đổi mật khẩu thành công. Mật khẩu mới của bạn đã có hiệu lực.');

        $this->assertTrue(Hash::check('NewPassword456', $user->fresh()->password));
    }

    /**
     * Thay đổi mật khẩu thất bại khi nhập sai mật khẩu hiện tại.
     */
    public function test_change_password_fails_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPassword123'),
        ]);

        $response = $this->actingAs($user)->post(route('profile.password.update'), [
            'current_password' => 'SaiMatKhauHienTai',
            'password' => 'NewPassword456',
            'password_confirmation' => 'NewPassword456',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $this->assertTrue(Hash::check('CurrentPassword123', $user->fresh()->password));
    }

    /**
     * Thay đổi mật khẩu thất bại nếu mật khẩu mới trùng với mật khẩu hiện tại.
     */
    public function test_change_password_fails_when_new_password_matches_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPassword123'),
        ]);

        $response = $this->actingAs($user)->post(route('profile.password.update'), [
            'current_password' => 'CurrentPassword123',
            'password' => 'CurrentPassword123',
            'password_confirmation' => 'CurrentPassword123',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}
