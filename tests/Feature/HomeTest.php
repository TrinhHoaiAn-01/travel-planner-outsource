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
}
