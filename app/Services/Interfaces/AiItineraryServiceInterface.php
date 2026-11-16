<?php

namespace App\Services\Interfaces;

use App\Models\Trip;

/**
 * Interface Dịch vụ AI gợi ý, tạo và đánh giá lịch trình chuyến đi thông minh.
 * Thành viên phụ trách: Trần Văn Trọng (22211TT0337) - Mục 11 theo Phân công Đồ án.
 * Tuân thủ nghiêm ngặt chuẩn kiến trúc MVC 3 lớp và Service Interface.
 */
interface AiItineraryServiceInterface
{
    /**
     * Tự động sinh lịch trình hoạt động thông minh theo từng ngày cho chuyến đi.
     *
     * @param Trip $trip Chuyến đi cần lập lịch trình
     * @param array<string, mixed> $options Tuỳ chọn: style (phong cách), mode (replace/fill_empty)
     * @return int Số lượng hoạt động đã được tự động tạo vào lịch trình
     */
    public function generateSmartItinerary(Trip $trip, array $options = []): int;

    /**
     * Đánh giá chất lượng và độ tối ưu của lịch trình chuyến đi hiện tại.
     *
     * @param Trip $trip
     * @return array<string, mixed> Kết quả đánh giá: score (1-100), suggestions, warnings
     */
    public function evaluateItinerary(Trip $trip): array;
}

