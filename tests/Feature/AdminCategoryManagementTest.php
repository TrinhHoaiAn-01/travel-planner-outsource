<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\User;
use Database\Seeders\AdminCategoryDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryManagementTest extends TestCase
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
    public function test_guest_cannot_access_admin_categories(): void
    {
        $response = $this->get(route('admin.categories.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng vai trò thông thường (user) không có quyền truy cập trang quản trị danh mục (HTTP 403).
     */
    public function test_regular_user_cannot_access_admin_categories(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.categories.index'));
        $response->assertForbidden();
    }

    /**
     * Quản trị viên (Admin) truy cập trang quản trị danh mục thành công và thấy giao diện tái hiện chuẩn ảnh 100%.
     */
    public function test_admin_can_view_categories_index_page_matching_screenshot(): void
    {
        $this->seed(AdminCategoryDemoSeeder::class);

        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));

        $response->assertOk();
        $response->assertSee('Quản Lý Danh Mục Du Lịch');
        $response->assertSee('+ Thêm Danh Mục Mới');
        $response->assertSee('Admin');
        $response->assertSee('Danh mục');
        $response->assertSee('Biểu tượng');
        $response->assertSee('Tên Danh Mục');
        $response->assertSee('Slug URL');
        $response->assertSee('Số địa điểm');
        $response->assertSee('Trạng thái');
        $response->assertSee('Hành động');
        $response->assertSee('Nghỉ dưỡng & Resort');
        $response->assertSee('resort-spa');
        $response->assertSee('Biển & Đảo');
        $response->assertSee('bien-dao');
        $response->assertSee('Núi Rừng & Trekking');
        $response->assertSee('nui-rung');
        $response->assertSee('Di Sản Văn Hóa');
        $response->assertSee('di-san-van-hoa');
        $response->assertSee('Hiển thị');
    }

    /**
     * Admin thêm mới danh mục du lịch thành công.
     */
    public function test_admin_can_create_new_category_successfully(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Du Lịch Sinh Thái',
            'slug' => 'du-lich-sinh-thai',
            'icon' => 'tree',
            'description' => 'Hòa mình vào thiên nhiên trong lành và bảo tồn hệ sinh thái.',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Du Lịch Sinh Thái',
            'slug' => 'du-lich-sinh-thai',
            'icon' => 'tree',
            'is_active' => true,
        ]);
    }

    /**
     * Không thể tạo danh mục khi bỏ trống tên bắt buộc.
     */
    public function test_cannot_create_category_without_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => '',
            'slug' => 'danh-muc-loi',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Không thể tạo danh mục với slug đã tồn tại.
     */
    public function test_cannot_create_category_with_duplicate_slug(): void
    {
        Category::create([
            'name' => 'Khám Phá',
            'slug' => 'kham-pha',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Khám Phá Mới',
            'slug' => 'kham-pha',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    /**
     * Admin cập nhật thông tin danh mục du lịch thành công.
     */
    public function test_admin_can_update_category(): void
    {
        $category = Category::create([
            'name' => 'Ẩm Thực',
            'slug' => 'am-thuc',
            'icon' => 'food',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $category->id), [
            'name' => 'Thiên Đường Ẩm Thực',
            'slug' => 'thien-duong-am-thuc',
            'icon' => 'food',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Thiên Đường Ẩm Thực',
            'slug' => 'thien-duong-am-thuc',
        ]);
    }

    /**
     * Quy tắc BR-17: Không cho phép xóa danh mục khi đang có địa điểm du lịch liên kết.
     */
    public function test_admin_cannot_delete_category_when_destinations_exist_under_br17(): void
    {
        $category = Category::create([
            'name' => 'Biển Đảo',
            'slug' => 'bien-dao',
            'icon' => 'waves',
            'is_active' => true,
        ]);

        $city = City::create([
            'name' => 'Đà Nẵng',
            'slug' => 'da-nang',
            'code' => 'CTY-DAD',
        ]);

        Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Bãi biển Mỹ Khê',
            'slug' => 'bai-bien-my-khe',
            'entrance_fee' => 0,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category->id));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /**
     * Cho phép xóa danh mục khi không có địa điểm du lịch nào liên kết.
     */
    public function test_admin_can_delete_category_without_destinations(): void
    {
        $category = Category::create([
            'name' => 'Danh Mục Thử Nghiệm',
            'slug' => 'danh-muc-thu-nghiem',
            'icon' => 'default',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category->id));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /**
     * Tìm kiếm và lọc danh mục du lịch theo từ khóa.
     */
    public function test_admin_can_filter_categories_by_keyword(): void
    {
        Category::create(['name' => 'Nghỉ dưỡng & Resort', 'slug' => 'resort-spa', 'is_active' => true]);
        Category::create(['name' => 'Biển & Đảo', 'slug' => 'bien-dao', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->get(route('admin.categories.index', ['search' => 'Resort']));

        $response->assertOk();
        $response->assertSee('Nghỉ dưỡng & Resort');
        $response->assertDontSee('Biển & Đảo');
    }
}
