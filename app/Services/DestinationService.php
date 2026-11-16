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

        // 1. Tìm kiếm bằng Apache Solr hoặc SQL theo engine
        $engine = strtolower((string) ($filters['engine'] ?? 'solr'));
        if (!empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);
            if ($engine === 'sql') {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('description', 'LIKE', "%{$keyword}%")
                      ->orWhere('address', 'LIKE', "%{$keyword}%")
                      ->orWhereHas('city', fn ($cq) => $cq->where('name', 'LIKE', "%{$keyword}%"))
                      ->orWhereHas('category', fn ($catq) => $catq->where('name', 'LIKE', "%{$keyword}%"));
                });
            } else {
                app(\App\Services\Interfaces\SearchServiceInterface::class)->apply($query, 'destination', $keyword);
            }
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
        $filters['engine'] = $engine;
        return $this->searchDestinations($filters);
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

        if ($engine === 'sql') {
            $citiesQuery = City::query()->withCount('destinations')
                ->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('region', 'LIKE', "%{$keyword}%");
                });
            $destinationsQuery = Destination::query()->with(['city', 'category', 'roomTypes'])
                ->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('description', 'LIKE', "%{$keyword}%")
                      ->orWhere('address', 'LIKE', "%{$keyword}%")
                      ->orWhereHas('city', fn ($cq) => $cq->where('name', 'LIKE', "%{$keyword}%"))
                      ->orWhereHas('category', fn ($catq) => $catq->where('name', 'LIKE', "%{$keyword}%"));
                });
        } else {
            $search = app(\App\Services\Interfaces\SearchServiceInterface::class);
            $citiesQuery = City::query()->withCount('destinations');
            $search->apply($citiesQuery, 'city', $keyword);

            $destinationsQuery = Destination::query()->with(['city', 'category', 'roomTypes']);
            $search->apply($destinationsQuery, 'destination', $keyword);
        }

        $cities = $citiesQuery
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
        $destinations = $destinationsQuery
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
                    'url' => route('destinations.show', $destination->slug ?? $destination->id),
                ];
            });

        return [
            'keyword' => $keyword,
            'cities' => $cities->values()->all(),
            'destinations' => $destinations->values()->all(),
        ];
    }

    private function _legacyQuickSearch(string $keyword, int $limit, string $engine): array
    {
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
                                'url' => route('home', ['keyword' => $destination->name, 'engine' => 'solr']),
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
                        'url' => route('home', ['keyword' => $city->name, 'engine' => 'solr']),
                    ];
                });

                // Solr hoạt động bình thường thì luôn trả về kết quả kèm engine=solr
                return [
                    'engine' => 'solr',
                    'keyword' => $keyword,
                    'destinations' => $destinations->values()->all(),
                    'cities' => $cities->values()->all(),
                ];
            } catch (\Throwable $e) {
                if (function_exists('logger')) {
                    logger()->warning('Lỗi quickSearch Solr, chuyển sang SQL dự phòng: ' . $e->getMessage());
                }
            }
        }

        // 2. SQL Search (khi người dùng chọn engine=sql hoặc Solr gặp sự cố gián đoạn)
        $cityCompounds = [
            '/\bha\s*noi\b/ui' => 'Hà Nội',
            '/\bhanoi\b/ui' => 'Hà Nội',
            '/\bda\s*nang\b/ui' => 'Đà Nẵng',
            '/\bdanang\b/ui' => 'Đà Nẵng',
            '/\bphu\s*quoc\b/ui' => 'Phú Quốc',
            '/\bphuquoc\b/ui' => 'Phú Quốc',
            '/\bha\s*long\b/ui' => 'Hạ Long',
            '/\bhalong\b/ui' => 'Hạ Long',
            '/\bnha\s*trang\b/ui' => 'Nha Trang',
            '/\bnhatrang\b/ui' => 'Nha Trang',
            '/\bsai\s*gon\b/ui' => 'Sài Gòn',
            '/\bsaigon\b/ui' => 'Sài Gòn',
            '/\bda\s*lat\b/ui' => 'Đà Lạt',
            '/\bdalat\b/ui' => 'Đà Lạt',
        ];
        $expandedKeyword = preg_replace(array_keys($cityCompounds), array_values($cityCompounds), $keyword) ?: $keyword;
        $tokens = preg_split('/\s+/', $expandedKeyword, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $cities = City::query()->withCount('destinations')
            ->where(function ($q) use ($keyword, $expandedKeyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('name', 'LIKE', "%{$expandedKeyword}%");
            })
            ->limit(4)
            ->get()
            ->map(function ($city) use ($engine) {
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                    'destinations_count' => $city->destinations_count,
                    'url' => route('home', ['keyword' => $city->name, 'engine' => $engine]),
                ];
            });

        $destinations = Destination::query()->with(['city', 'category', 'roomTypes'])
            ->where(function ($q) use ($keyword, $expandedKeyword, $tokens) {
                $q->where(function ($exactQ) use ($keyword, $expandedKeyword) {
                    $exactQ->where('name', 'LIKE', "%{$keyword}%")
                        ->orWhere('name', 'LIKE', "%{$expandedKeyword}%")
                        ->orWhere('address', 'LIKE', "%{$keyword}%")
                        ->orWhere('address', 'LIKE', "%{$expandedKeyword}%")
                        ->orWhere('description', 'LIKE', "%{$keyword}%")
                        ->orWhere('description', 'LIKE', "%{$expandedKeyword}%")
                        ->orWhereHas('city', fn ($cq) => $cq->where('name', 'LIKE', "%{$keyword}%")->orWhere('name', 'LIKE', "%{$expandedKeyword}%"));
                });

                if (count($tokens) > 1) {
                    $q->orWhere(function ($allTokensQ) use ($tokens) {
                        foreach ($tokens as $token) {
                            $allTokensQ->where(function ($singleQ) use ($token) {
                                $singleQ->where('name', 'LIKE', "%{$token}%")
                                    ->orWhere('address', 'LIKE', "%{$token}%")
                                    ->orWhere('description', 'LIKE', "%{$token}%")
                                    ->orWhereHas('city', fn ($cq) => $cq->where('name', 'LIKE', "%{$token}%"));
                            });
                        }
                    });
                }
            })
            ->orderByDesc('rating')
            ->limit($limit)
            ->get()
            ->map(function ($destination) use ($engine) {
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
                    'url' => route('home', ['keyword' => $destination->name, 'engine' => $engine]),
                ];
            });

        return [
            'engine' => ($engine === 'solr') ? 'sql_fallback' : 'sql',
            'keyword' => $keyword,
            'destinations' => $destinations->values()->all(),
            'cities' => $cities->values()->all(),
        ];
    }

    /**
     * Lấy danh sách địa điểm du lịch có phân trang, hỗ trợ bộ lọc và công cụ tìm kiếm (Solr hoặc SQL).
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @param string $engine 'solr' (mặc định) hoặc 'sql'
     * @return LengthAwarePaginator
     */
    public function getDestinations(array $filters = [], int $perPage = 6, string $engine = 'solr'): LengthAwarePaginator
    {
        $engine = strtolower((string) ($filters['engine'] ?? $engine));
        if (! in_array($engine, ['solr', 'sql'], true)) {
            $engine = 'solr';
        }

        // Đảm bảo package Search được nạp an toàn trong mọi môi trường
        if (! class_exists(\Search\HomeSearch\HomeSearchManager::class)) {
            spl_autoload_register(function ($class) {
                $prefix = 'Search\\HomeSearch\\';
                if (str_starts_with($class, $prefix)) {
                    $relativeClass = substr($class, strlen($prefix));
                    $file = base_path('packages/search/src/' . str_replace('\\', '/', $relativeClass) . '.php');
                    if (file_exists($file)) {
                        require_once $file;
                    }
                }
            });
        }

        $query = Destination::query()
            ->with(['city', 'category', 'primaryImage', 'images', 'roomTypes']);

        // 1. Tìm kiếm bằng Apache Solr hoặc SQL theo engine
        $search = trim((string) ($filters['search'] ?? ($filters['keyword'] ?? '')));
        if ($search !== '') {
            if ($engine === 'sql') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhere('address', 'LIKE', "%{$search}%")
                      ->orWhereHas('city', fn ($cq) => $cq->where('name', 'LIKE', "%{$search}%"))
                      ->orWhereHas('category', fn ($catq) => $catq->where('name', 'LIKE', "%{$search}%"));
                });
            } else {
                app(\App\Services\Interfaces\SearchServiceInterface::class)->apply($query, 'destination', $search);
            }
        }

        // 2. Lọc theo Thành phố
        if (!empty($filters['city_id']) && $filters['city_id'] !== 'all') {
            $query->where('city_id', $filters['city_id']);
        }

        // 2b. Lọc trực tiếp theo ID địa điểm từ bộ lọc nhanh (Quick Filter)
        if (!empty($filters['destination_id']) && $filters['destination_id'] !== 'all') {
            $query->where('destinations.id', $filters['destination_id']);
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
            // Mặc định: Nếu có danh sách ID từ Solr/SQL, giữ đúng thứ tự điểm số liên quan
            if (!empty($ids)) {
                if ($query->getConnection()->getDriverName() === 'sqlite') {
                    $orderCases = [];
                    foreach ($ids as $index => $id) {
                        $orderCases[] = "WHEN {$id} THEN {$index}";
                    }
                    $query->orderByRaw('CASE id ' . implode(' ', $orderCases) . ' END');
                } else {
                    $query->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')');
                }
            } else {
                // Độ phổ biến / đánh giá cao nhất
                $query->orderByDesc('rating')->orderByDesc('id');
            }
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
        // 1. Trích xuất từ khóa nhận diện chính từ tên điểm đến (ví dụ: 'Messi' từ 'Kỷ tưởng niệm Messi')
        $cleanName = trim(preg_replace('/^(kỷ niệm|kỷ tưởng niệm|khách sạn|khu du lịch|bảo tàng|sân vận động)\s+/iu', '', $destination->name));
        $keyword = mb_strlen($cleanName) >= 3 ? $cleanName : null;

        // 2. Tìm kiếm các điểm đến liên quan trực tiếp: cùng thành phố, cùng danh mục hoặc cùng từ khóa thương hiệu/địa danh
        $related = Destination::query()
            ->with(['city', 'category', 'primaryImage', 'images', 'roomTypes'])
            ->where('id', '!=', $destination->id)
            ->where(function ($q) use ($destination, $keyword) {
                $q->where('city_id', $destination->city_id)
                    ->orWhere('category_id', $destination->category_id);

                if (! empty($keyword)) {
                    $q->orWhere('name', 'like', "%{$keyword}%");
                }
            })
            ->orderByRaw("
                CASE 
                    WHEN city_id = {$destination->city_id} AND category_id = {$destination->category_id} THEN 1
                    WHEN city_id = {$destination->city_id} THEN 2
                    WHEN category_id = {$destination->category_id} THEN 3
                    ELSE 4
                END ASC
            ")
            ->orderByDesc('rating')
            ->limit($limit)
            ->get();

        // 3. Dự phòng chuẩn nghiệp vụ: nếu chưa đủ số lượng $limit, tự động bổ sung các điểm đến nổi bật khác để lấp đầy giao diện
        if ($related->count() < $limit) {
            $excludeIds = $related->pluck('id')->push($destination->id)->all();
            $moreDestinations = Destination::query()
                ->with(['city', 'category', 'primaryImage', 'images', 'roomTypes'])
                ->whereNotIn('id', $excludeIds)
                ->orderByDesc('is_featured')
                ->orderByDesc('rating')
                ->limit($limit - $related->count())
                ->get();

            $related = $related->merge($moreDestinations);
        }

        return $related;
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

    /**
     * Lấy danh sách thành phố phục vụ bộ lọc nhanh (Quick Filter) trên trang khám phá.
     * Tối đa $limit thành phố có trong dữ liệu admin (không dùng data mẫu, nếu có 2 thì hiện 2),
     * được sắp xếp theo tổng số tiền người dùng chi trả cho các điểm đến (destinations) trong thành phố đó nhiều nhất.
     *
     * @param int $limit
     * @return Collection
     */
    public function getQuickFilterCities(int $limit = 3): Collection
    {
        return City::query()
            ->where('is_active', true)
            ->select('cities.*')
            ->selectSub(function ($query) {
                $query->from('bookings')
                    ->join('room_types', 'bookings.room_type_id', '=', 'room_types.id')
                    ->join('destinations', 'room_types.destination_id', '=', 'destinations.id')
                    ->leftJoin('payments', function ($pJoin) {
                        $pJoin->on('bookings.id', '=', 'payments.booking_id')
                            ->where('payments.status', '=', 'completed');
                    })
                    ->whereColumn('destinations.city_id', 'cities.id')
                    ->whereNotIn('bookings.status', ['cancelled'])
                    ->selectRaw('COALESCE(SUM(CASE WHEN payments.id IS NOT NULL THEN payments.amount ELSE bookings.total_price END), 0)');
            }, 'total_spent')
            ->orderByDesc('total_spent')
            ->orderBy('cities.id', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy danh sách địa điểm phục vụ bộ lọc nhanh (Quick Filter) trên trang khám phá.
     * Tối đa $limit địa điểm có trong data admin (không dùng data mẫu, nếu có 2 thì hiện 2),
     * được xếp theo tổng số tiền người dùng chi trả nhiều nhất.
     *
     * @param int $limit
     * @return Collection
     */
    public function getQuickFilterDestinations(int $limit = 3): Collection
    {
        return Destination::query()
            ->with(['city', 'category', 'primaryImage'])
            ->select('destinations.*')
            ->selectSub(function ($query) {
                $query->from('bookings')
                    ->join('room_types', 'bookings.room_type_id', '=', 'room_types.id')
                    ->leftJoin('payments', function ($pJoin) {
                        $pJoin->on('bookings.id', '=', 'payments.booking_id')
                            ->where('payments.status', '=', 'completed');
                    })
                    ->whereColumn('room_types.destination_id', 'destinations.id')
                    ->whereNotIn('bookings.status', ['cancelled'])
                    ->selectRaw('COALESCE(SUM(CASE WHEN payments.id IS NOT NULL THEN payments.amount ELSE bookings.total_price END), 0)');
            }, 'total_spent')
            ->orderByDesc('total_spent')
            ->orderByDesc('destinations.rating')
            ->orderBy('destinations.id', 'asc')
            ->limit($limit)
            ->get();
    }
}

