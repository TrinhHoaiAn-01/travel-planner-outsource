<?php

namespace App\Services;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\RoomType;
use App\Models\Trip;
use App\Models\User;
use App\Services\Interfaces\DestinationServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

//Ngocai
class DestinationService implements DestinationServiceInterface
{
        /**
     * Lấy danh sách các địa điểm nổi bật (is_featured = true) kèm ảnh đại diện, thành phố và danh mục.
     */
    public function getFeaturedDestinations(int $limit = 4): Collection
    {
        return Destination::query()
            ->with(['city', 'category', 'primaryImage', 'roomTypes'])
            ->where('is_featured', true)
            ->orderByDesc('rating')
            ->limit($limit)
            ->get();
    }


    /**
     * Lấy danh sách các thành phố có nhiều địa điểm du lịch nhất.
     */
    public function getPopularCities(int $limit = 6): Collection
    {
        return City::query()
            ->withCount('destinations')
            ->orderByDesc('destinations_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy danh sách danh mục và đếm số lượng địa điểm thuộc từng danh mục.
     */
    public function getCategories(): Collection
    {
        return Category::query()
            ->withCount('destinations')
            ->orderBy('name')
            ->get();
    }

    /**
     * Thống kê tổng số lượng dữ liệu để hiển thị các con số nổi bật trên Trang chủ.
     */
    public function getHomeStatistics(): array
    {
        return [
            'total_destinations' => Destination::count(),
            'total_trips' => Trip::count(),
            'total_users' => User::where('role', 'user')->count(),
            'total_cities' => City::count(),
        ];
    }

    /**
     * Tìm kiếm địa điểm theo từ khóa (tên địa điểm, mô tả, địa chỉ, thành phố), danh mục và khoảng giá.
     *
     * @param array<string, mixed> $filters
     * @return Collection
     */
    public function searchDestinations(array $filters = []): Collection
    {
        $query = Destination::query()
            ->with(['city', 'category', 'primaryImage', 'roomTypes']);

        // 1. Lọc theo từ khóa tìm kiếm (tên địa điểm hoặc tên thành phố hoặc địa chỉ hoặc mô tả)
        if (!empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhere('address', 'like', "%{$keyword}%")
                  ->orWhereHas('city', function ($cityQuery) use ($keyword) {
                      $cityQuery->where('name', 'like', "%{$keyword}%");
                  });
            });
        }

        // 2. Lọc theo ID thành phố
        if (!empty($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        // 3. Lọc theo ID danh mục
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // 4. Lọc theo mức ngân sách (giá vé / tham quan)
        if (!empty($filters['budget'])) {
            $budget = (string) $filters['budget'];
            if ($budget === '1') {
                // Mức 1: Dưới 500.000đ
                $query->where('entrance_fee', '<', 500000);
            } elseif ($budget === '2') {
                // Mức 2: 500.000đ - 1.500.000đ
                $query->whereBetween('entrance_fee', [500000, 1500000]);
            } elseif ($budget === '3') {
                // Mức 3: Trên 1.500.000đ
                $query->where('entrance_fee', '>', 1500000);
            }
        }

        // 5. Lọc chỉ hiển thị nơi lưu trú
        if (!empty($filters['is_lodging'])) {
            $query->whereHas('roomTypes');
        }

        return $query->orderByDesc('rating')->orderBy('name')->get();
    }

    /**
     * Tìm kiếm nhanh danh sách gợi ý thành phố và địa điểm cho ô tìm kiếm (Autocomplete).
     *
     * @param string $keyword
     * @param int $limit
     * @return array<string, mixed>
     */
    public function quickSearch(string $keyword, int $limit = 6): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [
                'keyword' => '',
                'cities' => [],
                'destinations' => [],
            ];
        }

        // Tìm thành phố phù hợp kèm số lượng địa điểm
        $cities = City::query()
            ->withCount('destinations')
            ->where('name', 'like', "%{$keyword}%")
            ->limit(4)
            ->get()
            ->map(function ($city) {
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                    'destinations_count' => $city->destinations_count,
                    'url' => route('home', ['keyword' => $city->name]),
                ];
            });

