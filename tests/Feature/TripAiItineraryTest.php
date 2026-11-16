<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\ItineraryItem;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm thử tính năng AI tự động gợi ý và sinh lịch trình cho chuyến đi (Mục 11 - Trần Văn Trọng).
 */
class TripAiItineraryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private User $admin;
    private City $danangCity;
    private Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $this->otherUser = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->danangCity = City::factory()->create(['name' => 'Đà Nẵng']);
        $cat = Category::factory()->create(['name' => 'Bãi biển']);

        // Tạo sẵn một số điểm đến tại Đà Nẵng
        for ($i = 1; $i <= 6; $i++) {
            Destination::factory()->create([
                'city_id' => $this->danangCity->id,
                'category_id' => $cat->id,
                'name' => "Điểm đến Đà Nẵng {$i}",
                'rating' => 4.5 + ($i * 0.05),
                'entrance_fee' => 0,
            ]);
        }

        // Tạo chuyến đi 3 ngày tại Đà Nẵng
        $this->trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Kỳ nghỉ tuyệt vời tại Đà Nẵng',
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'), // 3 ngày
            'status' => 'planned',
        ]);
    }

    /**
     * Khách chưa đăng nhập không thể gọi API sinh lịch trình bằng AI.
     */
    public function test_guest_cannot_generate_ai_itinerary(): void
    {
        $response = $this->post(route('trips.ai-itinerary.generate', $this->trip->id), [
            'style' => 'balanced',
            'mode' => 'replace',
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng khác không thể tự ý sinh hoặc ghi đè lịch trình của chuyến đi không thuộc về mình (BR-03).
     */
    public function test_other_user_cannot_generate_ai_itinerary(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->post(route('trips.ai-itinerary.generate', $this->trip->id), [
                'style' => 'balanced',
                'mode' => 'replace',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Chủ sở hữu chuyến đi có thể tự động sinh lịch trình bằng AI thành công.
     */
    public function test_owner_can_generate_smart_itinerary_successfully(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('trips.ai-itinerary.generate', $this->trip->id), [
                'style' => 'balanced',
                'mode' => 'replace',
            ]);

        $response->assertRedirect(route('trips.show', $this->trip->id));
        $response->assertSessionHas('success');

        // Chuyến đi 3 ngày, phong cách balanced: mỗi ngày có 3 hoạt động -> tổng 9 hoạt động
        $items = ItineraryItem::where('trip_id', $this->trip->id)->get();
        $this->assertGreaterThanOrEqual(3, $items->count());

        // Kiểm tra phân bổ ngày
        $this->assertTrue($items->contains('day_number', 1));
        $this->assertTrue($items->contains('day_number', 2));
        $this->assertTrue($items->contains('day_number', 3));

        // Kiểm tra có ghi chú AI gợi ý
        $firstItem = $items->first();
        $this->assertNotEmpty($firstItem->note);
        $this->assertNotEmpty($firstItem->start_time);
        $this->assertNotEmpty($firstItem->end_time);
    }

    /**
     * Chế độ fill_empty giữ nguyên các hoạt động đã có và chỉ điền vào các ngày còn trống.
     */
    public function test_fill_empty_mode_preserves_existing_activities(): void
    {
        $existingItem = ItineraryItem::create([
            'trip_id' => $this->trip->id,
            'day_number' => 1,
            'destination_id' => null,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'note' => 'Hoạt động tự chọn của người dùng',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('trips.ai-itinerary.generate', $this->trip->id), [
                'style' => 'relaxed',
                'mode' => 'fill_empty',
            ]);

        $response->assertRedirect(route('trips.show', $this->trip->id));

        // Hoạt động cũ ở ngày 1 vẫn tồn tại
        $this->assertDatabaseHas('itinerary_items', [
            'id' => $existingItem->id,
            'note' => 'Hoạt động tự chọn của người dùng',
        ]);

        // Các ngày 2 và 3 đã được AI tự động bù đắp
        $day2Items = ItineraryItem::where('trip_id', $this->trip->id)->where('day_number', 2)->count();
        $day3Items = ItineraryItem::where('trip_id', $this->trip->id)->where('day_number', 3)->count();
        $this->assertGreaterThan(0, $day2Items);
        $this->assertGreaterThan(0, $day3Items);
    }

    /**
     * Admin có quyền sinh lại lịch trình cho bất kỳ chuyến đi nào trong hệ thống.
     */
    public function test_admin_can_generate_itinerary_for_any_trip(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('trips.ai-itinerary.generate', $this->trip->id), [
                'style' => 'foodie',
                'mode' => 'replace',
            ]);

        $response->assertRedirect(route('trips.show', $this->trip->id));
        $this->assertDatabaseHas('itinerary_items', [
            'trip_id' => $this->trip->id,
        ]);
    }
}
