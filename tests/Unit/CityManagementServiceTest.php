<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Services\CityManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CityManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private CityManagementService $cityService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cityService = new CityManagementService();
    }

    /**
     * Kiểm thử lấy danh sách thành phố có phân trang và bộ lọc.
     */
    public function test_get_cities_returns_paginated_cities_with_destination_count(): void
    {
        $city = City::create([
            'name' => 'Đà Nẵng',
            'slug' => 'da-nang',
            'code' => 'CTY-DAD',
            'region' => 'Miền Trung',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Biển',
            'slug' => 'bien',
        ]);

        Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Bà Nà Hills',
            'slug' => 'ba-na-hills',
            'entrance_fee' => 900000,
        ]);

        $result = $this->cityService->getCities(['search' => 'Đà Nẵng']);
        $this->assertSame(1, $result->total());
        $this->assertSame(1, $result->first()->destinations_count);
    }

    /**
     * Kiểm thử tạo thành phố mới tự động sinh code và slug.
     */
    public function test_create_city_creates_record_and_auto_generates_code(): void
    {
        $city = $this->cityService->createCity([
            'name' => 'Cần Thơ',
            'region' => 'Miền Nam',
            'description' => 'Thủ phủ miền Tây sông nước',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('cities', [
            'id' => $city->id,
            'name' => 'Cần Thơ',
            'slug' => 'can-tho',
        ]);
        $this->assertNotEmpty($city->code);
    }

    /**
     * Kiểm thử cập nhật thông tin thành phố.
     */
    public function test_update_city_updates_attributes(): void
    {
        $city = City::create([
            'name' => 'Nha Trang',
            'slug' => 'nha-trang',
            'code' => 'CTY-NTR',
            'region' => 'Miền Trung',
            'is_active' => true,
        ]);

        $updated = $this->cityService->updateCity($city->id, [
            'name' => 'Nha Trang (Khánh Hòa)',
            'region' => 'Duyên hải Nam Trung Bộ',
        ]);

        $this->assertSame('Nha Trang (Khánh Hòa)', $updated->name);
        $this->assertSame('Duyên hải Nam Trung Bộ', $updated->region);
    }

    /**
     * Kiểm thử quy tắc BR-17: Không cho phép xóa thành phố khi đang có địa điểm du lịch liên kết.
     */
    public function test_delete_city_throws_validation_exception_when_destinations_exist(): void
    {
        $city = City::create([
            'name' => 'Hội An',
            'slug' => 'hoi-an',
            'code' => 'CTY-HOI',
            'region' => 'Miền Trung',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Văn hóa',
            'slug' => 'van-hoa',
        ]);

        Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Chùa Cầu',
            'slug' => 'chua-cau',
            'entrance_fee' => 0,
        ]);

        $this->expectException(ValidationException::class);
        $this->cityService->deleteCity($city->id);
    }

    /**
     * Kiểm thử xóa thành phố thành công khi không có địa điểm du lịch nào.
     */
    public function test_delete_city_succeeds_when_no_destinations_exist(): void
    {
        $city = City::create([
            'name' => 'Bình Thuận',
            'slug' => 'binh-thuan',
            'code' => 'CTY-BTH',
            'region' => 'Miền Trung',
            'is_active' => true,
        ]);

        $result = $this->cityService->deleteCity($city->id);
        $this->assertTrue($result);
        $this->assertDatabaseMissing('cities', ['id' => $city->id]);
    }
}
