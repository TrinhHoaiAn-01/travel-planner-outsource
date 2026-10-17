<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\DestinationServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Tiêm phụ thuộc DestinationServiceInterface vào Controller.
     */
    public function __construct(
        private readonly DestinationServiceInterface $destinationService
    ) {
    }

    /**
     * Hiển thị Trang chủ công khai (Public Home) kèm kết quả tìm kiếm nếu có.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $hasSearch = $request->filled('keyword')
            || $request->filled('category_id')
            || $request->filled('budget')
            || $request->filled('city_id');

        $searchResults = $hasSearch
            ? $this->destinationService->searchDestinations($request->only(['keyword', 'category_id', 'budget', 'city_id']))
            : null;

        $featuredDestinations = $this->destinationService->getFeaturedDestinations(6);
        $popularCities = $this->destinationService->getPopularCities(6);
        $categories = $this->destinationService->getCategories();
        $statistics = $this->destinationService->getHomeStatistics();

        return view('home', compact(
            'featuredDestinations',
            'popularCities',
            'categories',
            'statistics',
            'hasSearch',
            'searchResults'
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
        $data = $this->destinationService->quickSearch($keyword, 6);

        return response()->json($data);
    }
}
