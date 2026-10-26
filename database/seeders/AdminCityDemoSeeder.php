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
            [
                'name' => 'Hà Nội',
                'slug' => 'ha-noi',
                'code' => 'CTY-HAN',
                'region' => 'Miền Bắc',
                'description' => 'Thủ đô ngàn năm văn hiến với phố cổ, Hồ Gươm và ẩm thực phong phú.',
                'image' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=300&q=80',
                'is_active' => true,
                'target_destinations_count' => 52,
            ],
            [
                'name' => 'TP. Hồ Chí Minh',
                'slug' => 'tp-ho-chi-minh',
                'code' => 'CTY-SGN',
                'region' => 'Miền Nam',
                'description' => 'Trung tâm kinh tế sầm uất với nhịp sống năng động và đa dạng văn hóa.',
                'image' => 'https://images.unsplash.com/photo-1583417319070-4a69db38a482?auto=format&fit=crop&w=300&q=80',
                'is_active' => true,
                'target_destinations_count' => 60,
            ],
            [
                'name' => 'Lâm Đồng (Đà Lạt)',
                'slug' => 'lam-dong-da-lat',
                'code' => 'CTY-DLT',
                'region' => 'Tây Nguyên',
                'description' => 'Thành phố sương mù với rừng thông, ngàn hoa và khí hậu se lạnh quanh năm.',
                'image' => 'https://images.unsplash.com/photo-1572455044327-7348c1be7267?auto=format&fit=crop&w=300&q=80',
                'is_active' => true,
                'target_destinations_count' => 38,
            ],
            [
                'name' => 'Khánh Hòa (Nha Trang)',
                'slug' => 'khanh-hoa-nha-trang',
                'code' => 'CTY-NTR',
                'region' => 'Miền Trung',
                'description' => 'Vịnh biển Nha Trang tuyệt đẹp với các khu nghỉ dưỡng cao cấp.',
                'image' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=300&q=80',
                'is_active' => true,
                'target_destinations_count' => 31,
            ],
            [
                'name' => 'Thừa Thiên Huế',
                'slug' => 'thua-thien-hue',
                'code' => 'CTY-HUE',
                'region' => 'Miền Trung',
                'description' => 'Cố đô cổ kính với sông Hương, núi Ngự và Đại Nội uy nghiêm.',
                'image' => 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=300&q=80',
                'is_active' => true,
                'target_destinations_count' => 25,
            ],
            [
                'name' => 'Ninh Bình',
                'slug' => 'ninh-binh',
                'code' => 'CTY-NBI',
                'region' => 'Miền Bắc',
                'description' => 'Quần thể danh thắng Tràng An, Tam Cốc Bích Động và cố đô Hoa Lư.',
                'image' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=300&q=80',
                'is_active' => true,
                'target_destinations_count' => 20,
            ],
            [
                'name' => 'Lào Cai (Sa Pa)',
                'slug' => 'lao-cai-sa-pa',
                'code' => 'CTY-SPA',
                'region' => 'Miền Bắc',
                'description' => 'Xứ sở sương mù với đỉnh Fansipan hùng vĩ và ruộng bậc thang kỳ vĩ.',
                'image' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=300&q=80',
                'is_active' => false,
                'target_destinations_count' => 18,
            ],
            [
                'name' => 'Cần Thơ',
                'slug' => 'can-tho',
                'code' => 'CTY-CTH',
                'region' => 'Miền Nam',
                'description' => 'Thủ phủ miền Tây sông nước với chợ nổi Cái Răng và vườn cây trái sum suê.',
                'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=300&q=80',
                'is_active' => true,
                'target_destinations_count' => 15,
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
