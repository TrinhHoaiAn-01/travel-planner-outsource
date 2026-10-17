<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Destination;
use App\Services\Interfaces\CategoryManagementServiceInterface;
use App\Services\Interfaces\CategoryServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryManagementService implements CategoryManagementServiceInterface, CategoryServiceInterface
{
    /**
     * Lấy danh sách các danh mục kèm số lượng điểm đến và bộ lọc.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCategories(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Category::query()->withCount('destinations');

        // Lọc theo từ khóa tìm kiếm (tên danh mục hoặc slug)
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái hiển thị
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $query->where('is_active', (bool) $filters['status']);
        }

        return $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();
    }

    /**
     * Lấy thông tin chi tiết một danh mục theo ID.
     *
     * @param int $categoryId
     * @return Category|null
     */
    public function getCategoryById(int $categoryId): ?Category
    {
        return Category::withCount('destinations')->find($categoryId);
    }

    /**
     * Tạo mới một danh mục du lịch.
     *
     * @param array $data
     * @return Category
     */
    public function createCategory(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $name = trim($data['name']);
            $slug = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($name);

            // Đảm bảo tính duy nhất của slug
            $originalSlug = $slug;
            $counter = 1;
            while (Category::where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-{$counter}";
                $counter++;
            }

            $category = Category::create([
                'name' => $name,
                'slug' => $slug,
                'icon' => ! empty($data['icon']) ? trim($data['icon']) : null,
                'description' => $data['description'] ?? null,
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            ]);

            return $category;
        });
    }

    /**
     * Cập nhật thông tin danh mục du lịch.
     *
     * @param int $categoryId
     * @param array $data
     * @return Category
     */
    public function updateCategory(int $categoryId, array $data): Category
    {
        return DB::transaction(function () use ($categoryId, $data) {
            $category = Category::findOrFail($categoryId);

            $updateData = [];

            if (isset($data['name'])) {
                $updateData['name'] = trim($data['name']);
            }

            if (isset($data['slug'])) {
                $slug = Str::slug($data['slug']);
                if ($slug !== $category->slug) {
                    $originalSlug = $slug;
                    $counter = 1;
                    while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                        $slug = "{$originalSlug}-{$counter}";
                        $counter++;
                    }
                }
                $updateData['slug'] = $slug;
            }

            if (array_key_exists('icon', $data)) {
                $updateData['icon'] = ! empty($data['icon']) ? trim($data['icon']) : null;
            }

            if (array_key_exists('description', $data)) {
                $updateData['description'] = $data['description'];
            }

            if (isset($data['is_active'])) {
                $updateData['is_active'] = (bool) $data['is_active'];
            }

            $category->update($updateData);

            return $category;
        });
    }

    /**
     * Xóa một danh mục khỏi hệ thống (Bảo vệ toàn vẹn dữ liệu BR-17).
     *
     * @param int $categoryId
     * @return bool
     * @throws ValidationException
     */
    public function deleteCategory(int $categoryId): bool
    {
        return DB::transaction(function () use ($categoryId) {
            $category = Category::findOrFail($categoryId);

            // Kiểm tra quy tắc nghiệp vụ BR-17: Không xóa dữ liệu nếu đang có quan hệ ràng buộc
            if ($category->destinations()->exists()) {
                throw ValidationException::withMessages([
                    'delete' => "Không thể xóa danh mục '{$category->name}' vì đang có {$category->destinations()->count()} địa điểm du lịch liên kết. Vui lòng di chuyển hoặc xóa các địa điểm trước theo quy tắc bảo vệ toàn vẹn dữ liệu BR-17.",
                ]);
            }

            return (bool) $category->delete();
        });
    }

    /**
     * Lấy thống kê tổng quan về danh mục và điểm đến du lịch.
     *
     * @return array
     */
    public function getCategoryStatistics(): array
    {
        return [
            'total_categories' => Category::count(),
            'active_categories' => Category::where('is_active', true)->count(),
            'total_destinations' => Destination::count(),
        ];
    }
}
