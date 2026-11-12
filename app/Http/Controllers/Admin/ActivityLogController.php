<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\ActivityLogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Khởi tạo ActivityLogController với ràng buộc ActivityLogServiceInterface.
     */
    public function __construct(
        private ActivityLogServiceInterface $activityLogService
    ) {
    }

    /**
     * Hiển thị danh sách nhật ký hoạt động hệ thống kèm các số liệu thống kê và bộ lọc.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'action', 'date_from', 'date_to', 'user_id']);
        $logs = $this->activityLogService->getActivityLogs($filters, 15);
        $counts = $this->activityLogService->getActivityLogCounts();

        // Danh sách các hành động có sẵn để lọc
        $availableActions = [
            'login' => 'Đăng nhập thành công',
            'logout' => 'Đăng xuất',
            'login_failed' => 'Đăng nhập thất bại',
            'password_change' => 'Đổi mật khẩu',
            'user_created' => 'Tạo người dùng',
            'user_updated' => 'Cập nhật người dùng',
            'user_locked' => 'Khóa người dùng',
            'user_unlocked' => 'Mở khóa người dùng',
            'user_deleted' => 'Xóa mềm người dùng',
            'user_restored' => 'Khôi phục người dùng',
        ];

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'counts' => $counts,
            'filters' => $filters,
            'availableActions' => $availableActions,
        ]);
    }

    /**
     * Xem chi tiết thông tin và metadata an toàn của một bản ghi nhật ký.
     */
    public function show(int $id): JsonResponse
    {
        $log = $this->activityLogService->getLogDetails($id);

        if (! $log) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bản ghi nhật ký hoạt động.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'action' => $log->action,
                'user_name' => $log->user?->name ?? 'Hệ thống / Khách',
                'user_email' => $log->user?->email ?? 'N/A',
                'subject_type' => $log->subject_type ?? 'N/A',
                'subject_id' => $log->subject_id ?? 'N/A',
                'ip_address' => $log->ip_address ?? '127.0.0.1',
                'metadata' => $log->metadata,
                'created_at' => $log->created_at?->format('d/m/Y H:i:s'),
            ],
        ]);
    }
}
