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
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('address', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%")
                    ->orWhereHas('city', fn ($cq) => $cq->where('name', 'LIKE', "%{$keyword}%"));
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
     * Tìm kiếm địa điểm cho Trang chủ theo bộ lọc và công cụ lựa chọn (Solr / SQL).
     * Tích hợp qua Vendor Package Search\HomeSearch\HomeSearchManager.
     *
     * @param array<string, mixed> $filters
     * @param string $engine 'solr' (mặc định) hoặc 'sql'
     * @return Collection
     */
    public function searchHomeDestinations(array $filters = [], string $engine = 'solr'): Collection
    {
        $homeSearchManager = new \Search\HomeSearch\HomeSearchManager();
        $searchResult = $homeSearchManager->search($filters, $engine);
        $ids = $searchResult['ids'] ?? [];

        $keyword = trim((string) ($filters['keyword'] ?? ''));
        $cityId = !empty($filters['city_id']) ? (int) $filters['city_id'] : null;
        $categoryId = !empty($filters['category_id']) ? (int) $filters['category_id'] : null;
        $budget = !empty($filters['budget']) ? (int) $filters['budget'] : null;

        // Nếu có từ khóa tìm kiếm nhưng Solr không tìm thấy bất kỳ ID nào thì trả về rỗng ngay
        if (empty($ids) && $keyword !== '' && ($searchResult['engine_used'] ?? '') === 'solr') {
            return new Collection();
        }

        $query = Destination::query()
            ->with(['city', 'category', 'primaryImage', 'roomTypes']);

        // Nếu có danh sách ID từ search engine (Solr hoặc SQL)
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
            if (($searchResult['engine_used'] ?? '') === 'solr') {
                if ($query->getConnection()->getDriverName() === 'sqlite') {
                    $orderCases = [];
                    foreach ($ids as $index => $id) {
                        $orderCases[] = "WHEN {$id} THEN {$index}";
                    }
                    $query->orderByRaw('CASE id ' . implode(' ', $orderCases) . ' END');
                } else {
                    $query->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')');
                }
            }
        } elseif ($keyword !== '') {
            // Trường hợp fallback SQL tìm theo LIKE
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('address', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%")
                    ->orWhereHas('city', fn ($cq) => $cq->where('name', 'LIKE', "%{$keyword}%"));
            });
        }

        if ($cityId !== null) {
            $query->where('city_id', $cityId);
        }

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        if ($budget === 1) {
            $query->where('entrance_fee', '<', 500000);
        } elseif ($budget === 2) {
            $query->whereBetween('entrance_fee', [500000, 1500000]);
        } elseif ($budget === 3) {
            $query->where('entrance_fee', '>', 1500000);
        }

        if (!empty($filters['is_lodging'])) {
            $query->whereHas('roomTypes');
        }

        if (empty($ids) || ($searchResult['engine_used'] ?? '') !== 'solr') {
            $query->orderByDesc('rating')->orderBy('name');
        }

        return $query->get();
    }

    /**
     * Tìm kiếm nhanh danh sách gợi ý thành phố và địa điểm cho ô tìm kiếm (Autocomplete).
     *
     * @param string $keyword
     * @param int $limit
     * @return array<string, mixed>
     */
    public function quickSearch(string $keyword, int $limit = 6, string $engine = 'solr'): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [
                'keyword' => '',
                'cities' => [],
                'destinations' => [],
            ];
        }

        $engine = strtolower($engine);
        $isTesting = defined('PHPUNIT_COMPOSER_INSTALL')
            || class_exists(\PHPUnit\Framework\TestCase::class, false)
            || (function_exists('app') && (app()->environment('testing') || app()->runningUnitTests()));

        // 1. Nếu chọn Solr và không trong môi trường test, ưu tiên tìm kiếm bằng Solr engine qua Vendor Package
        if ($engine === 'solr' && ! $isTesting) {
            try {
                $solrClient = new \Search\HomeSearch\Solr\SolrClient();
                $solrQ = \Search\HomeSearch\Solr\SolrQueryBuilder::build($keyword);
                $params = [
                    'q' => $solrQ,
                    'defType' => 'edismax',
                    'qf' => 'name^6 city_name^4 category_name^2 address^2 description',
                    'rows' => $limit,
                    'fl' => 'id,city_name,name,score',
                    'wt' => 'json',
                ];

                $searchResult = $solrClient->select($params);
                $docs = $searchResult['docs'] ?? [];

                // Lấy danh sách điểm đến từ Solr
                $destinations = collect();
                if (! empty($docs)) {
                    $ids = array_map(fn ($doc) => (int) $doc['id'], $docs);

                    $destQuery = Destination::query()
                        ->with(['city', 'category', 'roomTypes'])
                        ->whereIn('id', $ids);

                    if ($destQuery->getConnection()->getDriverName() === 'sqlite') {
                        $orderCases = [];
                        foreach ($ids as $index => $id) {
                            $orderCases[] = "WHEN {$id} THEN {$index}";
                        }
                        $destQuery->orderByRaw('CASE id ' . implode(' ', $orderCases) . ' END');
                    } else {
                        $destQuery->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')');
                    }

                    $destinations = $destQuery->get()
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
                }

                // Lấy danh sách tên thành phố từ các điểm đến Solr tìm được
                $destinationCityNames = [];
                foreach ($docs as $doc) {
                    $cName = is_array($doc['city_name'] ?? null) ? $doc['city_name'][0] : ($doc['city_name'] ?? null);
                    if ($cName) {
                        $destinationCityNames[] = $cName;
                    }
                }

                // Gợi ý thành phố: Bao gồm cả thành phố khớp trực tiếp từ khóa VÀ thành phố của các điểm đến tìm được
                $normalizedK = \Illuminate\Support\Str::ascii(mb_strtolower($keyword));
                $cleanK = preg_replace('/([a-z0-9])\1{1,}$/ui', '$1', (string) $normalizedK);
                $cleanK = preg_replace('/[^a-z0-9]/', '', (string) $cleanK);

                $allCities = City::query()->withCount('destinations')->get();

                // 1. Thành phố khớp trực tiếp với từ khóa (ví dụ: danang -> Đà Nẵng, hanoi -> Hà Nội)
                $directCities = collect();
                if (strlen($cleanK) >= 2) {
                    $directCities = $allCities->filter(function ($city) use ($normalizedK, $cleanK) {
                        $cName = \Illuminate\Support\Str::ascii(mb_strtolower($city->name));
                        $cleanC = preg_replace('/[^a-z0-9]/', '', $cName);

                        return str_contains($cName, $normalizedK) || str_contains($cleanC, $cleanK);
                    });
                }

                // 2. Thành phố của các điểm đến tìm được (ví dụ: tìm "messi" -> có thành phố "Hà Nội")
                $relatedCities = $allCities->whereIn('name', array_unique($destinationCityNames));

                // Kết hợp cả hai nguồn thành phố (loại bỏ trùng lặp ID, lấy tối đa 4 thành phố)
                $cities = $directCities->concat($relatedCities)->unique('id')->slice(0, 4)->values()->map(function ($city) {
                    return [
                        'id' => $city->id,
                        'name' => $city->name,
                        'destinations_count' => $city->destinations_count,
                        'url' => route('home', ['keyword' => $city->name]),
                    ];
                });

                // Nếu Solr có kết quả điểm đến HOẶC có thành phố khớp thì trả về kết quả
                // Đặt 'destinations' trước 'cities' để khi kiểm tra Postman hoặc giao diện, điểm đến (như Messi) luôn hiện lên đầu
                if (! empty($docs) || $cities->isNotEmpty()) {
                    return [
                        'engine' => 'solr',
                        'keyword' => $keyword,
                        'destinations' => $destinations->values()->all(),
                        'cities' => $cities->values()->all(),
                    ];
                }
            } catch (\Throwable $e) {
                if (function_exists('logger')) {
                    logger()->warning('Lỗi quickSearch Solr, chuyển sang SQL: ' . $e->getMessage());
                }
            }
        }

        // 2. Fallback SQL Index (hoặc khi người dùng chọn engine=sql)
        $cities = City::query()->withCount('destinations')
            ->where('name', 'LIKE', "%{$keyword}%")
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

        $destinations = Destination::query()->with(['city', 'category', 'roomTypes'])
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('address', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%")
                    ->orWhereHas('city', fn ($cq) => $cq->where('name', 'LIKE', "%{$keyword}%"));
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
            'engine' => 'sql',
            'keyword' => $keyword,
            'destinations' => $destinations->values()->all(),
            'cities' => $cities->values()->all(),
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
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('address', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhereHas('city', fn ($cq) => $cq->where('name', 'LIKE', "%{$search}%"));
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

    /**
     * Lấy toàn bộ thư viện hình ảnh của địa điểm du lịch.
     *
     * @param Destination $destination
     * @return Collection
     */
    public function getDestinationGallery(Destination $destination): Collection
    {
        return $destination->images()
            ->orderByDesc('is_primary')
            ->orderBy('id', 'asc')
            ->get();
    }
}

