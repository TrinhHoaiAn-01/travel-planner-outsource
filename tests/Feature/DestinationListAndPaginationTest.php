<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationListAndPaginationTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Concerns\FakesWebsiteSearch;

    /**
     * Kiểm thử hiển thị danh sách địa điểm và phân trang mặc định (6 địa điểm / trang).
     */
    public function test_destinations_index_paginates_records_correctly(): void
    {
        $city = City::factory()->create(['name' => 'Đà Nẵng']);
        $category = Category::factory()->create(['name' => 'Tham quan']);

        // Tạo 10 địa điểm
        Destination::factory()->count(10)->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('destinations.index'));

        $response->assertStatus(200);
        $response->assertSee('Hiển thị <strong>1–6</strong> trong tổng số <strong>10</strong> địa điểm', false);
        $response->assertSee('Trang <strong>1</strong> / <strong>2</strong>', false);
        $response->assertSee('Tiếp &rsaquo;', false);
    }

    /**
     * Kiểm thử chuyển sang trang 2 hiển thị các bản ghi tiếp theo và thông tin trang 2.
     */
    public function test_destinations_index_page_two_displays_next_records(): void
    {
        $city = City::factory()->create(['name' => 'Hà Nội']);
        $category = Category::factory()->create(['name' => 'Văn hóa']);

        Destination::factory()->count(10)->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('destinations.index', ['page' => 2]));

        $response->assertStatus(200);
        $response->assertSee('Hiển thị <strong>7–10</strong> trong tổng số <strong>10</strong> địa điểm', false);
        $response->assertSee('Trang <strong>2</strong> / <strong>2</strong>', false);
        $response->assertSee('&lsaquo; Trước', false);
    }

    /**
     * Kiểm thử tham số tùy chọn số lượng hiển thị trên trang (per_page).
     */
    public function test_destinations_supports_custom_per_page(): void
    {
        $city = City::factory()->create(['name' => 'Nha Trang']);
        $category = Category::factory()->create(['name' => 'Biển đảo']);

        Destination::factory()->count(15)->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
        ]);

        // Chọn hiển thị 9 địa điểm mỗi trang
        $response = $this->get(route('destinations.index', ['per_page' => 9]));

        $response->assertStatus(200);
        $response->assertSee('Hiển thị <strong>1–9</strong> trong tổng số <strong>15</strong> địa điểm', false);
        $response->assertSee('Trang <strong>1</strong> / <strong>2</strong>', false);
    }

    /**
     * Kiểm thử phân trang duy trì đầy đủ query string (tìm kiếm, lọc, sắp xếp, per_page).
     */
    public function test_destinations_pagination_preserves_query_string(): void
    {
        $city = City::factory()->create(['name' => 'Huế']);
        $category = Category::factory()->create(['name' => 'Lịch sử']);

        // Tạo 8 địa điểm phù hợp từ khóa "Đại Nội"
        for ($i = 1; $i <= 8; $i++) {
            Destination::factory()->create([
                'city_id' => $city->id,
                'category_id' => $category->id,
                'name' => "Khu di tích Đại Nội $i",
                'slug' => "dai-noi-$i",
            ]);
        }

        $this->fakeWebsiteSearch(['destination' => Destination::pluck('id')->all()]);
        $response = $this->get(route('destinations.index', [
            'search' => 'Đại Nội',
            'per_page' => 6,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Hiển thị <strong>1–6</strong> trong tổng số <strong>8</strong> địa điểm', false);
        // Link sang trang 2 phải chứa query string search và per_page
        $response->assertSee('page=2');
        $response->assertSee('per_page=6');
    }

    /**
     * Kiểm thử route lọc địa điểm theo thành phố: GET /cities/{city}/destinations
     */
    public function test_destinations_by_city_slug_route(): void
    {
        $cityHN = City::factory()->create(['name' => 'Hà Nội', 'slug' => 'ha-noi']);
        $cityDN = City::factory()->create(['name' => 'Đà Nẵng', 'slug' => 'da-nang']);
        $category = Category::factory()->create(['name' => 'Ẩm thực']);

        $destHN = Destination::factory()->create([
            'city_id' => $cityHN->id,
            'category_id' => $category->id,
            'name' => 'Phố Cổ Hà Nội',
            'slug' => 'pho-co-ha-noi',
        ]);

        $destDN = Destination::factory()->create([
            'city_id' => $cityDN->id,
            'category_id' => $category->id,
            'name' => 'Cầu Rồng Đà Nẵng',
            'slug' => 'cau-rong-da-nang',
        ]);

        $response = $this->get(route('cities.destinations', ['city' => 'ha-noi']));

        $response->assertStatus(200);
        $response->assertSee('Phố Cổ Hà Nội');
        $response->assertDontSee('Cầu Rồng Đà Nẵng');
    }

    /**
     * Kiểm thử route lọc địa điểm theo danh mục: GET /categories/{category}/destinations
     */
    public function test_destinations_by_category_slug_route(): void
    {
        $city = City::factory()->create(['name' => 'Quảng Nam', 'slug' => 'quang-nam']);
        $catBien = Category::factory()->create(['name' => 'Biển An Bàng', 'slug' => 'bien-dao']);
        $catDiSan = Category::factory()->create(['name' => 'Di sản thế giới', 'slug' => 'di-san']);

        $destBien = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $catBien->id,
            'name' => 'Bãi biển An Bàng',
            'slug' => 'bai-bien-an-bang',
        ]);

        $destDiSan = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $catDiSan->id,
            'name' => 'Phố cổ Hội An',
            'slug' => 'pho-co-hoi-an',
        ]);

        $response = $this->get(route('categories.destinations', ['category' => 'bien-dao']));

        $response->assertStatus(200);
        $response->assertSee('Bãi biển An Bàng');
        $response->assertDontSee('Phố cổ Hội An');
    }

    /**
     * Kiểm thử tìm kiếm địa điểm hỗ trợ bộ chọn Solr / SQL và hiển thị tiêu đề kết quả theo engine.
     */
    public function test_destinations_search_supports_solr_and_sql_engine_selection(): void
    {
        $city = City::factory()->create(['name' => 'Đà Nẵng']);
        $category = Category::factory()->create(['name' => 'Danh lam']);

        $dest1 = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Bà Nà Hills Sun World',
            'slug' => 'ba-na-hills',
        ]);

        Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Cù Lao Chàm',
            'slug' => 'cu-lao-cham',
        ]);

        $this->fakeWebsiteSearch(['destination' => [$dest1->id]]);

        // 1. Kiểm thử tìm kiếm với engine Solr (mặc định)
        $solrResponse = $this->get(route('destinations.index', [
            'search' => 'Bà Nà',
            'engine' => 'solr',
        ]));

        $solrResponse->assertStatus(200);
        $solrResponse->assertSee('Bà Nà Hills Sun World');
        $solrResponse->assertDontSee('Cù Lao Chàm');
        $solrResponse->assertSee('KẾT QUẢ TÌM KIẾM (SOLR)', false);
        $solrResponse->assertSee('name="engine"', false);
        $solrResponse->assertSee('value="solr"', false);
        $solrResponse->assertSee('value="sql"', false);
        $solrResponse->assertSee('✕ Xóa tìm kiếm', false);

        // 2. Kiểm thử tìm kiếm với engine SQL
        $sqlResponse = $this->get(route('destinations.index', [
            'search' => 'Bà Nà',
            'engine' => 'sql',
        ]));

        $sqlResponse->assertStatus(200);
        $sqlResponse->assertSee('Bà Nà Hills Sun World');
        $sqlResponse->assertDontSee('Cù Lao Chàm');
        $sqlResponse->assertSee('KẾT QUẢ TÌM KIẾM (SQL)', false);
    }

    /**
     * Kiểm thử tìm kiếm duy trì engine đã chọn trong liên kết phân trang.
     */
    public function test_destinations_search_preserves_engine_in_pagination(): void
    {
        $city = City::factory()->create(['name' => 'Hải Phòng']);
        $category = Category::factory()->create(['name' => 'Vịnh đảo']);

        for ($i = 1; $i <= 8; $i++) {
            Destination::factory()->create([
                'city_id' => $city->id,
                'category_id' => $category->id,
                'name' => "Vịnh Lan Hạ Đảo $i",
                'slug' => "vinh-lan-ha-$i",
            ]);
        }

        $response = $this->get(route('destinations.index', [
            'search' => 'Lan Hạ',
            'engine' => 'sql',
            'per_page' => 6,
        ]));

        $response->assertStatus(200);
        $response->assertSee('engine=sql');
        $response->assertSee('page=2');
    }
}
