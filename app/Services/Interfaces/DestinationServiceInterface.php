<?php

namespace App\Services\Interfaces;

use App\Models\Destination;
use App\Models\RoomType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

//Ngocai
interface DestinationServiceInterface
{
    /**
     * Lấy danh sách các địa điểm du lịch nổi bật trên trang chủ.
     *
     * @param int $limit
     * @return Collection
     */
    public function getFeaturedDestinations(int $limit = 6): Collection;

    /**
     * Lấy danh sách các thành phố phổ biến.
     *
     * @param int $limit
     * @return Collection
     */
    public function getPopularCities(int $limit = 6): Collection;

    /**
     * Lấy danh sách danh mục trải nghiệm du lịch.
     *
     * @return Collection
     */
    public function getCategories(): Collection;

    /**
     * Lấy các con số thống kê tổng quan của hệ thống.
     *
     * @return array
     */
    public function getHomeStatistics(): array;

    /**
     * Tìm kiếm và lọc danh sách địa điểm theo từ khóa, thành phố, danh mục và khoảng giá.
     *
     * @param array<string, mixed> $filters
     * @return Collection
     */
    public function searchDestinations(array $filters = []): Collection;

    /**
     * Tìm kiếm nhanh gợi ý địa điểm và thành phố cho thanh tìm kiếm (Quick Search Autocomplete).
     *
     * @param string $keyword
     * @param int $limit
     * @return array<string, mixed>
     */
    public function quickSearch(string $keyword, int $limit = 6): array;

    /**
     * Lấy danh sách địa điểm du lịch có phân trang, hỗ trợ bộ lọc và lọc theo nơi lưu trú.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getDestinations(array $filters = [], int $perPage = 6): LengthAwarePaginator;

    /**
     * Lấy chi tiết địa điểm du lịch kèm thư viện ảnh, phòng khách sạn (Room Types) và đánh giá.
     *
     * @param string $slug
     * @return Destination|null
     */
    public function getDestinationBySlug(string $slug): ?Destination;

    /**
     * Lấy thông tin chi tiết của một loại phòng (Room Type) theo ID.
     *
     * @param int $roomTypeId
     * @return RoomType|null
     */
    public function getRoomTypeById(int $roomTypeId): ?RoomType;

    /**
     * Lấy danh sách các địa điểm liên quan (cùng thành phố hoặc danh mục).
     *
     * @param Destination $destination
     * @param int $limit
     * @return Collection
     */
    public function getRelatedDestinations(Destination $destination, int $limit = 4): Collection;

    /**
     * Lấy danh sách các địa điểm có dịch vụ lưu trú (khách sạn / resort).
     *
     * @param int $limit
     * @return Collection
     */
    public function getLodgingDestinations(int $limit = 6): Collection;
}
