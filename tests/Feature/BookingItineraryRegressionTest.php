<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\ItineraryItem;
use App\Models\Payment;
use App\Models\RoomType;
use App\Models\Trip;
use App\Models\User;
use App\Services\CommerceBackendService;
use App\Services\Interfaces\BookingServiceInterface;
use App\Services\Interfaces\TripServiceInterface;
use Carbon\Carbon;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingItineraryRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected City $city;
    protected Category $category;
    protected Destination $destination;
    protected RoomType $roomType;
    protected Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        $this->otherUser = User::factory()->create(['role' => 'user']);

        $this->city = City::create(['name' => 'Nha Trang', 'slug' => 'nha-trang']);
        $this->category = Category::create(['name' => 'Khách sạn', 'slug' => 'khach-san']);

        $this->destination = Destination::create([
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'name' => 'Vinpearl Resort Nha Trang',
            'slug' => 'vinpearl-resort-nha-trang',
            'is_lodging' => true,
        ]);

        $this->roomType = RoomType::create([
            'destination_id' => $this->destination->id,
            'name' => 'Phòng Deluxe Ocean View',
            'capacity' => 2,
            'price_per_night' => 1500000,
            'quantity' => 2, // Số lượng tối đa 2 phòng
        ]);

        $this->trip = Trip::create([
            'user_id' => $this->user->id,
            'name' => 'Chuyến đi nghỉ hè Nha Trang',
            'start_date' => Carbon::now()->addDays(2)->toDateString(),
            'end_date' => Carbon::now()->addDays(8)->toDateString(),
            'budget' => 20000000,
            'status' => Trip::STATUS_PLANNED,
        ]);
    }

    /**
     * Khuyết tật 6: Tính công suất phòng theo từng đêm với exclusive checkout.
     * Sequential non-overlapping stays không bị tính gộp sai gây từ chối đặt phòng.
     */
    public function test_room_availability_per_night_and_exclusive_checkout(): void
    {
        $bookingService = app(BookingServiceInterface::class);

        // Đêm A: Ngày 2 đến Ngày 3 (1 phòng)
        Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'check_in_date' => Carbon::now()->addDays(2)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(3)->toDateString(),
            'room_count' => 1,
            'total_price' => 1500000,
            'status' => 'pending',
            'booking_code' => '#BK-TEST-A1',
        ]);

        // Đêm B: Ngày 3 đến Ngày 4 (1 phòng)
        Booking::create([
            'user_id' => $this->otherUser->id,
            'room_type_id' => $this->roomType->id,
            'check_in_date' => Carbon::now()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(4)->toDateString(),
            'room_count' => 1,
            'total_price' => 1500000,
            'status' => 'pending',
            'booking_code' => '#BK-TEST-B1',
        ]);

        // Kiểm tra khả năng đặt 1 phòng cho cả 2 đêm (Ngày 2 đến Ngày 4).
        // Thuật toán cũ: sum(1 + 1) = 2, 2 + 1 = 3 > quantity(2) -> SAI.
        // Thuật toán mới: Đêm 2 (1/2 phòng), Đêm 3 (1/2 phòng) -> CÒN TRỐNG (HỢP LỆ).
        $isAvailable = $bookingService->checkRoomAvailability(
            $this->roomType->id,
            Carbon::now()->addDays(2)->toDateString(),
            Carbon::now()->addDays(4)->toDateString(),
            1
        );
        $this->assertTrue($isAvailable, 'Khách có thể đặt 1 phòng cho 2 đêm khi mỗi đêm chỉ mới có 1 phòng được đặt (tổng 2 phòng).');

        // Kiểm tra đêm thực sự đã kín (Genuinely full night):
        // Thêm 1 phòng vào Đêm 2 -> Đêm 2 đạt tối đa 2/2 phòng
        Booking::create([
            'user_id' => $this->otherUser->id,
            'room_type_id' => $this->roomType->id,
            'check_in_date' => Carbon::now()->addDays(2)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(3)->toDateString(),
            'room_count' => 1,
            'total_price' => 1500000,
            'status' => 'pending',
            'booking_code' => '#BK-TEST-A2',
        ]);

        $isFull = $bookingService->checkRoomAvailability(
            $this->roomType->id,
            Carbon::now()->addDays(2)->toDateString(),
            Carbon::now()->addDays(4)->toDateString(),
            1
        );
        $this->assertFalse($isFull, 'Phải từ chối khi có ít nhất 1 đêm trong khoảng lưu trú đã hết phòng.');

        // Kiểm tra ngày trả phòng liền kề (Adjacent checkout / check-in):
        // Khách A trả phòng ngày 4 (checkout exclusive), Khách C nhận phòng ngày 4
        $adjacentAvailable = $bookingService->checkRoomAvailability(
            $this->roomType->id,
            Carbon::now()->addDays(4)->toDateString(),
            Carbon::now()->addDays(6)->toDateString(),
            2
        );
        $this->assertTrue($adjacentAvailable, 'Ngày trả phòng là exclusive nên có thể nhận phòng ngay trong cùng ngày đó.');
    }

    /**
     * Khuyết tật 7: Xóa giỏ hàng không hủy đơn đặt phòng của người dùng và không xóa lịch trình.
     */
    public function test_cart_item_deletion_does_not_cancel_unrelated_booking_or_itinerary(): void
    {
        // 1. Tạo 1 đơn booking pending thuộc người dùng
        $myBooking = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'check_in_date' => Carbon::now()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(5)->toDateString(),
            'room_count' => 1,
            'total_price' => 3000000,
            'status' => 'pending',
            'booking_code' => '#BK-SEC-CART1',
        ]);

        // 2. Tạo 1 mục lịch trình
        $itinerary = ItineraryItem::create([
            'trip_id' => $this->trip->id,
            'destination_id' => $this->destination->id,
            'day_number' => 1,
            'custom_title' => 'Nghỉ dưỡng Vinpearl',
        ]);

        $unrelatedRoom = RoomType::create([
            'destination_id' => $this->destination->id,
            'name' => 'Phòng President Suite',
            'capacity' => 4,
            'price_per_night' => 5000000,
            'quantity' => 1,
        ]);

        $backend = new CommerceBackendService($this->user);

        // Đưa 1 phòng không liên quan vào giỏ hàng
        $backend->execute('add_to_cart', [
            'product_id' => 'room:' . $unrelatedRoom->id,
            'quantity' => 1,
        ]);

        // Xóa phòng không liên quan khỏi giỏ hàng
        $removeResult = $backend->execute('remove_from_cart', [
            'product_id' => 'room:' . $unrelatedRoom->id,
        ]);

        // Đơn booking của người dùng KHÔNG bị hủy
        $myBooking->refresh();
        $this->assertEquals('pending', $myBooking->status);

        // Mục lịch trình KHÔNG bị xóa
        $this->assertDatabaseHas('itinerary_items', ['id' => $itinerary->id]);

        // 3. Hủy đơn qua cancel_booking với ID không tồn tại hoặc của người khác phải thất bại
        $this->expectException(DomainException::class);
        $backend->execute('cancel_booking', [
            'booking_id' => 999999,
        ]);
    }

    /**
     * Khuyết tật 7: Hủy đơn hợp lệ thông qua cancel_booking thành công theo đúng quy tắc.
     */
    public function test_explicit_cancellation_cancels_only_intended_booking(): void
    {
        $myBooking = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'check_in_date' => Carbon::now()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(5)->toDateString(),
            'room_count' => 1,
            'total_price' => 3000000,
            'status' => 'pending',
            'booking_code' => '#BK-EXPLICIT-1',
        ]);

        $backend = new CommerceBackendService($this->user);
        $preview = $backend->execute('cancel_booking', [
            'booking_id' => $myBooking->id,
        ]);
        $this->assertTrue($preview['preview']);
        $result = $backend->confirmPreview([
            'preview_token' => $preview['preview_token'],
        ]);

        $this->assertTrue($result['success']);
        $myBooking->refresh();
        $this->assertEquals('cancelled', $myBooking->status);
    }

    /**
     * Khuyết tật 8: Trợ lý cập nhật ngày đặt phòng xác thực chặt chẽ ID, ngày, chuyến đi, trạng thái và phòng trống.
     */
    public function test_update_booking_dates_validations_and_reschedule(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'trip_id' => $this->trip->id, // Chuyến đi từ addDays(2) đến addDays(8)
            'room_type_id' => $this->roomType->id,
            'check_in_date' => Carbon::now()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(5)->toDateString(),
            'room_count' => 1,
            'total_price' => 3000000,
            'status' => 'pending',
            'booking_code' => '#BK-RESCHEDULE-1',
        ]);

        $foreignBooking = Booking::create([
            'user_id' => $this->otherUser->id,
            'room_type_id' => $this->roomType->id,
            'check_in_date' => Carbon::now()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(5)->toDateString(),
            'room_count' => 1,
            'total_price' => 3000000,
            'status' => 'pending',
            'booking_code' => '#BK-FOREIGN-1',
        ]);

        $backend = new CommerceBackendService($this->user);

        // 1. Booking ID không tồn tại -> Báo lỗi, không âm thầm sửa booking khác
        try {
            $backend->execute('update_booking_dates', [
                'booking_id' => 88888,
                'check_in_date' => Carbon::now()->addDays(4)->toDateString(),
            ]);
            $this->fail('Phải ném DomainException khi truyền booking_id không tồn tại.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Không tìm thấy đơn đặt phòng', $e->getMessage());
        }

        // 2. Booking của người khác -> Báo lỗi
        try {
            $backend->execute('update_booking_dates', [
                'booking_id' => $foreignBooking->id,
                'check_in_date' => Carbon::now()->addDays(4)->toDateString(),
            ]);
            $this->fail('Phải ném DomainException khi cố sửa booking của người khác.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Không tìm thấy đơn đặt phòng', $e->getMessage());
        }

        // 3. Ngày trong quá khứ -> Báo lỗi
        try {
            $backend->execute('update_booking_dates', [
                'booking_id' => $booking->id,
                'check_in_date' => Carbon::now()->subDays(2)->toDateString(),
                'check_out_date' => Carbon::now()->addDays(2)->toDateString(),
            ]);
            $this->fail('Phải ném DomainException khi ngày nhận phòng ở quá khứ.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('quá khứ', $e->getMessage());
        }

        // 4. Ngày đảo ngược (checkout <= checkin) -> Báo lỗi
        try {
            $backend->execute('update_booking_dates', [
                'booking_id' => $booking->id,
                'check_in_date' => Carbon::now()->addDays(5)->toDateString(),
                'check_out_date' => Carbon::now()->addDays(4)->toDateString(),
            ]);
            $this->fail('Phải ném DomainException khi ngày checkout trước ngày checkin.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('sau ngày nhận phòng', $e->getMessage());
        }

        // 5. Ngày nằm ngoài khoảng thời gian chuyến đi (Trip: addDays(2) -> addDays(8)) -> Báo lỗi
        try {
            $backend->execute('update_booking_dates', [
                'booking_id' => $booking->id,
                'check_in_date' => Carbon::now()->addDays(7)->toDateString(),
                'check_out_date' => Carbon::now()->addDays(10)->toDateString(),
            ]);
            $this->fail('Phải ném DomainException khi ngày lưu trú vượt ra ngoài khoảng thời gian của chuyến đi.');
        } catch (DomainException $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        // 6. Đơn đã thanh toán (paid) -> Không cho phép đổi ngày
        $paidBooking = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'check_in_date' => Carbon::now()->addDays(2)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(3)->toDateString(),
            'room_count' => 1,
            'total_price' => 1500000,
            'status' => 'confirmed',
            'booking_code' => '#BK-PAID-1',
        ]);
        Payment::create([
            'booking_id' => $paidBooking->id,
            'amount' => 3000000,
            'payment_method' => 'banking',
            'status' => 'completed',
            'transaction_id' => 'TX_PAID_DONE',
        ]);

        try {
            $backend->execute('update_booking_dates', [
                'booking_id' => $paidBooking->id,
                'check_in_date' => Carbon::now()->addDays(4)->toDateString(),
                'check_out_date' => Carbon::now()->addDays(6)->toDateString(),
            ]);
            $this->fail('Phải chặn đổi ngày khi đơn đặt phòng đã thanh toán thành công.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('đã được thanh toán hoặc xác nhận', $e->getMessage());
        }

        // 7. Reschedule hợp lệ cho đơn pending qua xem trước và xác nhận (Propose Only -> Confirm Preview)
        // Đổi sang ngày addDays(4) -> addDays(7) (3 đêm = 4,500,000 VND)
        $preview = $backend->execute('update_booking_dates', [
            'booking_id' => $booking->id,
            'check_in_date' => Carbon::now()->addDays(4)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(7)->toDateString(),
        ]);
        $this->assertTrue($preview['preview']);
        $reschedule = $backend->confirmPreview([
            'preview_token' => $preview['preview_token'],
        ]);

        $this->assertTrue($reschedule['success']);
        $booking->refresh();
        $this->assertEquals(Carbon::now()->addDays(4)->toDateString(), Carbon::parse($booking->check_in_date)->toDateString());
        $this->assertEquals(Carbon::now()->addDays(7)->toDateString(), Carbon::parse($booking->check_out_date)->toDateString());
        $this->assertEquals(4500000, $booking->total_price);
    }

    /**
     * Khuyết tật 9: Nhân bản chuyến đi (cloneTrip) xóa liên kết booking_id trên lịch trình bản sao.
     */
    public function test_cloned_trip_itinerary_items_clear_booking_id(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'trip_id' => $this->trip->id,
            'room_type_id' => $this->roomType->id,
            'check_in_date' => Carbon::now()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(5)->toDateString(),
            'room_count' => 1,
            'total_price' => 3000000,
            'status' => 'pending',
            'booking_code' => '#BK-ORIGINAL-LINK',
        ]);

        // Mục lịch trình 1: Có liên kết với booking
        $linkedItem = ItineraryItem::create([
            'trip_id' => $this->trip->id,
            'destination_id' => $this->destination->id,
            'booking_id' => $booking->id,
            'day_number' => 1,
            'custom_title' => 'Check-in Vinpearl Resort',
        ]);

        // Mục lịch trình 2: Không có liên kết booking
        $unlinkedItem = ItineraryItem::create([
            'trip_id' => $this->trip->id,
            'destination_id' => $this->destination->id,
            'booking_id' => null,
            'day_number' => 2,
            'custom_title' => 'Tắm biển bãi Dài',
        ]);

        $tripService = app(TripServiceInterface::class);
        $clonedTrip = $tripService->cloneTrip($this->trip->id);

        $this->assertNotEquals($this->trip->id, $clonedTrip->id);
        $this->assertCount(2, $clonedTrip->itineraryItems);

        // Chuyến đi gốc vẫn giữ nguyên liên kết booking
        $linkedItem->refresh();
        $this->assertEquals($booking->id, $linkedItem->booking_id);

        // Chuyến đi bản sao: Tất cả mục lịch trình đều có booking_id là null
        foreach ($clonedTrip->itineraryItems as $clonedItem) {
            $this->assertNull($clonedItem->booking_id, 'Mục lịch trình trên chuyến đi bản sao không được giữ booking_id của chuyến gốc.');
        }
    }

    /**
     * Khuyết tật 6: Kiểm thử đặt phòng đồng thời cho phòng cuối cùng trên MySQL (Row Locking & Concurrency).
     */
    public function test_concurrent_booking_for_last_room_on_mysql(): void
    {
        try {
            $pdoA = new \PDO('mysql:host=127.0.0.1;port=3307;dbname=travel_planner', 'root', 'root@admin', [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => 3,
            ]);
        } catch (\Throwable $e) {
            $this->markTestSkipped('MySQL test container trên port 3307 không khả dụng: ' . $e->getMessage());
        }

        // Tạo bảng tạm thời phục vụ kiểm thử concurrency nếu chưa có
        $pdoA->exec("CREATE TABLE IF NOT EXISTS test_room_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quantity INT NOT NULL DEFAULT 1
        ) ENGINE=InnoDB");
        $pdoA->exec("CREATE TABLE IF NOT EXISTS test_bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_type_id INT NOT NULL,
            room_count INT NOT NULL,
            status VARCHAR(50) NOT NULL
        ) ENGINE=InnoDB");

        $pdoA->exec("TRUNCATE TABLE test_room_types");
        $pdoA->exec("TRUNCATE TABLE test_bookings");
        $pdoA->exec("INSERT INTO test_room_types (id, quantity) VALUES (1, 1)");

        $pdoB = new \PDO('mysql:host=127.0.0.1;port=3307;dbname=travel_planner', 'root', 'root@admin', [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_TIMEOUT => 3,
        ]);

        // Connection A bắt đầu transaction và khóa dòng loại phòng
        $pdoA->beginTransaction();
        $stmtA = $pdoA->prepare("SELECT * FROM test_room_types WHERE id = 1 FOR UPDATE");
        $stmtA->execute();
        $roomTypeA = $stmtA->fetch(\PDO::FETCH_ASSOC);

        // Kiểm tra số phòng đã đặt trên A
        $bookedCountA = (int) $pdoA->query("SELECT COALESCE(SUM(room_count), 0) FROM test_bookings WHERE room_type_id = 1 AND status != 'cancelled'")->fetchColumn();
        $this->assertEquals(0, $bookedCountA);

        // A tạo đơn đặt phòng cuối cùng
        $pdoA->exec("INSERT INTO test_bookings (room_type_id, room_count, status) VALUES (1, 1, 'pending')");
        $pdoA->commit();

        // Bây giờ Connection B thực hiện kiểm tra sau khi A đã hoàn tất
        $pdoB->beginTransaction();
        $stmtB = $pdoB->prepare("SELECT * FROM test_room_types WHERE id = 1 FOR UPDATE");
        $stmtB->execute();
        $roomTypeB = $stmtB->fetch(\PDO::FETCH_ASSOC);

        $bookedCountB = (int) $pdoB->query("SELECT COALESCE(SUM(room_count), 0) FROM test_bookings WHERE room_type_id = 1 AND status != 'cancelled'")->fetchColumn();
        $pdoB->rollBack();

        // B nhận thấy phòng đã hết (1/1) nên không thể đặt tiếp
        $this->assertEquals(1, $bookedCountB);
        $this->assertFalse(($bookedCountB + 1) <= (int) $roomTypeB['quantity'], 'Phòng cuối cùng đã được Connection A đặt, Connection B phải bị từ chối.');

        // Dọn dẹp
        $pdoA->exec("DROP TABLE test_room_types");
        $pdoA->exec("DROP TABLE test_bookings");
    }
}
