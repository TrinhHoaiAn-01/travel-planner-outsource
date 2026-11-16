<?php

namespace App\Services;

use App\Models\City;
use App\Models\Destination;
use App\Models\ItineraryItem;
use App\Models\Trip;
use App\Services\Interfaces\AiItineraryServiceInterface;
use Illuminate\Support\Facades\DB;

/**
 * Service AI tự động gợi ý, sinh và đánh giá lịch trình du lịch thông minh.
 * Thành viên phụ trách: Trần Văn Trọng (22211TT0337) - Mục 11 theo Phân công Đồ án.
 * Phân bổ hoạt động khoa học theo từng ngày, khớp điểm đến thực tế trong CSDL và tối ưu thời gian.
 */
class AiItineraryService implements AiItineraryServiceInterface
{
    /**
     * Tự động sinh lịch trình hoạt động thông minh theo từng ngày cho chuyến đi.
     *
     * @param Trip $trip
     * @param array<string, mixed> $options
     * @return int
     */
    public function generateSmartItinerary(Trip $trip, array $options = []): int
    {
        return DB::transaction(function () use ($trip, $options) {
            // 1. Tính tổng số ngày của chuyến đi
            $daysCount = 1;
            if ($trip->start_date && $trip->end_date) {
                $daysCount = max(1, $trip->start_date->diffInDays($trip->end_date) + 1);
            }

            // 2. Chế độ xử lý lịch trình: 'replace' (ghi đè làm mới) hoặc 'fill_empty' (chỉ điền ngày trống)
            $mode = $options['mode'] ?? 'replace';
            $existingDaysWithActivities = [];

            if ($mode === 'replace') {
                $trip->itineraryItems()->delete();
            } else {
                $existingDaysWithActivities = $trip->itineraryItems()
                    ->pluck('day_number')
                    ->unique()
                    ->all();
            }

            // 3. Nhận diện địa danh / thành phố của chuyến đi
            $targetCity = $this->detectTripCity($trip);

            // 4. Lấy danh sách điểm đến phù hợp trong cơ sở dữ liệu
            $query = Destination::query()->with('category');
            if ($targetCity) {
                $query->where('city_id', $targetCity->id);
            }
            $destinations = $query->orderByDesc('rating')->limit(30)->get();

            // Nếu không có địa điểm theo thành phố, lấy các địa điểm nổi tiếng toàn quốc
            if ($destinations->isEmpty()) {
                $destinations = Destination::query()->with('category')->orderByDesc('rating')->limit(30)->get();
            }

            // 5. Cấu hình khung thời gian hoạt động theo phong cách du lịch
            $style = $options['style'] ?? 'balanced';
            $slots = $this->getTimeSlotsByStyle($style);

            $createdCount = 0;
            $destIndex = 0;
            $totalDests = $destinations->count();

            // 6. Phân bổ hoạt động cho từng ngày
            for ($day = 1; $day <= $daysCount; $day++) {
                if ($mode === 'fill_empty' && in_array($day, $existingDaysWithActivities, true)) {
                    continue;
                }

                $sortOrder = 1;
                foreach ($slots as $slot) {
                    $assignedDest = $totalDests > 0 ? $destinations[$destIndex % $totalDests] : null;
                    $destIndex++;

                    $activityTitle = $assignedDest ? $assignedDest->name : $slot['default_title'];
                    $aiNote = $this->generateAiNote($slot['slot_name'], $assignedDest, $day);

                    ItineraryItem::create([
                        'trip_id' => $trip->id,
                        'destination_id' => $assignedDest?->id,
                        'custom_title' => $activityTitle,
                        'day_number' => $day,
                        'start_time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                        'note' => $aiNote,
                        'sort_order' => $sortOrder++,
                    ]);

                    $createdCount++;
                }
            }

            return $createdCount;
        });
    }

    /**
     * Đánh giá chất lượng và phân bổ thời gian của lịch trình chuyến đi hiện tại.
     *
     * @param Trip $trip
     * @return array<string, mixed>
     */
    public function evaluateItinerary(Trip $trip): array
    {
        $items = $trip->itineraryItems()->with('destination')->get();
        $daysCount = $trip->start_date && $trip->end_date ? max(1, $trip->start_date->diffInDays($trip->end_date) + 1) : 1;

        if ($items->isEmpty()) {
            return [
                'score' => 20,
                'status' => 'needs_work',
                'summary' => 'Chuyến đi hiện chưa có hoạt động nào được lên lịch.',
                'suggestions' => ['Sử dụng tính năng "Tự động sinh lịch trình bằng AI" để lấp đầy các hoạt động theo từng ngày.'],
                'warnings' => ['Bạn chưa có kế hoạch cụ thể cho các ngày của chuyến đi.'],
            ];
        }

        $daysWithActivities = $items->pluck('day_number')->unique()->count();
        $coverage = round(($daysWithActivities / $daysCount) * 100);

        $score = min(100, (int) ($coverage * 0.6 + min(40, $items->count() * 4)));
        $suggestions = [];
        $warnings = [];

        if ($daysWithActivities < $daysCount) {
            $warnings[] = "Có " . ($daysCount - $daysWithActivities) . " ngày trong chuyến đi chưa có hoạt động nào được sắp xếp.";
            $suggestions[] = "Nên bổ sung thêm ít nhất 2 hoạt động cho những ngày còn trống để chuyến đi liền mạch.";
        }

        if ($items->count() >= $daysCount * 2) {
            $suggestions[] = "Lịch trình có mật độ hoạt động hợp lý, kết hợp hài hòa giữa tham quan và nghỉ ngơi thư giãn.";
        }

        return [
            'score' => $score,
            'status' => $score >= 80 ? 'excellent' : ($score >= 50 ? 'good' : 'fair'),
            'summary' => "Lịch trình đã bao phủ {$daysWithActivities}/{$daysCount} ngày của chuyến đi (Đạt điểm đánh giá {$score}/100).",
            'suggestions' => $suggestions,
            'warnings' => $warnings,
        ];
    }

