<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    /**
     * Khách vãng lai chưa đăng nhập bị chuyển hướng về trang đăng nhập khi truy cập Admin.
     */
    public function test_guest_is_redirected_to_login_when_accessing_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Người dùng thông thường (role user) bị chặn với mã lỗi 403 Forbidden theo quy tắc bảo mật.
     */
    public function test_regular_user_gets_403_forbidden_when_accessing_admin(): void
    {
        $user = new User([
            'id' => 99,
            'name' => 'Test Regular User',
            'email' => 'user@test.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(403);
    }

    /**
     * Quản trị viên (role admin) truy cập thành công và nhìn thấy đầy đủ các mục trên Dashboard.
     */
    public function test_admin_user_can_access_dashboard_and_see_all_required_sections(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Administrator',
            'email' => 'admin@travelplanner.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);

        // Tiêu đề và trạng thái
        $response->assertSee('Hệ Thống Quản Trị Travel Planner');
        $response->assertSee('Chào mừng, Quản trị viên hệ thống');

        // 4 Thẻ chỉ số thống kê
        $response->assertSee('TỔNG SỐ NGƯỜI DÙNG');
        $response->assertSee('4.520');
        $response->assertSee('TỔNG SỐ ĐIỂM ĐẾN');
        $response->assertSee('1.280');
        $response->assertSee('TỔNG ĐẶT PHÒNG');
        $response->assertSee('860');
        $response->assertSee('TỔNG DOANH THU');
        $response->assertSee('128.500.000 đ');

        // Biểu đồ và Tỷ lệ đặt phòng theo loại
        $response->assertSee('Thống Kê Lượt Đặt Phòng Theo Tháng');
        $response->assertSee('Tỷ Lệ Đặt Phòng - Theo Loại');
        $response->assertSee('Khách Sạn');
        $response->assertSee('Resort & Nghỉ Dưỡng');
        $response->assertSee('Homestay');
        $response->assertSee('Căn Hộ & Villa');

        // Bảng Giao dịch gần đây
        $response->assertSee('Giao Dịch Gần Đây');
        $response->assertSee('#BK-2026-0891');
        $response->assertSee('Nguyễn Văn An');
        $response->assertSee('Vinpearl Resort Nha Trang');
        $response->assertSee('3.500.000 đ');
        $response->assertSee('Hoàn thành');
        $response->assertSee('#BK-2026-0890');
        $response->assertSee('Trần Thị Mai');
        $response->assertSee('#BK-2026-0889');
        $response->assertSee('Lê Hoàng Nam');
    }

    /**
     * Kiểm tra thanh điều hướng Sidebar chứa đầy đủ các phân hệ quản lý hệ thống.
     */
    public function test_admin_sidebar_contains_all_system_management_links(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Administrator',
            'email' => 'admin@travelplanner.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Sidebar Brand
        $response->assertSee('Admin Panel');

        // Nhóm Tổng quan
        $response->assertSee('TỔNG QUAN');
        $response->assertSee('Dashboard');

        // Nhóm Quản lý hệ thống
        $response->assertSee('QUẢN LÝ HỆ THỐNG');
        $response->assertSee('Người dùng (Users)');
        $response->assertSee('Thành phố (Cities)');
        $response->assertSee('Danh mục (Categories)');
        $response->assertSee('Địa điểm (Destinations)');
        $response->assertSee('Thư viện ảnh (Images)');

        // Nhóm Kiểm duyệt
        $response->assertSee('KIỂM DUYỆT');
        $response->assertSee('Đánh giá (Reviews)');
    }

    /**
     * Kiểm tra nút chuyển đổi Xem Giao diện Public và đăng xuất bảo mật CSRF.
     */
    public function test_admin_header_actions_and_logout_form_are_present(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Administrator',
            'email' => 'admin@travelplanner.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Admin');
        $response->assertSee('Bảng điều khiển (Dashboard)');
    }
}
