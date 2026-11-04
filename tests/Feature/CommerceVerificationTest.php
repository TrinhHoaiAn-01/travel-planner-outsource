<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\RoomType;
use App\Models\Trip;
use App\Models\User;
use App\Services\CommerceBackendService;
use App\Services\Interfaces\BookingServiceInterface;
use App\Services\Interfaces\PaymentServiceInterface;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CommerceVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    /**
     * 1. Kiểm tra xác nhận thanh toán:
     * - Khi Admin chưa duyệt: báo pending, thông báo vui lòng thanh toán, trả đủ thông tin phòng, giá, ngày (không bị 0 VNĐ hay mất tên)
     * - Khi Admin đã duyệt: báo confirmed, is_paid = true, trả đủ thông tin
     */
    public function test_payment_admin_approval_verification_and_full_payload(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create(['name' => 'Phú Quốc']);
        $destination = Destination::factory()->create([
            'city_id' => $city->id,
            'name' => 'Vinpearl Resort & Spa Phú Quốc',
        ]);
        $room = RoomType::factory()->create([
            'destination_id' => $destination->id,
            'name' => 'Phòng Deluxe King Hướng Biển',
            'price_per_night' => 2800000,
            'quantity' => 10,
        ]);

        $backend = new CommerceBackendService($user);
        $booking = $backend->execute('book_hotel_room', [
            'destination_name' => 'Vinpearl Resort & Spa Phú Quốc',
            'nights' => 3,
            'check_in_date' => '2026-11-10',
            'check_out_date' => '2026-11-13',
            'room_count' => 1,
        ]);

        $this->assertSame(8400000.0, (float) $booking['total_price']);
        $this->assertSame('pending', $booking['status']);

        // Khách ấn xác nhận chuyển khoản KHI ADMIN CHƯA DUYỆT
        $unapproved = $backend->execute('confirm_bank_transfer', [
            'booking_id' => $booking['booking_id'],
        ]);

        $this->assertTrue($unapproved['success']);
        $this->assertSame('pending', $unapproved['status']);
        $this->assertFalse($unapproved['is_paid']);
        $this->assertStringContainsString('chưa được Quản trị viên (Admin) duyệt', $unapproved['message']);
        // Payload đầy đủ, không bị rỗng hay 0 VNĐ
        $this->assertSame('Vinpearl Resort & Spa Phú Quốc', $unapproved['hotel_name']);
        $this->assertSame('Phòng Deluxe King Hướng Biển', $unapproved['room_name']);
        $this->assertSame(3, $unapproved['nights']);
        $this->assertSame(8400000.0, (float) $unapproved['total_price']);
        $this->assertNotEmpty($unapproved['payment_instructions']);

        // Admin duyệt thanh toán
        $paymentService = app(PaymentServiceInterface::class);
        $payment = $paymentService->processPayment(\App\Models\Booking::find($booking['booking_id']), [
            'amount' => 8400000,
            'payment_method' => 'bank_transfer',
        ]);
        $paymentService->updatePaymentStatus($payment->id, 'completed');

        // Khách xác nhận lại sau khi Admin đã duyệt
        $approved = $backend->execute('confirm_bank_transfer', [
            'booking_id' => $booking['booking_id'],
        ]);

        $this->assertTrue($approved['success']);
        $this->assertSame('confirmed', $approved['status']);
        $this->assertTrue($approved['is_paid']);
        $this->assertStringContainsString('đã xác nhận thanh toán thành công', $approved['message']);
        $this->assertSame('Vinpearl Resort & Spa Phú Quốc', $approved['hotel_name']);
        $this->assertSame('Phòng Deluxe King Hướng Biển', $approved['room_name']);
        $this->assertSame(3, $approved['nights']);
        $this->assertSame(8400000.0, (float) $approved['total_price']);
    }

    /**
     * 2. Kiểm tra Kế hoạch chuyến đi (TripPanel drawer / cart):
     * Phải lấy được đầy đủ các mục thực tế trong database của người dùng (điểm tham quan trong Trip, phòng khách sạn đã đặt)
     * và tính đúng tổng giá tạm tính.
     */
    public function test_trip_plan_drawer_loads_user_data_and_calculates_total(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create(['name' => 'Đà Nẵng']);
        $dest1 = Destination::factory()->create([
            'city_id' => $city->id,
            'name' => 'Bán đảo Sơn Trà & Chùa Linh Ứng',
            'entrance_fee' => 50000,
        ]);
        $dest2 = Destination::factory()->create([
            'city_id' => $city->id,
            'name' => 'Sun World Bà Nà Hills',
            'entrance_fee' => 900000,
        ]);
        $room = RoomType::factory()->create([
            'destination_id' => $dest2->id,
            'name' => 'Mercure Danang French Village Bana Hills',
            'price_per_night' => 1200000,
            'quantity' => 10,
        ]);

        $backend = new CommerceBackendService($user);
        // Thêm điểm đến vào chuyến đi
        $backend->execute('add_destination_to_trip', [
            'destination_name' => 'Bán đảo Sơn Trà & Chùa Linh Ứng',
            'day_number' => 1,
        ]);

        // Đặt phòng khách sạn 2 đêm
        $backend->execute('book_hotel_room', [
            'destination_name' => 'Sun World Bà Nà Hills',
            'nights' => 2,
            'check_in_date' => '2026-11-15',
            'check_out_date' => '2026-11-17',
            'room_count' => 1,
        ]);

        // Kiểm tra giỏ hàng kế hoạch qua API
        $response = $this->actingAs($user)->getJson('/commerce-api/shopping/cart')->assertOk();
        $cart = $response->json();

        $this->assertNotEmpty($cart['items']);
        $this->assertCount(2, $cart['items']);

        // Item 1: Điểm tham quan
        $destItem = collect($cart['items'])->firstWhere('product_id', 'destination:'.$dest1->id);
        $this->assertNotNull($destItem);
        $this->assertSame('Bán đảo Sơn Trà & Chùa Linh Ứng', $destItem['title']);
        $this->assertSame(50000.0, (float) $destItem['price']);

        // Item 2: Phòng lưu trú 2 đêm
        $roomItem = collect($cart['items'])->firstWhere('product_id', 'room:'.$room->id);
        $this->assertNotNull($roomItem);
        $this->assertStringContainsString('Mercure Danang French Village Bana Hills', $roomItem['title']);
        $this->assertSame(2, $roomItem['quantity']);
        $this->assertSame(2400000.0, (float) $roomItem['line_total']);

        // Tổng tạm tính
        $this->assertSame(2450000.0, (float) $cart['subtotal']);
    }

    /**
     * 3. Kiểm tra Trí nhớ AI (Memory):
     * Lưu trữ đầy đủ thông tin sau các cuộc trò chuyện và bền vững qua Cache
     */
    public function test_ai_memory_persistence_and_retrieval(): void
    {
        $user = User::factory()->create();
        $cacheKey = "user_memory:{$user->id}";

        // Giả lập lưu trí nhớ từ conversation vào Cache
        $memoryFacts = [
            ['key' => 'fav_city_hà_nội', 'value' => 'Điểm đến quan tâm: Hà Nội', 'category' => 'preference', 'source' => 'sess_1'],
            ['key' => 'budget_limit', 'value' => 'Ngân sách dự kiến: 5 triệu', 'category' => 'constraint', 'source' => 'sess_1'],
            ['key' => 'trip_length', 'value' => 'Thời gian chuyến đi: 3 ngày 2 đêm', 'category' => 'preference', 'source' => 'sess_1'],
            ['key' => 'party_kids', 'value' => 'Thành viên chuyến đi: Có trẻ nhỏ', 'category' => 'constraint', 'source' => 'sess_1'],
            ['key' => 'beach_preference', 'value' => 'Sở thích: Thích nghỉ ngơi gần bãi biển', 'category' => 'preference', 'source' => 'sess_1'],
        ];

        Cache::forever($cacheKey, $memoryFacts);

        // Lấy lại qua GET:memory
        $response = $this->actingAs($user)->getJson('/commerce-api/shopping/memory')->assertOk();
        $facts = $response->json('facts');
        $factValues = array_column($facts, 'value');
        $this->assertContains('Điểm đến quan tâm: Hà Nội', $factValues);
        $this->assertContains('Ngân sách dự kiến: 5 triệu', $factValues);
        $this->assertContains('Thời gian chuyến đi: 3 ngày 2 đêm', $factValues);
        $this->assertContains('Thành viên chuyến đi: Có trẻ nhỏ', $factValues);
        $this->assertContains('Sở thích: Thích nghỉ ngơi gần bãi biển', $factValues);
    }

    /**
     * 4. Kiểm tra bookHotelRoom với danh lam thắng cảnh không có phòng:
     * Không được tự tiện đặt bừa Bà Nà Hills
     */
    public function test_book_hotel_room_refuses_hallucinating_random_hotel(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create(['name' => 'Quảng Ninh (Hạ Long)']);
        $cave = Destination::factory()->create([
            'city_id' => $city->id,
            'name' => 'Hang Sửng Sốt Vịnh Hạ Long',
        ]);

        $backend = new CommerceBackendService($user);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('chưa có loại phòng lưu trú khả dụng');

        $backend->execute('book_hotel_room', [
            'destination_name' => 'Hang Sửng Sốt Vịnh Hạ Long',
        ]);
    }

    /**
     * 5. Kiểm tra xóa kế hoạch chuyến đi (Destination & Booking):
     * - Xóa điểm đến khỏi chuyến đi: remove_destination_from_trip & remove_from_cart
     * - Xóa / hủy đặt phòng khách sạn: cancel_booking & DELETE /commerce-api/shopping/cart
     */
    public function test_delete_destination_and_hotel_from_trip_plan_removes_from_cart_and_database(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create(['name' => 'Đà Nẵng']);
        $destination = Destination::factory()->create([
            'city_id' => $city->id,
            'name' => 'Bán đảo Sơn Trà & Chùa Linh Ứng',
            'entrance_fee' => 0,
        ]);
        $hotel = Destination::factory()->create([
            'city_id' => $city->id,
            'name' => 'Sun World Bà Nà Hills - Mercure Danang French Village Bana Hills',
        ]);
        $room = RoomType::factory()->create([
            'destination_id' => $hotel->id,
            'name' => 'Phòng Superior King',
            'price_per_night' => 900000,
            'quantity' => 5,
        ]);

        $backend = new CommerceBackendService($user);

        // 1. Thêm điểm đến vào chuyến đi
        $tripRes = $backend->execute('add_destination_to_trip', [
            'destination_name' => 'Bán đảo Sơn Trà & Chùa Linh Ứng',
            'day_number' => 1,
        ]);
        $this->assertTrue($tripRes['success']);

        // 2. Đặt phòng khách sạn
        $bookingRes = $backend->execute('book_hotel_room', [
            'destination_name' => 'Sun World Bà Nà Hills - Mercure Danang French Village Bana Hills',
            'nights' => 3,
            'check_in_date' => '2026-11-10',
            'check_out_date' => '2026-11-13',
            'room_count' => 1,
        ]);
        $this->assertSame('pending', $bookingRes['status']);

        // 3. Kiểm tra giỏ hàng có 2 mục
        $cartBefore = $backend->execute('get_cart', []);
        $this->assertCount(2, $cartBefore['items']);

        // 4. Xóa điểm đến khỏi chuyến đi bằng tên
        $removeDest = $backend->execute('remove_destination_from_trip', [
            'destination_name' => 'Bán đảo Sơn Trà & Chùa Linh Ứng',
        ]);
        $this->assertTrue($removeDest['success']);

        // Kiểm tra trong database itinerary_items đã bị xóa
        $this->assertDatabaseMissing('itinerary_items', [
            'destination_id' => $destination->id,
        ]);

        // Giỏ hàng chỉ còn 1 mục (phòng khách sạn)
        $cartMid = $backend->execute('get_cart', []);
        $this->assertCount(1, $cartMid['items']);
        $this->assertSame('room:' . $room->id, $cartMid['items'][0]['product_id']);

        // 5. Hủy/Xóa phòng khách sạn bằng cancel_booking
        $cancelBooking = $backend->execute('cancel_booking', [
            'hotel_name' => 'Sun World Bà Nà Hills',
        ]);
        $this->assertTrue($cancelBooking['success']);

        // Kiểm tra trong database booking đã chuyển sang cancelled
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingRes['booking_id'],
            'status' => 'cancelled',
        ]);

        // Giỏ hàng bây giờ hoàn toàn trống (0 mục)
        $cartAfter = $backend->execute('get_cart', []);
        $this->assertCount(0, $cartAfter['items']);
        $this->assertSame(0.0, (float) $cartAfter['subtotal']);

        // 6. Test qua API DELETE /commerce-api/shopping/cart
        // Tạo lại 1 booking mới
        $booking2 = $backend->execute('book_hotel_room', [
            'destination_name' => 'Sun World Bà Nà Hills - Mercure Danang French Village Bana Hills',
            'nights' => 2,
        ]);
        $this->assertCount(1, $backend->execute('get_cart', [])['items']);

        // Gọi API DELETE /commerce-api/shopping/cart
        $response = $this->actingAs($user)
            ->withSession(['commerce_shopping' => ['identity' => 'shopping:' . $user->id]])
            ->deleteJson('/commerce-api/shopping/cart', [
                'product_id' => 'room:' . $room->id,
            ]);

        $response->assertOk();
        $deletedCart = $response->json();
        $this->assertCount(0, $deletedCart['items']);
        $this->assertSame(0, $deletedCart['subtotal']);

        // Đơn booking thứ 2 cũng đã được cập nhật thành cancelled
        $this->assertDatabaseHas('bookings', [
            'id' => $booking2['booking_id'],
            'status' => 'cancelled',
        ]);
    }
}
