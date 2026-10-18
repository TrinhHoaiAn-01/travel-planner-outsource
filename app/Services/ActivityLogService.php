<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Services\Interfaces\ActivityLogServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityLogService implements ActivityLogServiceInterface
{
    /**
     * Danh sách các khóa dữ liệu nhạy cảm bắt buộc phải loại bỏ khỏi metadata (Bảng 40).
     *
     * @var array<int, string>
     */
    protected array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'remember_token',
        'secret',
        'cookie',
        'session',
        'session_id',
        'credentials',
        'api_key',
        'auth_token',
        '_token',
    ];

    /**
     * Ghi lại một hoạt động nghiệp vụ hoặc sự kiện an ninh vào hệ thống.
     * Tự động lọc bỏ các trường nhạy cảm để đảm bảo an toàn tuyệt đối.
     *
     * @param int|null $userId
     * @param string $action
     * @param string|null $subjectType
     * @param int|null $subjectId
     * @param array<string, mixed>|null $metadata
     * @param string|null $ipAddress
     * @return ActivityLog
     */
    public function logActivity(
        ?int $userId,
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?array $metadata = null,
        ?string $ipAddress = null
    ): ActivityLog {
        $sanitizedMetadata = $metadata ? $this->sanitizeMetadata($metadata) : null;
        $clientIp = $ipAddress ?: (app()->runningInConsole() ? '127.0.0.1' : request()?->ip());

        return ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'metadata' => $sanitizedMetadata,
            'ip_address' => $clientIp,
            'created_at' => now(),
        ]);
    }

    /**
     * Lấy danh sách nhật ký hoạt động có phân trang và hỗ trợ bộ lọc.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActivityLogs(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ActivityLog::with('user')->orderBy('id', 'desc');

        // Tìm kiếm theo từ khóa (hành động, địa chỉ IP hoặc tên/email người dùng)
        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Lọc theo loại hành động cụ thể
        if (! empty($filters['action'])) {
            $action = trim((string) $filters['action']);
            $query->where('action', $action);
        }

        // Lọc theo khoảng thời gian từ ngày (date_from)
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        // Lọc theo khoảng thời gian đến ngày (date_to)
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Lọc theo ID người dùng cụ thể
        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Lấy thống kê số lượng nhật ký hoạt động phục vụ hiển thị thẻ tổng quan (KPI cards).
     *
     * @return array<string, int>
     */
    public function getActivityLogCounts(): array
    {
        $total = ActivityLog::count();
        $today = ActivityLog::whereDate('created_at', today())->count();
        $authEvents = ActivityLog::whereIn('action', [
            'login',
            'logout',
            'login_failed',
            'password_change',
            'password_reset',
        ])->count();
        $adminActions = ActivityLog::where(function ($q) {
            $q->where('action', 'like', 'user_%')
                ->orWhere('action', 'like', 'admin_%')
                ->orWhere('action', 'like', 'review_%');
        })->count();

        return [
            'total' => $total,
            'today' => $today,
            'auth_events' => $authEvents,
            'admin_actions' => $adminActions,
        ];
    }

    /**
     * Lấy chi tiết một bản ghi nhật ký hoạt động theo ID.
     *
     * @param int $id
     * @return ActivityLog|null
     */
    public function getLogDetails(int $id): ?ActivityLog
    {
        return ActivityLog::with('user')->find($id);
    }

    /**
     * Khử trùng và làm sạch metadata, loại trừ đệ quy mọi trường thông tin nhạy cảm.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function sanitizeMetadata(array $data): array
    {
        $clean = [];

        foreach ($data as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            // Kiểm tra xem khóa có chứa bất kỳ từ khóa nhạy cảm nào không
            $isSensitive = false;
            foreach ($this->sensitiveKeys as $sensitiveWord) {
                if ($normalizedKey === $sensitiveWord || str_contains($normalizedKey, $sensitiveWord)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                continue; // Bỏ qua hoàn toàn dữ liệu nhạy cảm
            }

            if (is_array($value)) {
                $clean[$key] = $this->sanitizeMetadata($value);
            } else {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }
}
