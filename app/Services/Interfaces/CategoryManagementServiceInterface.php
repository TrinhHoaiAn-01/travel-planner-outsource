<?php

namespace App\Services\Interfaces;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryManagementServiceInterface
{
    /**
     * Lấy danh sách các danh mục kèm số lượng điểm đến và bộ lọc.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCategories(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Lấy thông tin chi tiết một danh mục theo ID.
     *
     * @param int $categoryId
     * @return Category|null
     */
    public function getCategoryById(int $categoryId): ?Category;

    /**
     * Tạo mới một danh mục du lịch.
     *
     * @param array $data
     * @return Category
     */
    public function createCategory(array $data): Category;

    /**
     * Cập nhật thông tin danh mục du lịch.
     *
     * @param int $categoryId
     * @param array $data
     * @return Category
     */
    public function updateCategory(int $categoryId, array $data): Category;

    /**
     * Xóa một danh mục khỏi hệ thống (Bảo vệ toàn vẹn dữ liệu BR-17).
     *
     * @param int $categoryId
     * @return bool
     */
    public function deleteCategory(int $categoryId): bool;

    /**
     * Lấy thống kê tổng quan về danh mục và điểm đến du lịch.
     *
     * @return array
     */
    public function getCategoryStatistics(): array;
}
