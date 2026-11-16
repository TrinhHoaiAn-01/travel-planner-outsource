<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm thử tính năng Quản lý Loại phòng lưu trú dành cho Quản trị viên (Mục 10 - Trần Văn Trọng).
 */
class RoomTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $regularUser;
    private Destination $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->regularUser = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $city = City::factory()->create(['name' => 'Phú Quốc']);
        $category = Category::factory()->create(['name' => 'Resort & Khách sạn']);

        $this->destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Vinpearl Resort & Spa Phú Quốc',
            'slug' => 'vinpearl-resort-spa-phu-quoc',
        ]);
    }

    /**
     * Khách vãng lai và người dùng thông thường không có quyền truy cập trang quản lý loại phòng (BR-13, BR-20).
     */
    public function test_guest_and_regular_user_cannot_access_admin_room_types(): void
    {
        $guestResponse = $this->get(route('admin.room-types.index'));
        $guestResponse->assertRedirect(route('login'));

        $userResponse = $this->actingAs($this->regularUser)->get(route('admin.room-types.index'));
        $userResponse->assertStatus(403);
    }

    /**
     * Admin có thể truy cập danh sách loại phòng và xem số liệu thống kê.
     */
    public function test_admin_can_view_room_types_list(): void
    {
        RoomType::factory()->create([
            'destination_id' => $this->destination->id,
            'name' => 'Phòng Deluxe Ocean View',
            'capacity' => 2,
            'price_per_night' => 2500000,
            'quantity' => 15,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.room-types.index'));

        $response->assertStatus(200);
        $response->assertSee('Quản Lý Loại Phòng Lưu Trú');
        $response->assertSee('Phòng Deluxe Ocean View');
        $response->assertSee('Vinpearl Resort & Spa Phú Quốc');
        $response->assertSee('2.500.000đ');
        $response->assertSee('Còn phòng');
    }

    /**
     * Admin có thể thêm mới loại phòng thành công.
     */
    public function test_admin_can_create_new_room_type(): void
    {
        $data = [
            'destination_id' => $this->destination->id,
            'name' => 'Villa Biển 3 Phòng Ngủ',
            'capacity' => 6,
            'price_per_night' => 8500000,
            'quantity' => 5,
            'description' => 'Biệt thự hướng biển có hồ bơi riêng biệt.',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.room-types.store'), $data);

        $response->assertRedirect(route('admin.room-types.index'));
        $response->assertSessionHas('success', 'Thêm mới loại phòng lưu trú thành công.');

        $this->assertDatabaseHas('room_types', [
            'destination_id' => $this->destination->id,
            'name' => 'Villa Biển 3 Phòng Ngủ',
            'capacity' => 6,
            'price_per_night' => 8500000,
            'quantity' => 5,
        ]);
    }

    /**
     * Admin có thể cập nhật thông tin loại phòng.
     */
    public function test_admin_can_update_room_type(): void
    {
        $room = RoomType::factory()->create([
            'destination_id' => $this->destination->id,
            'name' => 'Phòng Tiêu Chuẩn',
            'capacity' => 2,
            'price_per_night' => 1200000,
            'quantity' => 10,
        ]);

        $updatedData = [
            'destination_id' => $this->destination->id,
            'name' => 'Phòng Deluxe Nâng Cấp',
            'capacity' => 3,
            'price_per_night' => 1800000,
            'quantity' => 8,
            'description' => 'Đã nâng cấp nội thất cao cấp.',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.room-types.update', $room->id), $updatedData);

        $response->assertRedirect(route('admin.room-types.index'));
        $response->assertSessionHas('success', 'Cập nhật thông tin loại phòng thành công.');

        $this->assertDatabaseHas('room_types', [
            'id' => $room->id,
            'name' => 'Phòng Deluxe Nâng Cấp',
            'capacity' => 3,
            'price_per_night' => 1800000,
            'quantity' => 8,
        ]);
    }

    /**
     * Admin có thể xóa loại phòng khi chưa có đơn đặt phòng nào hoạt động.
     */
    public function test_admin_can_delete_room_type_without_active_bookings(): void
    {
        $room = RoomType::factory()->create([
            'destination_id' => $this->destination->id,
            'name' => 'Phòng Cần Xóa',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.room-types.destroy', $room->id));

        $response->assertRedirect(route('admin.room-types.index'));
        $response->assertSessionHas('success', 'Xóa loại phòng lưu trú thành công.');

        $this->assertDatabaseMissing('room_types', ['id' => $room->id]);
    }

    /**
     * Không thể xóa loại phòng nếu đang có đơn đặt phòng pending/confirmed (BR-17).
     */
    public function test_admin_cannot_delete_room_type_with_active_bookings(): void
    {
        $room = RoomType::factory()->create([
            'destination_id' => $this->destination->id,
            'name' => 'Phòng Đang Có Khách Đặt',
        ]);

        // Tạo đơn đặt phòng trạng thái confirmed
        Booking::create([
            'user_id' => $this->regularUser->id,
            'room_type_id' => $room->id,
            'room_count' => 1,
            'check_in_date' => now()->addDays(5)->format('Y-m-d'),
            'check_out_date' => now()->addDays(7)->format('Y-m-d'),
            'total_price' => 3000000,
            'status' => 'confirmed',
            'booking_code' => 'BK-TEST-DEL-01',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.room-types.destroy', $room->id));

        $response->assertSessionHasErrors('room_type');
        $this->assertDatabaseHas('room_types', ['id' => $room->id]);
    }
}