        // Tìm địa điểm du lịch phù hợp
        $destinations = Destination::query()
            ->with(['city', 'category', 'roomTypes'])
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhereHas('city', function ($cityQuery) use ($keyword) {
                      $cityQuery->where('name', 'like', "%{$keyword}%");
                  });
            })
            ->orderByDesc('rating')
            ->limit($limit)
            ->get()
            ->map(function ($destination) {
                return [
                    'id' => $destination->id,
                    'name' => $destination->name,
                    'city_name' => $destination->city->name ?? '',
                    'category_name' => $destination->category->name ?? '',
                    'rating' => (float) $destination->rating,
                    'entrance_fee' => (float) $destination->entrance_fee,
                    'has_hotel' => $destination->has_hotel,
                    'formatted_price' => $destination->entrance_fee > 0
                        ? number_format($destination->entrance_fee, 0, ',', '.') . 'đ'
                        : 'Miễn phí',
                    'url' => route('home', ['keyword' => $destination->name]),
                ];
            });

        return [
            'keyword' => $keyword,
            'cities' => $cities->values()->all(),
            'destinations' => $destinations->values()->all(),
        ];
    }

    /**
     * Lấy danh sách địa điểm du lịch có phân trang, hỗ trợ bộ lọc và lọc theo nơi lưu trú.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getDestinations(array $filters = [], int $perPage = 6): LengthAwarePaginator
    {
        $query = Destination::query()
            ->with(['city', 'category', 'primaryImage', 'images', 'roomTypes']);

        // 1. Tìm kiếm theo từ khóa (tên địa điểm, mô tả, địa chỉ hoặc thành phố)
        $search = trim((string) ($filters['search'] ?? $filters['keyword'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhereHas('city', function ($cityQuery) use ($search) {
                      $cityQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Lọc theo Thành phố
        if (!empty($filters['city_id']) && $filters['city_id'] !== 'all') {
            $query->where('city_id', $filters['city_id']);
        }

        // 3. Lọc theo Danh mục
        if (!empty($filters['category_id']) && $filters['category_id'] !== 'all') {
            $query->where('category_id', $filters['category_id']);
        }

        // 4. Lọc theo Khoảng giá
        if (!empty($filters['budget']) && $filters['budget'] !== 'all') {
            $budget = (string) $filters['budget'];
            if ($budget === '1') {
                $query->where('entrance_fee', '<', 500000);
            } elseif ($budget === '2') {
                $query->whereBetween('entrance_fee', [500000, 1500000]);
            } elseif ($budget === '3') {
                $query->where('entrance_fee', '>', 1500000);
            }
        }

        // 5. Lọc chỉ hiển thị Nơi lưu trú (Khách sạn / Resort có roomTypes)
        if (!empty($filters['is_lodging']) && $filters['is_lodging'] !== '0') {
            $query->whereHas('roomTypes');
        }

        // 6. Sắp xếp kết quả
        $sort = (string) ($filters['sort'] ?? 'popular');
        if ($sort === 'price_asc') {
            $query->orderBy('entrance_fee', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('entrance_fee', 'desc');
        } elseif ($sort === 'rating_desc') {
            $query->orderByDesc('rating');
        } elseif ($sort === 'name_asc') {
            $query->orderBy('name', 'asc');
        } else {
            // Mặc định: Độ phổ biến / đánh giá cao nhất
            $query->orderByDesc('rating')->orderByDesc('id');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Lấy chi tiết địa điểm du lịch kèm thư viện ảnh, phòng khách sạn (Room Types) và đánh giá.
     *
     * @param string $slug
     * @return Destination|null
     */
    public function getDestinationBySlug(string $slug): ?Destination
    {
        $query = Destination::query()
            ->with([
                'city',
                'category',
                'images',
                'primaryImage',
                'roomTypes' => function ($q) {
                    $q->orderBy('price_per_night', 'asc');
                },
                'approvedReviews.user',
            ]);

        // Tìm theo slug trước, nếu là ID số thì fallback tìm theo ID
        $destination = $query->where('slug', $slug)->first();
        if (!$destination && is_numeric($slug)) {
            $destination = Destination::query()
                ->with([
                    'city',
                    'category',
                    'images',
                    'primaryImage',
                    'roomTypes' => function ($q) {
                        $q->orderBy('price_per_night', 'asc');
                    },
                    'approvedReviews.user',
                ])
                ->find((int) $slug);
        }

        return $destination;
    }

    /**
     * Lấy thông tin chi tiết của một loại phòng (Room Type) theo ID.
     *
     * @param int $roomTypeId
     * @return RoomType|null
     */
    public function getRoomTypeById(int $roomTypeId): ?RoomType
    {
        return RoomType::with(['destination.city', 'destination.primaryImage'])->find($roomTypeId);
    }

    /**
     * Lấy danh sách các địa điểm liên quan (cùng thành phố hoặc danh mục).
     *
     * @param Destination $destination
     * @param int $limit
     * @return Collection
     */
    public function getRelatedDestinations(Destination $destination, int $limit = 4): Collection
    {
        return Destination::query()
            ->with(['city', 'category', 'primaryImage', 'roomTypes'])
            ->where('id', '!=', $destination->id)
            ->where(function ($q) use ($destination) {
                $q->where('city_id', $destination->city_id)
                  ->orWhere('category_id', $destination->category_id);
            })
            ->orderByDesc('rating')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy danh sách các địa điểm có dịch vụ lưu trú (khách sạn / resort).
     *
     * @param int $limit
     * @return Collection
     */
    public function getLodgingDestinations(int $limit = 6): Collection
    {
        return Destination::query()
            ->with(['city', 'category', 'primaryImage', 'roomTypes'])
            ->whereHas('roomTypes')
            ->orderByDesc('rating')
            ->limit($limit)
            ->get();
    }
}
