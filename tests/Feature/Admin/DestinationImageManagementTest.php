<?php

namespace Tests\Feature\Admin;

use App\Models\Destination;
use App\Models\DestinationImage;
use App\Models\User;
use Database\Seeders\AdminDestinationDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationImageManagementTest extends TestCase
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
            'name' => 'Người Dùng Thường',
            'email' => 'user@travelplanner.test',
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    /**
     * Khách chưa đăng nhập bị chuyển hướng về login (HTTP 302).
     */
    public function test_guest_cannot_access_destination_images(): void
    {
        $response = $this->get(route('admin.destination-images.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng thường bị chặn truy cập (HTTP 403 Forbidden theo BR-13, BR-20).
     */
    public function test_regular_user_cannot_access_destination_images(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.destination-images.index'));
        $response->assertForbidden();
    }

    /**
     * Quản trị viên truy cập thành công và thấy giao diện tái hiện chuẩn ảnh (Bà Nà Hills, 6 hình ảnh, Ảnh đại diện).
     */
    public function test_admin_can_view_destination_images_page(): void
    {
        $this->seed(AdminDestinationDemoSeeder::class);

        $response = $this->actingAs($this->admin)->get(route('admin.destination-images.index'));

        $response->assertOk();
        $response->assertSee('Quản Lý Thư Viện Hình Ảnh');
        $response->assertSee('+ Tải Lên Ảnh Mới');
        $response->assertSee('Thư viện ảnh');
        $response->assertSee('Lọc theo Địa điểm du lịch:');
        $response->assertSee('Sun World Bà Nà Hills');
        $response->assertSee('Tổng cộng: 6 hình ảnh');
        $response->assertSee('Ảnh đại diện');
    }

    /**
     * Lọc thư viện ảnh theo địa điểm du lịch khác.
     */
    public function test_admin_can_filter_images_by_destination(): void
    {
        $this->seed(AdminDestinationDemoSeeder::class);
        $hoiAn = Destination::where('slug', 'pho-co-hoi-an')->firstOrFail();

        $response = $this->actingAs($this->admin)->get(route('admin.destination-images.index', [
            'destination_id' => $hoiAn->id,
        ]));

        $response->assertOk();
        $response->assertSee('Phố Cổ Hội An');
        $response->assertSee('Tổng cộng: 1 hình ảnh');
    }

    /**
     * Tải lên hình ảnh mới cho địa điểm du lịch thành công.
     */
    public function test_admin_can_upload_new_image_successfully(): void
    {
        $this->seed(AdminDestinationDemoSeeder::class);
        $dest = Destination::firstOrFail();

        $response = $this->actingAs($this->admin)->post(route('admin.destination-images.store'), [
            'destination_id' => $dest->id,
            'image_url' => 'https://images.unsplash.com/photo-test-upload.jpg',
            'is_primary' => 0,
        ]);

        $response->assertRedirect(route('admin.destination-images.index', ['destination_id' => $dest->id]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('destination_images', [
            'destination_id' => $dest->id,
            'image_path' => 'https://images.unsplash.com/photo-test-upload.jpg',
            'is_primary' => false,
        ]);
    }

    /**
     * Đặt hình ảnh làm ảnh đại diện chính của địa điểm.
     */
    public function test_admin_can_set_image_as_primary(): void
    {
        $this->seed(AdminDestinationDemoSeeder::class);
        $baNa = Destination::where('slug', 'sun-world-ba-na-hills')->firstOrFail();

        // Lấy một ảnh không phải đại diện
        $secondaryImage = $baNa->images()->where('is_primary', false)->firstOrFail();

        $response = $this->actingAs($this->admin)->post(route('admin.destination-images.set-primary', $secondaryImage->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $secondaryImage->refresh();
        $this->assertTrue((bool) $secondaryImage->is_primary);

        // Các ảnh khác của địa điểm này đều không phải đại diện
        $otherCount = $baNa->images()->where('id', '!=', $secondaryImage->id)->where('is_primary', true)->count();
        $this->assertEquals(0, $otherCount);
    }

    /**
     * Xóa một hình ảnh khỏi thư viện.
     */
    public function test_admin_can_delete_image_successfully(): void
    {
        $this->seed(AdminDestinationDemoSeeder::class);
        $baNa = Destination::where('slug', 'sun-world-ba-na-hills')->firstOrFail();

        $imageToDelete = $baNa->images()->firstOrFail();
        $imageId = $imageToDelete->id;

        $response = $this->actingAs($this->admin)->delete(route('admin.destination-images.destroy', $imageId));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('destination_images', ['id' => $imageId]);
    }
}
