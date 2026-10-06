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
     * Kiểm thử khách vãng lai (Guest) truy cập trang chủ thành công và thấy dữ liệu.
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
     * Kiểm thử người dùng đã đăng nhập (User) hiển thị tên trên thanh điều hướng.
     */
    public function test_authenticated_user_sees_profile_on_home_page(): void
    {
        $user = User::factory()->create(['name' => 'Phạm Thị Ngọc Ái']);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Phạm Thị Ngọc Ái');
    }
}
