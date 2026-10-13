<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Kiểm thử khách vãng lai truy cập trang chủ thành công và thấy dữ liệu.
     */
    public function test_guest_can_access_home_page_successfully(): void
    {
        $city = City::factory()->create(['name' => 'Đà Nẵng']);
        $category = Category::factory()->create(['name' => 'Biển đảo']);
        Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Bà Nà Hills',
            'is_featured' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('TRAVEL PLANNER');
        $response->assertSee('Bà Nà Hills');
    }

    /**
     * Kiểm thử chỉ hiển thị các địa điểm có is_featured = true và ẩn địa điểm is_featured = false.
     */
    public function test_only_featured_destinations_are_displayed_on_home_page(): void
    {
        $city = City::factory()->create(['name' => 'Hà Nội']);
        $category = Category::factory()->create(['name' => 'Văn hóa']);

        // Địa điểm nổi bật
        Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Hoàng Thành Thăng Long',
            'is_featured' => true,
        ]);

        // Địa điểm không nổi bật
        Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Quán Cà Phê Nhỏ',
            'is_featured' => false,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Hoàng Thành Thăng Long');
        $response->assertDontSee('Quán Cà Phê Nhỏ');
    }

    /**
     * Kiểm thử người dùng đã đăng nhập hiển thị tên trên thanh điều hướng.
     */
    public function test_authenticated_user_sees_profile_on_home_page(): void
    {
        $user = User::factory()->create(['name' => 'Phạm Thị Ngọc Ái']);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Phạm Thị Ngọc Ái');
    }

    /**
     * Kiểm thử tìm kiếm địa điểm theo từ khóa tên địa điểm từ Trang chủ.
     */
    public function test_guest_can_search_destinations_by_keyword_from_home_page(): void
    {
        $city = City::factory()->create(['name' => 'Đà Nẵng']);
        $category = Category::factory()->create(['name' => 'Nghỉ dưỡng']);

        $dest1 = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Bà Nà Hills',
            'is_featured' => true,
        ]);

        $dest2 = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Chùa Linh Ứng',
            'is_featured' => true,
        ]);

        $response = $this->get(route('home', ['keyword' => 'Bà Nà']));

        $response->assertStatus(200);
        $response->assertSee('KẾT QUẢ TÌM KIẾM NHANH');
        $response->assertSee('Bà Nà Hills');
    }

    /**
     * Kiểm thử tìm kiếm địa điểm theo tên Thành phố từ Trang chủ.
     */
    public function test_guest_can_search_destinations_by_city_name_from_home_page(): void
    {
        $cityDaNang = City::factory()->create(['name' => 'Đà Nẵng']);
        $cityHaNoi = City::factory()->create(['name' => 'Hà Nội']);
        $category = Category::factory()->create(['name' => 'Văn hóa']);

        Destination::factory()->create([
            'city_id' => $cityDaNang->id,
            'category_id' => $category->id,
            'name' => 'Cầu Rồng Đà Nẵng',
            'is_featured' => false,
        ]);

        Destination::factory()->create([
            'city_id' => $cityHaNoi->id,
            'category_id' => $category->id,
            'name' => 'Hồ Gươm Hà Nội',
            'is_featured' => false,
        ]);

        $response = $this->get(route('home', ['keyword' => 'Đà Nẵng']));

        $response->assertStatus(200);
        $response->assertSee('Cầu Rồng Đà Nẵng');
        $response->assertDontSee('Hồ Gươm Hà Nội');
    }

    /**
     * Kiểm thử lọc địa điểm theo Danh mục từ Trang chủ.
     */
    public function test_guest_can_filter_destinations_by_category_from_home_page(): void
    {
        $city = City::factory()->create(['name' => 'Nha Trang']);
        $catBeach = Category::factory()->create(['name' => 'Biển & Đảo']);
        $catMountain = Category::factory()->create(['name' => 'Núi Rừng']);

        Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $catBeach->id,
            'name' => 'Bãi Dài Cam Ranh',
        ]);

        Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $catMountain->id,
            'name' => 'Đỉnh Hòn Bà',
        ]);

        $response = $this->get(route('home', ['category_id' => $catBeach->id]));

        $response->assertStatus(200);
        $response->assertSee('Bãi Dài Cam Ranh');
        $response->assertDontSee('Đỉnh Hòn Bà');
    }

    /**
     * Kiểm thử lọc địa điểm theo Mức ngân sách (giá vé / tham quan) từ Trang chủ.
     */
    public function test_guest_can_filter_destinations_by_budget_range_from_home_page(): void
    {
        $city = City::factory()->create(['name' => 'Phú Quốc']);
        $category = Category::factory()->create(['name' => 'Vui chơi']);

        // Giá vé dưới 500k
        Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Làng Chài Hàm Ninh',
            'entrance_fee' => 0,
        ]);

        // Giá vé trên 1.5tr
        Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Tour Du Thuyền VIP Phú Quốc',
            'entrance_fee' => 2500000,
        ]);

        // Lọc mức 1: Dưới 500.000đ
        $response = $this->get(route('home', ['budget' => '1']));

        $response->assertStatus(200);
        $response->assertSee('Làng Chài Hàm Ninh');
        $response->assertDontSee('Tour Du Thuyền VIP Phú Quốc');
    }

    /**
     * Kiểm thử API tìm kiếm nhanh gợi ý địa điểm & thành phố (Autocomplete).
     */
    public function test_quick_search_api_returns_matching_cities_and_destinations(): void
    {
        $city = City::factory()->create(['name' => 'Đà Lạt']);
        $category = Category::factory()->create(['name' => 'Ngắm cảnh']);

        Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Thung Lũng Tình Yêu Đà Lạt',
        ]);

        $response = $this->getJson(route('destinations.quick-search', ['keyword' => 'Đà Lạt']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'keyword',
            'cities',
            'destinations',
        ]);

        $response->assertJsonFragment(['name' => 'Đà Lạt']);
        $response->assertJsonFragment(['name' => 'Thung Lũng Tình Yêu Đà Lạt']);
    }

    /**
     * Kiểm thử hiển thị thông báo trống khi không tìm thấy địa điểm nào khớp.
     */
    public function test_home_search_shows_empty_state_when_no_destinations_match(): void
    {
        $response = $this->get(route('home', ['keyword' => 'Từ Khóa Không Tồn Tại 9999']));

        $response->assertStatus(200);
        $response->assertSee('Không tìm thấy địa điểm nào phù hợp');
    }
}
