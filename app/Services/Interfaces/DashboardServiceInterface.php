<?php

namespace App\Services\Interfaces;

interface DashboardServiceInterface
{
    /**
     * Lấy các chỉ số thống kê tổng quan trên Dashboard.
     *
     * @return array<string, mixed>
     */
    public function getSummaryStatistics(): array;

    /**
     * Lấy dữ liệu biểu đồ đặt chuyến đi và booking theo từng tháng trong năm 2026.
     *
     * @return array<string, mixed>
     */
    public function getMonthlyStatistics(int $year = 2026): array;

    /**
     * Lấy danh sách điểm đến phổ biến nhất kèm tỷ lệ phần trăm.
     *
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function getTopDestinations(int $limit = 4): array;

    /**
     * Lấy danh sách đơn đặt phòng và yêu cầu mới nhất.
     *
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function getRecentBookings(int $limit = 5): array;
}
