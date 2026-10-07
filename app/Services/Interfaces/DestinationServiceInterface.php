<?php

namespace App\Services\Interfaces;

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
}
