<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetAndExpenseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    /**
     * Khách vãng lai (Guest) chưa đăng nhập không thể xem trang ngân sách và bị chuyển hướng về login.
     * Tuân thủ quy tắc BR-01.
     */
    public function test_guest_cannot_access_trip_budget_page_and_is_redirected_to_login(): void
    {
        $user = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $user->id,
            'name' => 'Chuyến đi Hà Nội',
            'budget' => 10000000,
            'status' => 'planned',
        ]);

        $response = $this->get(route('trips.budget.show', $trip));
        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng không thể xem trang ngân sách của chuyến đi thuộc người khác (lỗi 403 Forbidden).
     * Tuân thủ quy tắc BR-03 và chống lỗ hổng IDOR.
     */
    public function test_user_cannot_access_another_users_trip_budget_returns_403(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi bí mật của Owner',
            'budget' => 15000000,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($otherUser)->get(route('trips.budget.show', $trip));
        $response->assertStatus(403);
    }

    /**
     * Chủ sở hữu có thể xem trang ngân sách với đầy đủ thông tin tóm tắt và các khối hiển thị theo Hình 21.
     */
    public function test_owner_can_view_trip_budget_page_and_see_summary(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Hành trình Khám phá Đà Nẵng - Hội An',
            'budget' => 15000000,
            'status' => 'planned',
        ]);

        // Tạo sẵn một khoản chi phí
        Expense::create([
            'trip_id' => $trip->id,
            'category' => 'transport',
            'description' => 'Vé máy bay khứ hồi',
            'amount' => 4200000,
            'expense_date' => '2026-10-10',
        ]);

        $response = $this->actingAs($owner)->get(route('trips.budget.show', $trip));

        $response->assertStatus(200);
        $response->assertSee('Theo Dõi Ngân Sách & Chi Tiêu', false);
        $response->assertSee('Tổng ngân sách dự kiến');
        $response->assertSee('Tổng đã chi tiêu');
        $response->assertSee('Số dư khả dụng');
        $response->assertSee('Tiến độ sử dụng ngân sách');
        $response->assertSee('Bảng Kê Chi Phí Chi Tiết');
        $response->assertSee('Vé máy bay khứ hồi');
        $response->assertSee('4.200.000đ');
        $response->assertSee('Trong vùng an toàn');
    }

    /**
     * Chủ sở hữu có thể cập nhật hạn mức ngân sách dự kiến thành công (FR22).
     */
    public function test_owner_can_update_trip_budget_successfully(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi Đà Lạt',
            'budget' => 10000000,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($owner)->patch(route('trips.budget.update', $trip), [
            'budget' => 20000000,
        ]);

        $response->assertRedirect(route('trips.budget.show', $trip));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'budget' => 20000000,
        ]);
    }

    /**
     * Cập nhật ngân sách từ chối số âm (Validation Error 422).
     */
    public function test_updating_trip_budget_rejects_negative_value(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi Nha Trang',
            'budget' => 10000000,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($owner)->patch(route('trips.budget.update', $trip), [
            'budget' => -5000000,
        ]);

        $response->assertSessionHasErrors(['budget']);
        $this->assertEquals(10000000, $trip->fresh()->budget);
    }

    /**
     * Chủ sở hữu có thể thêm mới khoản chi tiêu thành công (FR23).
     */
    public function test_owner_can_add_expense_to_trip_successfully(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi Phú Quốc',
            'budget' => 20000000,
            'status' => 'planned',
        ]);

        $postData = [
            'title' => 'Khách sạn Novotel 3 đêm',
            'category' => 'lodging',
            'amount' => 5400000,
            'expense_date' => '2026-10-12',
            'note' => 'Đã bao gồm ăn sáng',
        ];

        $response = $this->actingAs($owner)->post(route('trips.expenses.store', $trip), $postData);

        $response->assertRedirect(route('trips.budget.show', $trip));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'trip_id' => $trip->id,
            'category' => 'lodging',
            'amount' => 5400000,
        ]);
    }

    /**
     * Thêm khoản chi tiêu từ chối số tiền nhỏ hơn hoặc bằng 0 (FR23, BR-19).
     */
    public function test_adding_expense_rejects_zero_or_negative_amount(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi Sa Pa',
            'budget' => 10000000,
            'status' => 'planned',
        ]);

        $responseZero = $this->actingAs($owner)->post(route('trips.expenses.store', $trip), [
            'title' => 'Cà phê Fansipan',
            'category' => 'food',
            'amount' => 0,
        ]);
        $responseZero->assertSessionHasErrors(['amount']);

        $responseNegative = $this->actingAs($owner)->post(route('trips.expenses.store', $trip), [
            'title' => 'Cà phê Fansipan',
            'category' => 'food',
            'amount' => -150000,
        ]);
        $responseNegative->assertSessionHasErrors(['amount']);
    }

    /**
     * Người dùng khác không thể thêm khoản chi vào chuyến đi không phải của mình (BR-04, 403 Forbidden).
     */
    public function test_user_cannot_add_expense_to_another_users_trip_returns_403(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi của Owner',
            'budget' => 10000000,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($otherUser)->post(route('trips.expenses.store', $trip), [
            'title' => 'Khoản chi lậu',
            'category' => 'other',
            'amount' => 500000,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Chủ sở hữu có thể chỉnh sửa khoản chi tiêu thành công (FR24).
     */
    public function test_owner_can_update_expense_successfully(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi Vũng Tàu',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $expense = Expense::create([
            'trip_id' => $trip->id,
            'category' => 'food',
            'description' => 'Hải sản Gành Hào',
            'amount' => 1200000,
            'expense_date' => '2026-10-09',
        ]);

        $response = $this->actingAs($owner)->patch(route('expenses.update', $expense), [
            'title' => 'Hải sản Gành Hào cập nhật',
            'category' => 'food',
            'amount' => 1500000,
        ]);

        $response->assertRedirect(route('trips.budget.show', $trip));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'amount' => 1500000,
        ]);
    }

    /**
     * Người dùng khác không thể sửa khoản chi của người khác (BR-04, 403 Forbidden).
     */
    public function test_user_cannot_update_another_users_expense_returns_403(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip của Owner',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $expense = Expense::create([
            'trip_id' => $trip->id,
            'category' => 'food',
            'description' => 'Bữa trưa',
            'amount' => 300000,
        ]);

        $response = $this->actingAs($otherUser)->patch(route('expenses.update', $expense), [
            'title' => 'Sửa trộm',
            'category' => 'food',
            'amount' => 999999,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Chủ sở hữu có thể xóa khoản chi tiêu thành công (FR24).
     */
    public function test_owner_can_delete_expense_successfully(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi Cần Thơ',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $expense = Expense::create([
            'trip_id' => $trip->id,
            'category' => 'transport',
            'description' => 'Vé xe khách Phương Trang',
            'amount' => 350000,
        ]);

        $response = $this->actingAs($owner)->delete(route('expenses.destroy', $expense));

        $response->assertRedirect(route('trips.budget.show', $trip));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id,
        ]);
    }

    /**
     * Người dùng khác không thể xóa khoản chi của người khác (BR-04, 403 Forbidden).
     */
    public function test_user_cannot_delete_another_users_expense_returns_403(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip của Owner',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $expense = Expense::create([
            'trip_id' => $trip->id,
            'category' => 'transport',
            'description' => 'Vé máy bay',
            'amount' => 2000000,
        ]);

        $response = $this->actingAs($otherUser)->delete(route('expenses.destroy', $expense));
        $response->assertStatus(403);
    }

    /**
     * Kiểm tra trạng thái vượt ngân sách và hiển thị cảnh báo đỏ theo Hình 22.
     */
    public function test_budget_summary_detects_over_budget_and_displays_warning(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Hành trình vượt ngân sách',
            'budget' => 10000000,
            'status' => 'planned',
        ]);

        // Tạo khoản chi vượt ngân sách: 12.000.000đ > 10.000.000đ
        Expense::create([
            'trip_id' => $trip->id,
            'category' => 'transport',
            'description' => 'Vé máy bay hạng thương gia',
            'amount' => 12000000,
        ]);

        $response = $this->actingAs($owner)->get(route('trips.budget.show', $trip));

        $response->assertStatus(200);
        $response->assertSee('Cảnh báo: Chi tiêu đã vượt quá hạn mức ngân sách!');
        $response->assertSee('Vượt hạn mức chi tiêu');
        $response->assertSee('-2.000.000đ');
    }

    /**
     * Kiểm tra tính năng lọc danh sách khoản chi theo danh mục.
     */
    public function test_filter_expenses_by_category(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi Quy Nhơn',
            'budget' => 10000000,
            'status' => 'planned',
        ]);

        Expense::create([
            'trip_id' => $trip->id,
            'category' => 'food',
            'description' => 'Bánh xèo tôm nhảy',
            'amount' => 200000,
        ]);

        Expense::create([
            'trip_id' => $trip->id,
            'category' => 'lodging',
            'description' => 'Khách sạn FLC',
            'amount' => 3000000,
        ]);

        $responseFood = $this->actingAs($owner)->get(route('trips.budget.show', ['trip' => $trip, 'category' => 'food']));
        $responseFood->assertStatus(200);
        $responseFood->assertSee('Bánh xèo tôm nhảy');
        $responseFood->assertDontSee('Khách sạn FLC');
    }
}
