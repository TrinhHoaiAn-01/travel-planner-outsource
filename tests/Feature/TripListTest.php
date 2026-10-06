<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Khách vãng lai (Guest) chưa đăng nhập không thể xem danh sách chuyến đi và bị chuyển hướng về login.
     */
    public function test_guest_is_redirected_to_login_when_accessing_trips(): void
    {
        $response = $this->get(route('trips.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng đã đăng nhập có thể xem danh sách chuyến đi với đầy đủ thông tin và giao diện.
     */
    public function test_authenticated_user_can_view_trips_list(): void
    {
        $user = User::factory()->create([
            'name' => 'Ngọc Nguyễn',
            'email' => 'ngoc.nguyen@travelplanner.test',
        ]);

        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'name' => 'Hành trình Khám phá Đà Nẵng - Hội An',
            'description' => 'Bà Nà Hills, Bán đảo Sơn Trà, Ngũ Hành Sơn và phố đèn lồng Hội An thơ mộng.',
            'budget' => 15000000,
            'status' => Trip::STATUS_PLANNED,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('trips.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Hành Trình & Chuyến Đi', false);
        $response->assertSee('Hành trình Khám phá Đà Nẵng - Hội An');
        $response->assertSee('Ngân sách dự kiến');
        $response->assertSee('15.000.000đ');
        $response->assertSee('Tất cả (1)');
        $response->assertSee('Sắp tới (1)');
    }

    /**
     * Đảm bảo tính toàn vẹn dữ liệu: Người dùng chỉ thấy chuyến đi của chính mình, không thấy chuyến đi của người khác (BR-03 & Chống IDOR).
     */
    public function test_user_only_sees_their_own_trips(): void
    {
        $userA = User::factory()->create(['email' => 'usera@travelplanner.test']);
        $userB = User::factory()->create(['email' => 'userb@travelplanner.test']);

        $tripA = Trip::factory()->create([
            'user_id' => $userA->id,
            'name' => 'Chuyến đi bí mật của User A',
        ]);

        $tripB = Trip::factory()->create([
            'user_id' => $userB->id,
            'name' => 'Chuyến đi riêng tư của User B',
        ]);

        $response = $this->actingAs($userA)->get(route('trips.index'));

        $response->assertStatus(200);
        $response->assertSee('Chuyến đi bí mật của User A');
        $response->assertDontSee('Chuyến đi riêng tư của User B');
    }

    /**
     * Người dùng có thể tìm kiếm chuyến đi theo từ khóa tên hoặc mô tả.
     */
    public function test_user_can_search_trips_by_keyword(): void
    {
        $user = User::factory()->create();

        Trip::factory()->create([
            'user_id' => $user->id,
            'name' => 'Du Lịch Biển Nha Trang',
            'description' => 'Tắm biển và lặn ngắm san hô.',
        ]);

        Trip::factory()->create([
            'user_id' => $user->id,
            'name' => 'Khám Phá Sapa Mùa Lúa Chín',
            'description' => 'Đi bản Cát Cát và leo Fansipan.',
        ]);

        // Tìm từ khóa "Nha Trang"
        $response = $this->actingAs($user)->get(route('trips.index', ['search' => 'Nha Trang']));

        $response->assertStatus(200);
        $response->assertSee('Du Lịch Biển Nha Trang');
        $response->assertDontSee('Khám Phá Sapa Mùa Lúa Chín');

        // Tìm từ khóa "Fansipan" trong mô tả
        $responseDesc = $this->actingAs($user)->get(route('trips.index', ['search' => 'Fansipan']));

        $responseDesc->assertStatus(200);
        $responseDesc->assertSee('Khám Phá Sapa Mùa Lúa Chín');
        $responseDesc->assertDontSee('Du Lịch Biển Nha Trang');
    }

    /**
     * Người dùng có thể lọc danh sách chuyến đi theo trạng thái (planned, ongoing, completed).
     */
    public function test_user_can_filter_trips_by_status(): void
    {
        $user = User::factory()->create();

        Trip::factory()->create([
            'user_id' => $user->id,
            'name' => 'Chuyến đi Sắp tới',
            'status' => Trip::STATUS_PLANNED,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(15),
        ]);

        Trip::factory()->create([
            'user_id' => $user->id,
            'name' => 'Chuyến đi Đang diễn ra',
            'status' => 'ongoing',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(2),
        ]);

        Trip::factory()->create([
            'user_id' => $user->id,
            'name' => 'Chuyến đi Đã hoàn thành',
            'status' => Trip::STATUS_COMPLETED,
            'start_date' => now()->subDays(20),
            'end_date' => now()->subDays(15),
        ]);

        // Lọc "Sắp tới"
        $responsePlanned = $this->actingAs($user)->get(route('trips.index', ['status' => 'planned']));
        $responsePlanned->assertStatus(200);
        $responsePlanned->assertSee('Chuyến đi Sắp tới');
        $responsePlanned->assertDontSee('Chuyến đi Đã hoàn thành');

        // Lọc "Đã hoàn thành"
        $responseCompleted = $this->actingAs($user)->get(route('trips.index', ['status' => 'completed']));
        $responseCompleted->assertStatus(200);
        $responseCompleted->assertSee('Chuyến đi Đã hoàn thành');
        $responseCompleted->assertDontSee('Chuyến đi Sắp tới');
    }

    /**
     * Chuyến đi đã hoàn thành hiển thị tổng chi phí thực tế từ bảng expenses, chuyến chưa hoàn thành hiển thị ngân sách dự kiến.
     */
    public function test_completed_trip_shows_actual_expenses_and_other_shows_budget(): void
    {
        $user = User::factory()->create();

        $completedTrip = Trip::factory()->create([
            'user_id' => $user->id,
            'name' => 'Săn Mây Đỉnh Fansipan Sa Pa',
            'status' => Trip::STATUS_COMPLETED,
            'budget' => 10000000,
        ]);

        // Tạo chi phí thực tế cho chuyến đi đã hoàn thành
        Expense::create([
            'trip_id' => $completedTrip->id,
            'category' => 'food',
            'description' => 'Ăn uống lẩu cá hồi',
            'amount' => 3850000,
            'expense_date' => now()->subDays(10),
        ]);

        Expense::create([
            'trip_id' => $completedTrip->id,
            'category' => 'hotel',
            'description' => 'Khách sạn Sa Pa',
            'amount' => 4000000,
            'expense_date' => now()->subDays(10),
        ]);

        $response = $this->actingAs($user)->get(route('trips.index'));

        $response->assertStatus(200);
        $response->assertSee('Tổng chi thực tế');
        $response->assertSee('7.850.000đ'); // 3.850.000 + 4.000.000
    }
}
