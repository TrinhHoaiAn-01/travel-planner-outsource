<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Services\CategoryManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CategoryManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private CategoryManagementService $categoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryService = new CategoryManagementService();
    }

    /**
     * Kiểm thử lấy danh sách danh mục có phân trang và đếm số lượng điểm đến.
     */
    public function test_get_categories_returns_paginated_categories_with_destination_count(): void
    {
        $category = Category::create([
            'name' => 'Nghỉ dưỡng & Resort',
            'slug' => 'resort-spa',
            'icon' => 'hotel',
            'is_active' => true,
        ]);

        $city = City::create([
            'name' => 'Đà Nẵng',
            'slug' => 'da-nang',
            'code' => 'CTY-DAD',
        ]);

        Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'InterContinental Danang',
            'slug' => 'intercontinental-danang',
            'entrance_fee' => 0,
        ]);

        $result = $this->categoryService->getCategories(['search' => 'Resort']);
        $this->assertSame(1, $result->total());
        $this->assertSame(1, $result->first()->destinations_count);
    }

    /**
     * Kiểm thử tạo danh mục mới tự động sinh slug và gán icon.
     */
    public function test_create_category_creates_record_and_auto_generates_slug(): void
    {
        $category = $this->categoryService->createCategory([
            'name' => 'Ẩm Thực Đặc Sản',
            'icon' => 'food',
            'description' => 'Khám phá văn hóa ẩm thực các vùng miền',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Ẩm Thực Đặc Sản',
            'slug' => 'am-thuc-dac-san',
            'icon' => 'food',
            'is_active' => true,
        ]);
    }

    /**
     * Kiểm thử cập nhật thông tin danh mục du lịch.
     */
    public function test_update_category_updates_attributes(): void
    {
        $category = Category::create([
            'name' => 'Biển Đảo',
            'slug' => 'bien-dao',
            'icon' => 'waves',
            'is_active' => true,
        ]);

        $updated = $this->categoryService->updateCategory($category->id, [
            'name' => 'Biển & Đảo Kỳ Thú',
            'slug' => 'bien-dao-ky-thu',
            'icon' => 'beach',
            'is_active' => false,
        ]);

        $this->assertSame('Biển & Đảo Kỳ Thú', $updated->name);
        $this->assertSame('bien-dao-ky-thu', $updated->slug);
        $this->assertSame('beach', $updated->icon);
        $this->assertFalse($updated->is_active);
    }

    /**
     * Kiểm thử quy tắc BR-17: Không cho phép xóa danh mục khi đang có địa điểm du lịch liên kết.
     */
    public function test_delete_category_throws_validation_exception_when_destinations_exist(): void
    {
        $category = Category::create([
            'name' => 'Di Sản Văn Hóa',
            'slug' => 'di-san-van-hoa',
            'icon' => 'landmark',
            'is_active' => true,
        ]);

        $city = City::create([
            'name' => 'Hội An',
            'slug' => 'hoi-an',
            'code' => 'CTY-HOI',
        ]);

        Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Phố cổ Hội An',
            'slug' => 'pho-co-hoi-an',
            'entrance_fee' => 120000,
        ]);

        $this->expectException(ValidationException::class);
        $this->categoryService->deleteCategory($category->id);
    }

    /**
     * Kiểm thử xóa danh mục thành công khi không có địa điểm du lịch nào liên kết.
     */
    public function test_delete_category_succeeds_when_no_destinations_exist(): void
    {
        $category = Category::create([
            'name' => 'Danh Mục Trống',
            'slug' => 'danh-muc-trong',
            'icon' => 'default',
            'is_active' => true,
        ]);

        $result = $this->categoryService->deleteCategory($category->id);
        $this->assertTrue($result);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /**
     * Kiểm thử lấy số liệu thống kê danh mục và điểm đến.
     */
    public function test_get_category_statistics_returns_correct_summary(): void
    {
        Category::create(['name' => 'Cat 1', 'slug' => 'cat-1', 'is_active' => true]);
        Category::create(['name' => 'Cat 2', 'slug' => 'cat-2', 'is_active' => false]);

        $stats = $this->categoryService->getCategoryStatistics();
        $this->assertSame(2, $stats['total_categories']);
        $this->assertSame(1, $stats['active_categories']);
    }
}
