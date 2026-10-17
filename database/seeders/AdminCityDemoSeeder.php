<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminCityDemoSeeder extends Seeder
{
    /**
     * Khởi tạo 4 tỉnh/thành phố mẫu chính xác theo ảnh thiết kế giao diện Admin Cities.
     */
    public function run(): void
    {
        // Đảm bảo có ít nhất một danh mục để liên kết địa điểm
        $category = Category::first();
        if (! $category) {
            $category = Category::create([
                'name' => 'Du lịch & Khám phá',
                'slug' => 'du-lich-kham-pha',
                'description' => 'Danh mục điểm đến nổi bật',
            ]);
        }

        $cities = [
            [
                'name' => 'Đà Nẵng',
                'slug' => 'da-nang',
                'code' => 'CTY-DAD',
                'region' => 'Miền Trung',
                'description' => 'Thành phố đáng sống với biển Mỹ Khê, Bà Nà Hills và Cầu Rồng.',
                'image' => 'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?auto=format&fit=crop&w=300&q=80',
                'is_active' => true,
                'target_destinations_count' => 48,
            ],
            [
                'name' => 'Quảng Ninh (Hạ Long)',
                'slug' => 'quang-ninh-ha-long',
                'code' => 'CTY-QNH',
                'region' => null, // Trong ảnh cột Khu vực của Quảng Ninh để trống
                'description' => 'Vịnh Hạ Long - kỳ quan thiên nhiên thế giới với hàng ngàn đảo đá.',
                'image' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=300&q=80',
                'is_active' => true,
                'target_destinations_count' => 35,
            ],
            [
                'name' => 'Phú Quốc (Kiên Giang)',
                'slug' => 'phu-quoc-kien-giang',
                'code' => 'CTY-PQC',
                'region' => 'Miền Nam',
                'description' => 'Đảo Ngọc thiên đường với bãi biển cát trắng, resort cao cấp và hoàng hôn tuyệt đẹp.',
                'image' => 'https://images.unsplash.com/photo-1589308078059-be1415eab4c3?auto=format&fit=crop&w=300&q=80',
                'is_active' => true,
                'target_destinations_count' => 42,
            ],
            [
                'name' => 'Quảng Nam (Hội An)',
                'slug' => 'quang-nam-hoi-an',
                'code' => 'CTY-QNM',
                'region' => 'Miền Trung',
                'description' => 'Phố cổ Hội An trầm mặc, lung linh đèn lồng và đậm đà bản sắc di sản văn hóa.',
                'image' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=300&q=80',
                'is_active' => true,
                'target_destinations_count' => 29,
            ],
        ];

        foreach ($cities as $cityData) {
            $targetCount = $cityData['target_destinations_count'];
            unset($cityData['target_destinations_count']);

            $city = City::updateOrCreate(
                ['slug' => $cityData['slug']],
                $cityData
            );

            // Bổ sung các điểm đến mẫu để tổng số điểm đến khớp chính xác với ảnh
            $currentCount = $city->destinations()->count();
            if ($currentCount < $targetCount) {
                $needed = $targetCount - $currentCount;
                for ($i = 1; $i <= $needed; $i++) {
                    $index = $currentCount + $i;
                    $destName = "Điểm tham quan {$city->name} #{$index}";
                    $destSlug = Str::slug("{$city->slug}-destination-{$index}");

                    Destination::firstOrCreate(
                        ['slug' => $destSlug],
                        [
                            'city_id' => $city->id,
                            'category_id' => $category->id,
                            'name' => $destName,
                            'slug' => $destSlug,
                            'description' => "Địa điểm du lịch nổi tiếng tại {$city->name}",
                            'address' => "Địa chỉ tại {$city->name}",
                            'entrance_fee' => 0,
                            'rating' => 4.5,
                            'is_featured' => false,
                        ]
                    );
                }
            }
        }
    }
}
