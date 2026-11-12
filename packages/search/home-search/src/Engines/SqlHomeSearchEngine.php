<?php

namespace Search\HomeSearch\Engines;

use App\Models\Destination;
use Search\HomeSearch\Contracts\HomeSearchEngineInterface;
use Search\HomeSearch\DTO\HomeSearchCriteria;

/**
 * Công cụ tìm kiếm Trang chủ sử dụng cơ sở dữ liệu MySQL (SQL Home Search Engine).
 * Đóng vai trò là công cụ tìm kiếm chuẩn qua SQL và là phương án dự phòng (Fallback Engine)
 * khi máy chủ Apache Solr gặp sự cố gián đoạn hoặc bảo trì.
 */
class SqlHomeSearchEngine implements HomeSearchEngineInterface
{
    /**
     * Thực hiện tìm kiếm danh sách ID địa điểm trực tiếp từ MySQL bằng Eloquent / Query Builder.
     *
     * @param HomeSearchCriteria $criteria Tiêu chí tìm kiếm đã được chuẩn hóa
     * @return array Mảng kết quả gồm: ['ids' => [1, 2, ...], 'total' => 10, 'engine' => 'sql']
     */
    public function search(HomeSearchCriteria $criteria): array
    {
        $query = Destination::query();

        // 1. Lọc theo từ khóa tìm kiếm (so khớp LIKE trên Tên, Địa chỉ, Mô tả và Tên thành phố)
        if ($criteria->hasKeyword()) {
            $keyword = $criteria->keyword;
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('address', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%")
                    ->orWhereHas('city', function ($cityQuery) use ($keyword) {
                        $cityQuery->where('name', 'LIKE', "%{$keyword}%");
                    });
            });
        }

        // 2. Lọc theo Thành phố
        if ($criteria->hasCity()) {
            $query->where('city_id', $criteria->cityId);
        }

        // 3. Lọc theo Danh mục
        if ($criteria->hasCategory()) {
            $query->where('category_id', $criteria->categoryId);
        }

        // 4. Lọc theo Mức ngân sách (Giá vé vào cổng)
        if ($criteria->hasBudget()) {
            if ($criteria->budget === 1) {
                $query->where('entrance_fee', '<', 500000);
            } elseif ($criteria->budget === 2) {
                $query->whereBetween('entrance_fee', [500000, 1500000]);
            } elseif ($criteria->budget === 3) {
                $query->where('entrance_fee', '>', 1500000);
            }
        }

        // 5. Lọc nơi lưu trú có phòng nghỉ
        if ($criteria->isLodging) {
            $query->whereHas('roomTypes');
        }

        // 6. Đếm tổng số bản ghi phù hợp
        $totalMatches = (clone $query)->count();

        // 7. Sắp xếp theo Đánh giá sao (Rating) giảm dần, sau đó theo Tên A-Z
        $ids = $query->orderByDesc('rating')
            ->orderBy('name')
            ->offset($criteria->offset)
            ->limit($criteria->limit)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return [
            'ids' => $ids,
            'total' => $totalMatches,
            'engine' => 'sql',
        ];
    }
}

