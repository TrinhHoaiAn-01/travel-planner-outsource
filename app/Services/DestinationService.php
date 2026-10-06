<?php

namespace App\Services;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\Trip;
use App\Models\User;
use App\Services\Interfaces\DestinationServiceInterface;
use Illuminate\Database\Eloquent\Collection;

//Ngocai
class DestinationService implements DestinationServiceInterface
{
    /**
     * Lấy danh sách các địa điểm nổi bật kèm thông tin thành phố, danh mục và ảnh đại diện.
     */
    public function getFeaturedDestinations(int $limit = 6): Collection
    {
        // Eager loading giúp tối ưu câu lệnh SQL, tránh lỗi N+1 query
        return Destination::query()
            ->with(['city', 'category', 'primaryImage'])
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
}
