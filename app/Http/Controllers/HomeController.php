<?php

namespace App\Http\Controllers;

require_once app_path('Services/Search/Home/home_search.php');
require_once app_path('Services/Search/Home/home_search_sql_index.php');

use App\Services\Interfaces\DestinationServiceInterface;
use App\Services\Interfaces\FavoriteServiceInterface;
use App\Services\Search\Home\HomeSearch;
use App\Services\Search\Home\HomeSearchSqlIndex;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Tiêm phụ thuộc DestinationServiceInterface và FavoriteServiceInterface vào Controller.
     */
    public function __construct(
        private readonly DestinationServiceInterface $destinationService,
        private readonly FavoriteServiceInterface $favoriteService
    ) {
    }

    /**
     * Hiển thị Trang chủ công khai (Public Home) kèm kết quả tìm kiếm nếu có.
     * Hỗ trợ trả về JSON khi test qua Postman (Accept: application/json hoặc ?format=json).
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $hasSearch = $request->filled('keyword')
            || $request->filled('category_id')
            || $request->filled('budget')
            || $request->filled('city_id');

        // Lấy engine tìm kiếm được chọn từ form: 'solr' (mặc định) hoặc 'sql'
        $engine = strtolower((string) $request->input('engine', 'solr'));

        if ($hasSearch) {
            $filters = $request->only(['keyword', 'category_id', 'budget', 'city_id']);
            // Điều hướng sang công cụ tìm kiếm tương ứng
            $searchResults = ($engine === 'sql')
                ? HomeSearchSqlIndex::search($filters)
                : HomeSearch::search($filters);
        } else {
            $searchResults = null;
        }

        // Hỗ trợ test trực tiếp bằng Postman trả về định dạng JSON gọn gàng, nhanh chóng
        if ($request->wantsJson() || $request->query('format') === 'json') {
            $mappedData = $searchResults ? $searchResults->map(function ($dest) {
                return [
                    'id' => $dest->id,
                    'name' => $dest->name,
                    'city' => $dest->city?->name,
                    'category' => $dest->category?->name,
                    'rating' => (float) $dest->rating,
                    'entrance_fee' => (float) $dest->entrance_fee,
                    'address' => $dest->address,
                ];
            }) : [];

            return response()->json([
                'success' => true,
                'engine' => $engine,
                'keyword' => $request->query('keyword'),
                'total' => count($mappedData),
                'data' => $mappedData,
            ]);
        }

        $featuredDestinations = $this->destinationService->getFeaturedDestinations(6);
        $popularCities = $this->destinationService->getPopularCities(6);
        $categories = $this->destinationService->getCategories();
        $statistics = $this->destinationService->getHomeStatistics();
        $userFavoriteIds = $this->favoriteService->getUserFavoriteDestinationIds(auth()->user());

        return view('home', compact(
            'featuredDestinations',
            'popularCities',
            'categories',
            'statistics',
            'hasSearch',
            'searchResults',
            'userFavoriteIds',
            'engine'
        ));
    }

    /**
     * API Tìm kiếm nhanh gợi ý địa điểm và thành phố cho thanh tìm kiếm (Autocomplete).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function quickSearch(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->query('keyword', ''));
        $engine = strtolower((string) $request->query('engine', 'solr'));
        $data = $this->destinationService->quickSearch($keyword, 6, $engine);

        return response()->json($data);
    }
}
