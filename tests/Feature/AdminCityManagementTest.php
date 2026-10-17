<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\User;
use Database\Seeders\AdminCityDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCityManagementTest extends TestCase
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
    public function test_guest_cannot_access_admin_cities(): void
    {
        $response = $this->get(route('admin.cities.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng vai trò thông thường (user) không có quyền truy cập trang quản trị thành phố (HTTP 403).
     */
    public function test_regular_user_cannot_access_admin_cities(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.cities.index'));
        $response->assertForbidden();
    }

    /**
     * Quản trị viên (Admin) truy cập trang quản trị thành phố thành công và thấy giao diện chuẩn ảnh.
     */
    public function test_admin_can_view_cities_index_page(): void
    {
        $this->seed(AdminCityDemoSeeder::class);

        $response = $this->actingAs($this->admin)->get(route('admin.cities.index'));

        $response->assertOk();
        $response->assertSee('Quản Lý Tỉnh / Thành Phố');
        $response->assertSee('+ Thêm Thành Phố Mới');
        $response->assertSee('Admin');
        $response->assertSee('Thành phố');
        $response->assertSee('Đà Nẵng');
        $response->assertSee('CTY-DAD');
        $response->assertSee('Quảng Ninh (Hạ Long)');
        $response->assertSee('Phú Quốc (Kiên Giang)');
        $response->assertSee('Quảng Nam (Hội An)');
    }

    /**
     * Admin thêm mới tỉnh / thành phố thành công.
     */
    public function test_admin_can_create_new_city_successfully(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.cities.store'), [
            'name' => 'Thừa Thiên Huế',
            'code' => 'CTY-HUE',
            'region' => 'Miền Trung',
            'description' => 'Cố đô Huế mộng mơ với di tích Đại Nội và lăng tẩm các vua triều Nguyễn.',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.cities.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cities', [
            'name' => 'Thừa Thiên Huế',
            'code' => 'CTY-HUE',
            'region' => 'Miền Trung',
            'is_active' => true,
        ]);
    }

    /**
     * Không thể tạo thành phố khi bỏ trống tên bắt buộc.
     */
    public function test_cannot_create_city_without_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.cities.store'), [
            'name' => '',
            'code' => 'CTY-ERR',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Không thể tạo thành phố với mã code đã tồn tại.
     */
    public function test_cannot_create_city_with_duplicate_code(): void
    {
        City::create([
            'name' => 'Hải Phòng',
            'slug' => 'hai-phong',
            'code' => 'CTY-HPH',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.cities.store'), [
            'name' => 'Hải Phòng Mới',
            'code' => 'CTY-HPH',
        ]);

        $response->assertSessionHasErrors('code');
    }

    /**
     * Admin cập nhật thông tin tỉnh / thành phố thành công.
     */
    public function test_admin_can_update_city(): void
    {
        $city = City::create([
            'name' => 'Lâm Đồng',
            'slug' => 'lam-dong',
            'code' => 'CTY-LDG',
            'region' => 'Tây Nguyên',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.cities.update', $city->id), [
            'name' => 'Đà Lạt (Lâm Đồng)',
            'code' => 'CTY-DLT',
            'region' => 'Miền Trung',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.cities.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cities', [
            'id' => $city->id,
            'name' => 'Đà Lạt (Lâm Đồng)',
            'code' => 'CTY-DLT',
        ]);
    }

    /**
     * Quy tắc BR-17: Không cho phép xóa thành phố khi đang có địa điểm du lịch liên kết.
     */
    public function test_admin_cannot_delete_city_when_destinations_exist_under_br17(): void
    {
        $city = City::create([
            'name' => 'Đà Nẵng',
            'slug' => 'da-nang',
            'code' => 'CTY-DAD',
            'region' => 'Miền Trung',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Giải trí',
            'slug' => 'giai-tri',
        ]);

        Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Cầu Rồng',
            'slug' => 'cau-rong',
            'entrance_fee' => 0,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.cities.destroy', $city->id));

        $response->assertRedirect(route('admin.cities.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('cities', ['id' => $city->id]);
    }

    /**
     * Cho phép xóa thành phố khi không có địa điểm du lịch nào liên kết.
     */
    public function test_admin_can_delete_city_without_destinations(): void
    {
        $city = City::create([
            'name' => 'Tỉnh Thử Nghiệm',
            'slug' => 'tinh-thu-nghiem',
            'code' => 'CTY-TST',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.cities.destroy', $city->id));

        $response->assertRedirect(route('admin.cities.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('cities', ['id' => $city->id]);
    }
}