    /**
     * Nhận diện thành phố hoặc địa danh từ tên, mô tả chuyến đi.
     *
     * @param Trip $trip
     * @return City|null
     */
    private function detectTripCity(Trip $trip): ?City
    {
        $text = mb_strtolower(($trip->name ?? '') . ' ' . ($trip->description ?? ''));

        $cities = City::all();
        foreach ($cities as $city) {
            $cityName = mb_strtolower($city->name);
            if (str_contains($text, $cityName)) {
                return $city;
            }
        }

        // Kiểm tra qua slug hoặc tên không dấu phổ biến
        $commonMappings = [
            'đà nẵng' => 'đà nẵng', 'da nang' => 'đà nẵng',
            'phú quốc' => 'phú quốc', 'phu quoc' => 'phú quốc',
            'hạ long' => 'hạ long', 'ha long' => 'hạ long',
            'hà nội' => 'hà nội', 'ha noi' => 'hà nội',
            'đà lạt' => 'đà lạt', 'da lat' => 'đà lạt',
            'sa pa' => 'lào cai', 'sapa' => 'lào cai',
            'nha trang' => 'khánh hòa',
        ];

        foreach ($commonMappings as $keyword => $target) {
            if (str_contains($text, $keyword)) {
                return City::where('name', 'LIKE', "%{$target}%")->first();
            }
        }

        return null;
    }

    /**
     * Lấy các khung giờ hoạt động tương ứng theo phong cách du lịch.
     *
     * @param string $style
     * @return array<int, array<string, string>>
     */
    private function getTimeSlotsByStyle(string $style): array
    {
        if ($style === 'relaxed') {
            // Phong cách nghỉ dưỡng thong thả: 2 hoạt động / ngày
            return [
                ['slot_name' => 'morning', 'start_time' => '09:00', 'end_time' => '11:30', 'default_title' => 'Dạo cảnh & thư giãn buổi sáng'],
                ['slot_name' => 'afternoon', 'start_time' => '15:00', 'end_time' => '17:30', 'default_title' => 'Ngắm hoàng hôn & cà phê thư thái'],
            ];
        }

        if ($style === 'active') {
            // Phong cách khám phá dày đặc: 4 hoạt động / ngày
            return [
                ['slot_name' => 'morning_early', 'start_time' => '08:00', 'end_time' => '10:30', 'default_title' => 'Khám phá danh thắng buổi sớm'],
                ['slot_name' => 'morning_late', 'start_time' => '11:00', 'end_time' => '13:00', 'default_title' => 'Thưởng thức ẩm thực địa phương'],
                ['slot_name' => 'afternoon', 'start_time' => '14:30', 'end_time' => '17:30', 'default_title' => 'Hoạt động trải nghiệm ngoài trời'],
                ['slot_name' => 'evening', 'start_time' => '19:00', 'end_time' => '21:30', 'default_title' => 'Khám phá phố đêm & giải trí'],
            ];
        }

        // Mặc định: Phong cách cân bằng (balanced / culture / foodie): 3 hoạt động / ngày
        return [
            ['slot_name' => 'morning', 'start_time' => '08:30', 'end_time' => '11:30', 'default_title' => 'Tham quan điểm đến văn hóa & check-in'],
            ['slot_name' => 'afternoon', 'start_time' => '14:00', 'end_time' => '17:00', 'default_title' => 'Trải nghiệm phong cảnh & vui chơi'],
            ['slot_name' => 'evening', 'start_time' => '18:30', 'end_time' => '21:00', 'default_title' => 'Khám phá ẩm thực chợ đêm & thư giãn'],
        ];
    }

    /**
     * Sinh lời khuyên thông minh từ AI gắn vào từng hoạt động.
     *
     * @param string $slotName
     * @param Destination|null $dest
     * @param int $dayNumber
     * @return string
     */
    private function generateAiNote(string $slotName, ?Destination $dest, int $dayNumber): string
    {
        $destName = $dest ? $dest->name : 'địa điểm';

        $notes = [
            'morning' => "Gợi ý AI (Ngày {$dayNumber}): Buổi sáng thời tiết dễ chịu, thích hợp chụp ảnh và ngắm cảnh tại {$destName}. Nên mang nón, kem chống nắng và chuẩn bị pin máy ảnh.",
            'morning_early' => "Gợi ý AI (Ngày {$dayNumber}): Khởi hành sớm để đón ánh nắng sớm và tránh đông đúc tại {$destName}.",
            'morning_late' => "Gợi ý AI (Ngày {$dayNumber}): Thưởng thức các món đặc sản địa phương đặc sắc quanh khu vực {$destName}.",
            'afternoon' => "Gợi ý AI (Ngày {$dayNumber}): Hoạt động ngoài trời lý tưởng tại {$destName}. Chuẩn bị trang phục thoải mái để dễ dàng di chuyển và trải nghiệm.",
            'evening' => "Gợi ý AI (Ngày {$dayNumber}): Thưởng thức không khí về đêm nhộn nhịp, dạo chơi và nạp năng lượng tại {$destName}.",
        ];

        return $notes[$slotName] ?? "Gợi ý AI: Trải nghiệm tuyệt vời cùng lịch trình được tối ưu hóa.";
    }
}

