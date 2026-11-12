<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\DestinationImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationGalleryAndRelatedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Kiểm thử hiển thị thư viện hình ảnh Gallery và các thành phần Lightbox trên trang chi tiết địa điểm.
     */
    public function test_destination_show_page_displays_gallery_images(): void
    {
        $city = City::factory()->create(['name' => 'Đà Nẵng', 'slug' => 'da-nang']);
        $category = Category::factory()->create(['name' => 'Nghỉ dưỡng', 'slug' => 'nghi-duong']);

        $destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Bà Nà Hills Resort',
            'slug' => 'ba-na-hills-resort',
        ]);

        // Tạo 6 hình ảnh thuộc địa điểm (1 ảnh chính và 5 ảnh phụ)
        DestinationImage::create([
            'destination_id' => $destination->id,
            'image_path' => 'https://images.unsplash.com/photo-primary.jpg',
            'caption' => 'Toàn cảnh Bà Nà Hills',
            'is_primary' => true,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            DestinationImage::create([
                'destination_id' => $destination->id,
                'image_path' => "https://images.unsplash.com/photo-sub-$i.jpg",
                'caption' => "Góc nhìn số $i",
                'is_primary' => false,
            ]);
        }

        $response = $this->get(route('destinations.show', $destination->slug));

        $response->assertStatus(200);
        // Kiểm tra hiển thị ảnh chính và số lượng ảnh
        $response->assertSee('https://images.unsplash.com/photo-primary.jpg');
        $response->assertSee('Xem tất cả 6 hình ảnh');
        // Kiểm tra các thành phần modal lightbox (FR12)
        $response->assertSee('id="galleryLightboxModal"', false);
        $response->assertSee('id="lightboxActiveImg"', false);
        $response->assertSee('id="lightboxCounter"', false);
    }

    /**
     * Kiểm thử endpoint API lấy danh sách toàn bộ ảnh Gallery của địa điểm: GET /destinations/{slug}/gallery
     */
    public function test_destination_gallery_api_endpoint_returns_json_images(): void
    {
        $city = City::factory()->create();
        $category = Category::factory()->create();

        $destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Cầu Vàng Đà Nẵng',
            'slug' => 'cau-vang-da-nang',
        ]);

        DestinationImage::create([
            'destination_id' => $destination->id,
            'image_path' => 'https://example.com/primary.jpg',
            'caption' => 'Ảnh đại diện Cầu Vàng',
            'is_primary' => true,
        ]);

        DestinationImage::create([
            'destination_id' => $destination->id,
            'image_path' => 'https://example.com/sub1.jpg',
            'caption' => 'Ảnh chi tiết 1',
            'is_primary' => false,
        ]);

        $response = $this->get(route('destinations.gallery', $destination->slug));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'destination' => [
                'id' => $destination->id,
                'name' => 'Cầu Vàng Đà Nẵng',
                'slug' => 'cau-vang-da-nang',
            ],
            'total' => 2,
        ]);

        $response->assertJsonFragment([
            'image_path' => 'https://example.com/primary.jpg',
            'is_primary' => true,
        ]);
        $response->assertJsonFragment([
            'image_path' => 'https://example.com/sub1.jpg',
            'is_primary' => false,
        ]);
    }

    /**
     * Kiểm thử API Gallery trả về lỗi 404 khi địa điểm du lịch không tồn tại.
     */
    public function test_destination_gallery_api_returns_404_for_invalid_slug(): void
    {
        $response = $this->get(route('destinations.gallery', 'dia-diem-khong-ton-tai'));

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Không tìm thấy địa điểm du lịch.',
        ]);
    }

    /**
     * Kiểm thử hiển thị danh sách địa điểm liên quan (FR13 - cùng thành phố hoặc cùng danh mục, không trùng địa điểm hiện tại).
     */
    public function test_destination_show_page_displays_related_destinations(): void
    {
        $cityDN = City::factory()->create(['name' => 'Đà Nẵng', 'slug' => 'da-nang']);
        $cityHN = City::factory()->create(['name' => 'Hà Nội', 'slug' => 'ha-noi']);
        $catBien = Category::factory()->create(['name' => 'Biển đảo', 'slug' => 'bien-dao']);
        $catVanHoa = Category::factory()->create(['name' => 'Văn hóa', 'slug' => 'van-hoa']);

        // Địa điểm hiện tại đang xem (Đà Nẵng + Biển đảo)
        $currentDest = Destination::factory()->create([
            'city_id' => $cityDN->id,
            'category_id' => $catBien->id,
            'name' => 'Bán đảo Sơn Trà',
            'slug' => 'ban-dao-son-tra',
            'rating' => 4.8,
        ]);

        // Địa điểm liên quan 1: Cùng thành phố Đà Nẵng
        $relatedByCity = Destination::factory()->create([
            'city_id' => $cityDN->id,
            'category_id' => $catVanHoa->id,
            'name' => 'Ngũ Hành Sơn Đà Nẵng',
            'slug' => 'ngu-hanh-son-da-nang',
            'rating' => 4.6,
        ]);

        // Địa điểm liên quan 2: Cùng danh mục Biển đảo ở tỉnh khác
        $relatedByCategory = Destination::factory()->create([
            'city_id' => $cityHN->id,
            'category_id' => $catBien->id,
            'name' => 'Hồ Tây Lộng Gió',
            'slug' => 'ho-tay-long-gio',
            'rating' => 4.5,
        ]);

        $response = $this->get(route('destinations.show', $currentDest->slug));

        $response->assertStatus(200);
        $response->assertSee('📍 Địa Điểm Khám Phá Liên Quan');
        // Phải nhìn thấy các địa điểm liên quan
        $response->assertSee('Ngũ Hành Sơn Đà Nẵng');
        $response->assertSee('Hồ Tây Lộng Gió');
        // Phải chứa link dẫn tới trang chi tiết của địa điểm liên quan
        $response->assertSee(route('destinations.show', $relatedByCity->slug));
        $response->assertSee(route('destinations.show', $relatedByCategory->slug));
    }

    /**
     * Kiểm thử hiển thị trạng thái trống khi không có địa điểm liên quan nào khác.
     */
    public function test_destination_show_page_handles_no_related_destinations_gracefully(): void
    {
        $city = City::factory()->create(['name' => 'Côn Đảo', 'slug' => 'con-dao']);
        $category = Category::factory()->create(['name' => 'Lịch sử Côn Đảo', 'slug' => 'lich-su-con-dao']);

        // Chỉ có 1 địa điểm duy nhất trong hệ thống
        $destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Nhà tù Côn Đảo',
            'slug' => 'nha-tu-con-dao',
        ]);

        $response = $this->get(route('destinations.show', $destination->slug));

        $response->assertStatus(200);
        $response->assertSee('Chưa có địa điểm liên quan nào khác tại khu vực này');
    }
}
