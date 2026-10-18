<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\Interfaces\ActivityLogServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ActivityLogAndSystemLoggingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $member;
    private ActivityLogServiceInterface $logService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logService = app(ActivityLogServiceInterface::class);

        $this->admin = User::factory()->create([
            'name' => 'Quản Trị Viên Log',
            'email' => 'admin.log@travelplanner.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->member = User::factory()->create([
            'name' => 'Thành Viên Log',
            'email' => 'member.log@travelplanner.test',
            'password' => Hash::make('MemberPass@123'),
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    /**
     * Đăng nhập và đăng xuất thành công tự động ghi lại nhật ký hoạt động.
     */
    public function test_user_login_and_logout_records_activity_log(): void
    {
        // 1. Đăng nhập thành công với captcha hợp lệ
        $this->withSession(['auth_captcha' => '7KX9P'])->post(route('login'), [
            'email' => $this->member->email,
            'password' => 'MemberPass@123',
            'captcha' => '7KX9P',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->member->id,
            'action' => 'login',
            'subject_type' => 'User',
            'subject_id' => $this->member->id,
        ]);

        // 2. Đăng xuất
        $this->actingAs($this->member)->post(route('logout'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->member->id,
            'action' => 'logout',
            'subject_type' => 'User',
        ]);
    }

    /**
     * Đăng nhập thất bại tự động ghi lại nhật ký sự kiện an ninh (login_failed).
     */
    public function test_failed_login_records_activity_log(): void
    {
        // Giả lập phiên captcha để vượt qua validation cơ bản
        session(['auth_captcha' => 'ABCDE']);

        $response = $this->post(route('login'), [
            'email' => $this->member->email,
            'password' => 'SaiMatKhau@999',
            'captcha' => 'ABCDE',
        ]);

        $response->assertSessionHasErrors(['email']);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->member->id,
            'action' => 'login_failed',
        ]);
    }

    /**
     * Đổi mật khẩu thành công ghi nhật ký mà tuyệt đối không chứa mật khẩu vào metadata.
     */
    public function test_password_change_records_activity_log_without_passwords(): void
    {
        $response = $this->actingAs($this->member)->post(route('profile.password.update'), [
            'current_password' => 'MemberPass@123',
            'password' => 'NewMemberPass@456',
            'password_confirmation' => 'NewMemberPass@456',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->member->id,
            'action' => 'password_change',
            'subject_type' => 'User',
            'subject_id' => $this->member->id,
        ]);

        // Kiểm tra log trong cơ sở dữ liệu không lưu chuỗi mật khẩu
        $latestLog = ActivityLog::where('action', 'password_change')->latest('id')->first();
        $this->assertNotNull($latestLog);
        $this->assertEmpty($latestLog->metadata);
    }

    /**
     * Quản trị viên khóa và mở khóa tài khoản người dùng tự động ghi nhật ký.
     */
    public function test_admin_lock_and_unlock_records_activity_log(): void
    {
        // 1. Khóa tài khoản
        $this->actingAs($this->admin)->post(route('admin.users.toggle-status', $this->member->id));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'user_locked',
            'subject_type' => 'User',
            'subject_id' => $this->member->id,
        ]);

        // 2. Mở khóa tài khoản
        $this->actingAs($this->admin)->post(route('admin.users.toggle-status', $this->member->id));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'user_unlocked',
            'subject_type' => 'User',
            'subject_id' => $this->member->id,
        ]);
    }

    /**
     * Quản trị viên xóa mềm và khôi phục tài khoản tự động ghi nhật ký.
     */
    public function test_admin_soft_delete_and_restore_records_activity_log(): void
    {
        // 1. Xóa mềm (Soft Delete)
        $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->member->id));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'user_deleted',
            'subject_type' => 'User',
            'subject_id' => $this->member->id,
        ]);

        // 2. Khôi phục (Restore)
        $this->actingAs($this->admin)->post(route('admin.users.restore', $this->member->id));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'user_restored',
            'subject_type' => 'User',
            'subject_id' => $this->member->id,
        ]);
    }

    /**
     * Khử trùng dữ liệu metadata: loại bỏ hoàn toàn mật khẩu, token, session và secret key.
     */
    public function test_sensitive_data_is_never_stored_in_activity_log_metadata(): void
    {
        $log = $this->logService->logActivity(
            $this->admin->id,
            'test_sensitive_strip',
            'User',
            $this->member->id,
            [
                'safe_field' => 'Thao tác hợp lệ',
                'password' => 'SecretPass@123',
                'password_confirmation' => 'SecretPass@123',
                'remember_token' => 'token123456789',
                'auth_token' => 'jwt.token.string',
                'nested' => [
                    'session_id' => 'sess_99999',
                    'allowed_info' => 'Dữ liệu được phép lưu',
                ],
            ]
        );

        $this->assertArrayHasKey('safe_field', $log->metadata);
        $this->assertSame('Thao tác hợp lệ', $log->metadata['safe_field']);

        // Các khóa nhạy cảm bị loại bỏ hoàn toàn
        $this->assertArrayNotHasKey('password', $log->metadata);
        $this->assertArrayNotHasKey('password_confirmation', $log->metadata);
        $this->assertArrayNotHasKey('remember_token', $log->metadata);
        $this->assertArrayNotHasKey('auth_token', $log->metadata);

        // Khóa lồng nhau cũng được khử trùng đệ quy
        $this->assertArrayNotHasKey('session_id', $log->metadata['nested']);
        $this->assertSame('Dữ liệu được phép lưu', $log->metadata['nested']['allowed_info']);
    }

    /**
     * Người dùng thông thường (member) bị từ chối truy cập (403) vào trang nhật ký hoạt động.
     */
    public function test_regular_user_cannot_access_activity_logs(): void
    {
        $response = $this->actingAs($this->member)->get(route('admin.activity-logs.index'));
        $response->assertForbidden();

        $responseDetail = $this->actingAs($this->member)->get(route('admin.activity-logs.show', 1));
        $responseDetail->assertForbidden();
    }

    /**
     * Quản trị viên có thể xem trang nhật ký và lọc theo từ khóa hoặc loại hành động.
     */
    public function test_admin_can_view_activity_logs_and_filter(): void
    {
        // Tạo các bản ghi nhật ký mẫu
        ActivityLog::create([
            'user_id' => $this->member->id,
            'action' => 'login',
            'ip_address' => '192.168.1.100',
            'created_at' => now(),
        ]);

        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'user_locked',
            'ip_address' => '192.168.1.200',
            'created_at' => now(),
        ]);

        // 1. Mở trang index
        $response = $this->actingAs($this->admin)->get(route('admin.activity-logs.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.activity-logs.index');
        $response->assertSee('Nhật ký hoạt động hệ thống');
        $response->assertSee('login');
        $response->assertSee('user_locked');

        // 2. Lọc theo action = login
        $responseFilterAction = $this->actingAs($this->admin)->get(route('admin.activity-logs.index', ['action' => 'login']));
        $responseFilterAction->assertStatus(200);
        $responseFilterAction->assertSee('192.168.1.100');
        $responseFilterAction->assertDontSee('192.168.1.200');

        // 3. Tìm kiếm theo IP
        $responseFilterIp = $this->actingAs($this->admin)->get(route('admin.activity-logs.index', ['search' => '192.168.1.200']));
        $responseFilterIp->assertStatus(200);
        $responseFilterIp->assertSee('user_locked');
        $responseFilterIp->assertDontSee('192.168.1.100');
    }

    /**
     * Quản trị viên có thể xem chi tiết nhật ký qua JSON API an toàn.
     */
    public function test_admin_can_view_log_details_via_json(): void
    {
        $log = ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'user_updated',
            'subject_type' => 'User',
            'subject_id' => $this->member->id,
            'metadata' => ['role' => 'user'],
            'ip_address' => '10.0.0.1',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.activity-logs.show', $log->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'action' => 'user_updated',
                'user_name' => $this->admin->name,
                'ip_address' => '10.0.0.1',
                'metadata' => ['role' => 'user'],
            ],
        ]);
    }
}
