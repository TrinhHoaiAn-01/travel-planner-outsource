<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\DestinationServiceInterface;
use App\Services\Interfaces\FavoriteServiceInterface;
use Illuminate\Contracts\View\View;

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
     * Hiển thị Trang chủ công khai (Public Home).
     */
    public function index(): View
    {
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
            'userFavoriteIds'
        ));
    }
}
