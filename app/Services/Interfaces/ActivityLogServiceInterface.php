<?php

namespace App\Services\Interfaces;

use App\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ActivityLogServiceInterface
{
    /**
     * Ghi lại một hoạt động nghiệp vụ hoặc sự kiện an ninh vào hệ thống.
     * Mọi thông tin nhạy cảm (mật khẩu, token, session) bắt buộc phải được loại bỏ khỏi metadata.
     *
     * @param int|null $userId ID người dùng thực hiện (null nếu là tác nhân khách/hệ thống)
     * @param string $action Mã định danh hành động (ví dụ: login, logout, user_locked, user_deleted, v.v.)
     * @param string|null $subjectType Tên lớp hoặc đối tượng chịu tác động (ví dụ: User, Destination, Review)
     * @param int|null $subjectId ID của đối tượng chịu tác động
     * @param array<string, mixed>|null $metadata Thông tin bổ sung an toàn (không chứa dữ liệu nhạy cảm)
     * @param string|null $ipAddress Địa chỉ IP phát sinh yêu cầu
     * @return ActivityLog
     */
    public function logActivity(
        ?int $userId,
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?array $metadata = null,
        ?string $ipAddress = null
    ): ActivityLog;

    /**
     * Lấy danh sách nhật ký hoạt động có phân trang và hỗ trợ bộ lọc.
     *
     * @param array<string, mixed> $filters Mảng chứa các tiêu chí lọc: search, action, date_from, date_to, user_id
     * @param int $perPage Số lượng bản ghi trên mỗi trang (mặc định 15)
     * @return LengthAwarePaginator
     */
    public function getActivityLogs(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Lấy thống kê số lượng nhật ký hoạt động phục vụ hiển thị thẻ tổng quan (KPI cards).
     *
     * @return array<string, int>
     */
    public function getActivityLogCounts(): array;

    /**
     * Lấy chi tiết một bản ghi nhật ký hoạt động theo ID.
     *
     * @param int $id
     * @return ActivityLog|null
     */
    public function getLogDetails(int $id): ?ActivityLog;
}
