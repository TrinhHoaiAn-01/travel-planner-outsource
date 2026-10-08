<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminCategoryDemoSeeder extends Seeder
{
    /**
     * Khởi tạo 4 danh mục du lịch mẫu chính xác 100% theo ảnh thiết kế giao diện Admin Categories.
     */
    public function run(): void
    {
        // Đảm bảo có ít nhất một thành phố để liên kết địa điểm
        $city = City::first();
        if (! $city) {
            $city = City::create([
                'name' => 'Đà Nẵng',
                'slug' => 'da-nang',
                'code' => 'CTY-DAD',
                'region' => 'Miền Trung',
                'is_active' => true,
            ]);
        }

        $categories = [
            [
                'name' => 'Nghỉ dưỡng & Resort',
                'slug' => 'resort-spa',
                'icon' => 'hotel',
                'description' => 'Trải nghiệm kỳ nghỉ đẳng cấp tại các resort, spa cao cấp ven biển và trung tâm.',
                'is_active' => true,
                'target_destinations_count' => 65,
            ],
            [
                'name' => 'Biển & Đảo',
                'slug' => 'bien-dao',
                'icon' => 'waves',
                'description' => 'Khám phá những bãi biển cát trắng nắng vàng, làn nước xanh trong và đảo hoang sơ tuyệt đẹp.',
                'is_active' => true,
                'target_destinations_count' => 142,
            ],
            [
                'name' => 'Núi Rừng & Trekking',
                'slug' => 'nui-rung',
                'icon' => 'tree',
                'description' => 'Chinh phục những đỉnh núi hùng vĩ, cung đường trekking thử thách và thiên nhiên kỳ thú.',
                'is_active' => true,
                'target_destinations_count' => 89,
            ],
            [
                'name' => 'Di Sản Văn Hóa',
                'slug' => 'di-san-van-hoa',
                'icon' => 'landmark',
                'description' => 'Tìm hiểu lịch sử ngàn năm, đền đài cổ kính, phố cổ trầm mặc và di sản thế giới.',
                'is_active' => true,
                'target_destinations_count' => 110,
            ],
        ];

        foreach ($categories as $catData) {
            $targetCount = $catData['target_destinations_count'];
            unset($catData['target_destinations_count']);

            $category = Category::updateOrCreate(
                ['slug' => $catData['slug']],
                $catData
            );

            // Bổ sung các địa điểm mẫu để tổng số địa điểm khớp chính xác với ảnh thiết kế
            $currentCount = $category->destinations()->count();
            if ($currentCount < $targetCount) {
                $needed = $targetCount - $currentCount;
                for ($i = 1; $i <= $needed; $i++) {
                    $index = $currentCount + $i;
                    $destName = "Địa điểm {$category->name} #{$index}";
                    $destSlug = Str::slug("{$category->slug}-diem-den-{$index}");

                    Destination::firstOrCreate(
                        ['slug' => $destSlug],
                        [
                            'city_id' => $city->id,
                            'category_id' => $category->id,
                            'name' => $destName,
                            'slug' => $destSlug,
                            'description' => "Điểm du lịch thuộc danh mục {$category->name}",
                            'address' => "Địa chỉ tại {$city->name}",
                            'entrance_fee' => 0,
                            'rating' => 4.8,
                            'is_featured' => false,
                        ]
                    );
                }
            }
        }
    }
}
