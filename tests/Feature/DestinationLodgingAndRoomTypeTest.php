<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationLodgingAndRoomTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Kiểm thử khách vãng lai có thể truy cập trang danh sách khám phá địa điểm du lịch & nơi lưu trú.
     */
    public function test_guest_can_access_destinations_index_page(): void
    {
        $city = City::factory()->create(['name' => 'Đà Nẵng']);
        $category = Category::factory()->create(['name' => 'Nghỉ dưỡng']);

        $destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Sun World Bà Nà Hills',
            'slug' => 'sun-world-ba-na-hills',
        ]);

        $response = $this->get(route('destinations.index'));

        $response->assertStatus(200);
        $response->assertSee('Khám Phá Địa Điểm Du Lịch');
        $response->assertSee('Sun World Bà Nà Hills');
    }

    /**
     * Kiểm thử bộ lọc "Chỉ hiển thị nơi lưu trú" (is_lodging=1).
     */
    public function test_guest_can_filter_destinations_by_lodging_only(): void
    {
        $city = City::factory()->create(['name' => 'Phú Quốc']);
        $category = Category::factory()->create(['name' => 'Khách sạn']);

        // 1. Địa điểm CÓ chỗ lưu trú (có RoomType)
        $lodgingDest = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Vinpearl Resort & Spa Phú Quốc',
            'slug' => 'vinpearl-resort-spa-phu-quoc',
        ]);

        RoomType::factory()->create([
            'destination_id' => $lodgingDest->id,
            'name' => 'Phòng Deluxe Hướng Biển',
            'capacity' => 2,
            'price_per_night' => 2500000,
        ]);

        // 2. Địa điểm KHÔNG có chỗ lưu trú (không có RoomType)
        $sightseeingDest = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Mũi Gành Dầu Phú Quốc',
            'slug' => 'mui-ganh-dau-phu-quoc',
        ]);

        // Truy vấn với bộ lọc is_lodging = 1
        $response = $this->get(route('destinations.index', ['is_lodging' => '1']));

        $response->assertStatus(200);
        $response->assertSee('Vinpearl Resort & Spa Phú Quốc');
        $response->assertSee('Nơi lưu trú');
        $response->assertDontSee('Mũi Gành Dầu Phú Quốc');
    }

    /**
     * Kiểm thử xem trang chi tiết địa điểm du lịch kèm thông tin nơi lưu trú và danh sách Room Type.
     */
    public function test_guest_can_view_destination_detail_page_with_lodging_and_room_types(): void
    {
        $city = City::factory()->create(['name' => 'Đà Nẵng']);
        $category = Category::factory()->create(['name' => 'Nghỉ dưỡng']);

        $destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Sun World Bà Nà Hills',
            'slug' => 'sun-world-ba-na-hills',
            'description' => 'Chốn bồng lai tiên cảnh giữa lòng Đà Nẵng',
            'address' => 'Thôn An Sơn, Hòa Vang, Đà Nẵng',
            'entrance_fee' => 900000,
        ]);

        $room1 = RoomType::factory()->create([
            'destination_id' => $destination->id,
            'name' => 'Deluxe King Núi (Bà Nà Hills)',
            'capacity' => 2,
            'price_per_night' => 1850000,
            'quantity' => 8,
        ]);

        $room2 = RoomType::factory()->create([
            'destination_id' => $destination->id,
            'name' => 'Family Suite Làng Pháp',
            'capacity' => 4,
            'price_per_night' => 3200000,
            'quantity' => 3,
        ]);

        $response = $this->get(route('destinations.show', $destination->slug));

        $response->assertStatus(200);
        $response->assertSee('Sun World Bà Nà Hills');
        $response->assertSee('Có khách sạn lưu trú');
        $response->assertSee('Danh Sách Loại Phòng & Chỗ Lưu Trú');
        $response->assertSee('Deluxe King Núi (Bà Nà Hills)');
        $response->assertSee('Family Suite Làng Pháp');
        $response->assertSee('1.850.000đ');
        $response->assertSee('3.200.000đ');
        $response->assertSee('Còn 8 phòng trống');
        $response->assertSee('Còn 3 phòng trống');
    }

    /**
     * Kiểm thử API trả về chi tiết của một Room Type cho Modal popup.
     */
    public function test_guest_can_fetch_room_type_detail_via_api(): void
    {
        $city = City::factory()->create(['name' => 'Nha Trang']);
        $category = Category::factory()->create(['name' => 'Biển đảo']);

        $destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Vinpearl Resort Nha Trang',
            'slug' => 'vinpearl-resort-nha-trang',
        ]);

        $room = RoomType::factory()->create([
            'destination_id' => $destination->id,
            'name' => 'Phòng Ocean View Cao Cấp',
            'capacity' => 2,
            'price_per_night' => 2100000,
            'quantity' => 12,
            'description' => 'Tầm nhìn trực diện biển Nha Trang tuyệt đẹp.',
        ]);

        $response = $this->getJson(route('room-types.show', $room->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $room->id,
                'name' => 'Phòng Ocean View Cao Cấp',
                'capacity' => 2,
                'capacity_label' => '2 người lớn',
                'price_per_night' => 2100000,
                'quantity' => 12,
                'description' => 'Tầm nhìn trực diện biển Nha Trang tuyệt đẹp.',
            ],
        ]);
    }

    /**
     * Kiểm thử trang chi tiết của địa điểm không có khách sạn vẫn hiển thị đầy đủ và không bị lỗi.
     */
    public function test_destination_without_room_types_renders_gracefully(): void
    {
        $city = City::factory()->create(['name' => 'Hà Nội']);
        $category = Category::factory()->create(['name' => 'Văn hóa']);

        $destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Văn Miếu Quốc Tử Giám',
            'slug' => 'van-mieu-quoc-tu-giam',
        ]);

        $response = $this->get(route('destinations.show', $destination->slug));

        $response->assertStatus(200);
        $response->assertSee('Văn Miếu Quốc Tử Giám');
        $response->assertDontSee('Có khách sạn lưu trú');
    }

    /**
     * Kiểm thử trả về mã lỗi 404 khi truy cập địa điểm không tồn tại.
     */
    public function test_abort_404_when_destination_slug_not_found(): void
    {
        $response = $this->get(route('destinations.show', 'dia-diem-khong-ton-tai-9999'));

        $response->assertStatus(404);
    }
}
