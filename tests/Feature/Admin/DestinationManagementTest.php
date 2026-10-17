<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\ItineraryItem;
use App\Models\Trip;
use App\Models\User;
use Database\Seeders\AdminDestinationDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationManagementTest extends TestCase
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
     * Khách chưa đăng nhập bị chuyển hướng về trang đăng nhập (HTTP 302).
     */
    public function test_guest_cannot_access_admin_destinations(): void
    {
        $response = $this->get(route('admin.destinations.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng thường bị từ chối truy cập (HTTP 403 Forbidden theo BR-13, BR-20).
     */
    public function test_regular_user_cannot_access_admin_destinations(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.destinations.index'));
        $response->assertForbidden();
    }

    /**
     * Quản trị viên truy cập thành công và nhìn thấy đầy đủ giao diện, dữ liệu chuẩn theo ảnh.
     */
    public function test_admin_can_view_destinations_index_page(): void
    {
        $this->seed(AdminDestinationDemoSeeder::class);

        $response = $this->actingAs($this->admin)->get(route('admin.destinations.index'));

        $response->assertOk();
        $response->assertSee('Quản Lý Địa Điểm Du Lịch');
        $response->assertSee('+ Thêm Địa Điểm Mới');
        $response->assertSee('Địa điểm (Destinations)');
        $response->assertSee('Tìm theo tên địa điểm...');
        $response->assertSee('Tất cả thành phố');
        $response->assertSee('Tất cả danh mục');

        // Kiểm tra các cột tiêu đề bảng
        $response->assertSee('Địa điểm');
        $response->assertSee('Thành phố');
        $response->assertSee('Danh mục');
        $response->assertSee('Đánh giá');
        $response->assertSee('Giá vé / Phòng');
        $response->assertSee('Nơi lưu trú');
        $response->assertSee('Hành động');

        $baNa = Destination::where('slug', 'sun-world-ba-na-hills')->firstOrFail();
        $haLong = Destination::where('slug', 'du-thuyen-vinh-ha-long')->firstOrFail();
        $phuQuoc = Destination::where('slug', 'vinpearl-resort-spa-phu-quoc')->firstOrFail();
        $hoiAn = Destination::where('slug', 'pho-co-hoi-an')->firstOrFail();

        // Kiểm tra 4 địa điểm đặc trưng theo ảnh
        $response->assertSee('Sun World Bà Nà Hills');
        $response->assertSee(sprintf('ID: #DST-%03d', $baNa->id));
        $response->assertSee('Đà Nẵng');
        $response->assertSee('900.000đ');

        $response->assertSee('Du Thuyền Vịnh Hạ Long');
        $response->assertSee(sprintf('ID: #DST-%03d', $haLong->id));
        $response->assertSee('Quảng Ninh');
        $response->assertSee('1.500.000đ');

        $response->assertSee('Vinpearl Resort & Spa Phú Quốc');
        $response->assertSee(sprintf('ID: #DST-%03d', $phuQuoc->id));
        $response->assertSee('Phú Quốc');
        $response->assertSee('2.800.000đ');

        $response->assertSee('Phố Cổ Hội An');
        $response->assertSee(sprintf('ID: #DST-%03d', $hoiAn->id));
        $response->assertSee('Hội An');
        $response->assertSee('150.000đ');
        $response->assertSee('Không');
        $response->assertSee('Có khách sạn');
    }

    /**
     * Tìm kiếm địa điểm theo từ khóa tên hoặc địa chỉ.
     */
    public function test_admin_can_search_destinations_by_keyword(): void
    {
        $this->seed(AdminDestinationDemoSeeder::class);

        $response = $this->actingAs($this->admin)->get(route('admin.destinations.index', ['search' => 'Bà Nà Hills']));

        $response->assertOk();
        $response->assertSee('Sun World Bà Nà Hills');
        $response->assertDontSee('Phố Cổ Hội An');
    }

    /**
     * Lọc địa điểm theo Thành phố.
     */
    public function test_admin_can_filter_destinations_by_city(): void
    {
        $this->seed(AdminDestinationDemoSeeder::class);
        $hoiAnCity = City::where('name', 'Hội An')->firstOrFail();

        $response = $this->actingAs($this->admin)->get(route('admin.destinations.index', ['city_id' => $hoiAnCity->id]));

        $response->assertOk();
        $response->assertSee('Phố Cổ Hội An');
        $response->assertDontSee('Du Thuyền Vịnh Hạ Long');
    }

    /**
     * Lọc địa điểm theo Danh mục.
     */
    public function test_admin_can_filter_destinations_by_category(): void
    {
        $this->seed(AdminDestinationDemoSeeder::class);
        $heritageCategory = Category::where('name', 'Di sản')->firstOrFail();

        $response = $this->actingAs($this->admin)->get(route('admin.destinations.index', ['category_id' => $heritageCategory->id]));

        $response->assertOk();
        $response->assertSee('Phố Cổ Hội An');
        $response->assertDontSee('Sun World Bà Nà Hills');
    }

    /**
     * Thêm mới một địa điểm du lịch thành công.
     */
    public function test_admin_can_create_new_destination_successfully(): void
    {
        $city = City::factory()->create(['name' => 'Nha Trang']);
        $category = Category::factory()->create(['name' => 'Biển đảo']);

        $payload = [
            'name' => 'VinWonders Nha Trang',
            'city_id' => $city->id,
            'category_id' => $category->id,
            'entrance_fee' => 880000,
            'rating' => 4.9,
            'opening_time' => '08:30',
            'closing_time' => '21:00',
            'duration' => 360,
            'address' => 'Đảo Hòn Tre, Vĩnh Nguyên, Nha Trang',
            'description' => 'Công viên giải trí của những kỷ lục tọa lạc trên đảo Hòn Tre.',
            'has_hotel' => 1,
            'is_featured' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.destinations.store'), $payload);

        $response->assertRedirect(route('admin.destinations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('destinations', [
            'name' => 'VinWonders Nha Trang',
            'city_id' => $city->id,
            'category_id' => $category->id,
            'entrance_fee' => 880000,
            'is_featured' => true,
        ]);
    }

    /**
     * Kiểm tra validation khi gửi dữ liệu không hợp lệ.
     */
    public function test_destination_validation_rejects_missing_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.destinations.store'), [
            'name' => '',
            'city_id' => 99999, // Không tồn tại
            'category_id' => 99999, // Không tồn tại
        ]);

        $response->assertSessionHasErrors(['name', 'city_id', 'category_id']);
    }

    /**
     * Lấy thông tin chi tiết qua AJAX JSON cho Modal xem/sửa.
     */
    public function test_admin_can_view_destination_details_via_ajax(): void
    {
        $this->seed(AdminDestinationDemoSeeder::class);
        $dest = Destination::firstOrFail();

        $response = $this->actingAs($this->admin)->getJson(route('admin.destinations.show', $dest->id));

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.name', $dest->name);
        $response->assertJsonPath('data.code', sprintf('#DST-%03d', $dest->id));
    }

    /**
     * Cập nhật thông tin địa điểm du lịch thành công.
     */
    public function test_admin_can_update_destination_successfully(): void
    {
        $this->seed(AdminDestinationDemoSeeder::class);
        $dest = Destination::where('slug', 'sun-world-ba-na-hills')->firstOrFail();

        $payload = [
            'name' => 'Sun World Bà Nà Hills (Đã Cập Nhật)',
            'city_id' => $dest->city_id,
            'category_id' => $dest->category_id,
            'entrance_fee' => 950000,
            'rating' => 5.0,
            'opening_time' => '07:30',
            'closing_time' => '22:30',
            'address' => 'Hòa Vang, Đà Nẵng',
            'description' => 'Mô tả cập nhật mới.',
            'has_hotel' => 1,
            'is_featured' => 1,
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.destinations.update', $dest->id), $payload);

        $response->assertRedirect(route('admin.destinations.index'));
        $response->assertSessionHas('success');

        $dest->refresh();
        $this->assertEquals('Sun World Bà Nà Hills (Đã Cập Nhật)', $dest->name);
        $this->assertEquals(950000, (float) $dest->entrance_fee);
        $this->assertEquals(5.0, (float) $dest->rating);
    }

    /**
     * Xóa địa điểm an toàn khi không có ràng buộc dữ liệu.
     */
    public function test_admin_can_delete_destination_successfully(): void
    {
        $city = City::factory()->create();
        $category = Category::factory()->create();
        $dest = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Điểm đến tạm thời',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.destinations.destroy', $dest->id));

        $response->assertRedirect(route('admin.destinations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('destinations', ['id' => $dest->id]);
    }

    /**
     * Bảo vệ toàn vẹn dữ liệu BR-17: Không cho phép xóa địa điểm nếu đang nằm trong lịch trình du lịch.
     */
    public function test_cannot_delete_destination_with_active_itinerary_items_due_to_br17(): void
    {
        $this->seed(AdminDestinationDemoSeeder::class);
        $dest = Destination::firstOrFail();

        // Tạo chuyến đi và hoạt động lịch trình gắn với địa điểm này
        $trip = Trip::create([
            'user_id' => $this->user->id,
            'name' => 'Chuyến đi thử nghiệm',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(8)->toDateString(),
            'budget' => 5000000,
            'status' => Trip::STATUS_PLANNED,
        ]);

        ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $dest->id,
            'day_number' => 1,
            'order_index' => 1,
            'activity_name' => 'Tham quan địa điểm',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.destinations.destroy', $dest->id));

        $response->assertRedirect(route('admin.destinations.index'));
        $response->assertSessionHas('error');

        // Bản ghi vẫn tồn tại trong Database
        $this->assertDatabaseHas('destinations', ['id' => $dest->id]);
    }
}
