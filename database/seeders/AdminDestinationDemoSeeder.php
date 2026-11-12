<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class AdminDestinationDemoSeeder extends Seeder
{
    /**
     * Khởi tạo 4 địa điểm du lịch mẫu chính xác 100% theo ảnh thiết kế giao diện Admin Destinations.
     */
    public function run(): void
    {
        // 1. Chuẩn bị các thành phố khớp với ảnh
        $citiesData = [
            'Đà Nẵng' => ['pattern' => 'Đà Nẵng', 'name' => 'Đà Nẵng', 'slug' => 'da-nang', 'code' => 'CTY-DAD', 'region' => 'Miền Trung'],
            'Quảng Ninh' => ['pattern' => 'Quảng Ninh', 'name' => 'Quảng Ninh', 'slug' => 'quang-ninh', 'code' => 'CTY-QNH', 'region' => 'Miền Bắc'],
            'Phú Quốc' => ['pattern' => 'Phú Quốc', 'name' => 'Phú Quốc', 'slug' => 'phu-quoc', 'code' => 'CTY-PQC', 'region' => 'Miền Nam'],
            'Hội An' => ['pattern' => 'Hội An', 'name' => 'Hội An', 'slug' => 'hoi-an', 'code' => 'CTY-HOI', 'region' => 'Miền Trung'],
        ];

        $cities = [];
        foreach ($citiesData as $key => $cityData) {
            $city = City::where('name', 'like', "%{$cityData['pattern']}%")
                ->orWhere('slug', $cityData['slug'])
                ->orWhere('code', $cityData['code'])
                ->first();

            if (! $city) {
                $code = $cityData['code'];
                if (City::where('code', $code)->exists()) {
                    $code = 'CTY-' . strtoupper(substr(md5($cityData['name']), 0, 3));
                }

                $city = City::create([
                    'name' => $cityData['name'],
                    'slug' => $cityData['slug'],
                    'code' => $code,
                    'region' => $cityData['region'],
                    'is_active' => true,
                ]);
            }
            $cities[$key] = $city;
        }

        // 2. Chuẩn bị các danh mục hiển thị khớp với ảnh: Nghỉ dưỡng, Biển đảo, Di sản
        $categoriesData = [
            'Nghỉ dưỡng' => ['pattern' => 'Nghỉ dưỡng', 'name' => 'Nghỉ dưỡng', 'slug' => 'nghi-duong', 'description' => 'Resort, khách sạn và khu nghỉ dưỡng cao cấp.'],
            'Biển đảo' => ['pattern' => 'Biển', 'name' => 'Biển đảo', 'slug' => 'bien-dao', 'description' => 'Tour du thuyền, bãi biển và các hòn đảo đẹp.'],
            'Di sản' => ['pattern' => 'Di sản', 'name' => 'Di sản', 'slug' => 'di-san', 'description' => 'Phố cổ, di tích lịch sử và di sản văn hóa thế giới.'],
        ];

        $categories = [];
        foreach ($categoriesData as $key => $catData) {
            $category = Category::where('name', 'like', "%{$catData['pattern']}%")
                ->orWhere('slug', $catData['slug'])
                ->first();

            if (! $category) {
                $category = Category::create([
                    'name' => $catData['name'],
                    'slug' => $catData['slug'],
                    'description' => $catData['description'],
                    'is_active' => true,
                ]);
            }
            $categories[$key] = $category;
        }

        // 3. Khởi tạo 4 địa điểm chính xác theo ảnh
        $destinations = [
            [
                'id' => 1,
                'name' => 'Sun World Bà Nà Hills',
                'slug' => 'sun-world-ba-na-hills',
                'city_name' => 'Đà Nẵng',
                'category_name' => 'Nghỉ dưỡng',
                'rating' => 4.9,
                'entrance_fee' => 900000,
                'opening_time' => '08:00',
                'closing_time' => '22:00',
                'address' => 'Thôn An Sơn, Xã Hòa Ninh, Huyện Hòa Vang, Đà Nẵng',
                'description' => 'Quần thể du lịch nghỉ dưỡng kết hợp vui chơi giải trí đẳng cấp hàng đầu Việt Nam với Cầu Vàng nổi tiếng.',
                'image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=600&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=600&q=80',
                ],
                'has_hotel' => true,
                'hotel_name' => 'Mercure Danang French Village Bana Hills',
            ],
            [
                'id' => 2,
                'name' => 'Du Thuyền Vịnh Hạ Long',
                'slug' => 'du-thuyen-vinh-ha-long',
                'city_name' => 'Quảng Ninh',
                'category_name' => 'Biển đảo',
                'rating' => 4.8,
                'entrance_fee' => 1500000,
                'opening_time' => '07:30',
                'closing_time' => '20:00',
                'address' => 'Cảng tàu khách quốc tế Tuần Châu, Hạ Long, Quảng Ninh',
                'description' => 'Trải nghiệm hải trình du thuyền 5 sao đẳng cấp chiêm ngưỡng kỳ quan thiên nhiên thế giới Vịnh Hạ Long.',
                'image' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=400&q=80',
                'has_hotel' => true,
                'hotel_name' => 'Cabin Du Thuyền Ambassador Cruise',
            ],
            [
                'id' => 3,
                'name' => 'Vinpearl Resort & Spa Phú Quốc',
                'slug' => 'vinpearl-resort-spa-phu-quoc',
                'city_name' => 'Phú Quốc',
                'category_name' => 'Nghỉ dưỡng',
                'rating' => 5.0,
                'entrance_fee' => 2800000,
                'opening_time' => '00:00',
                'closing_time' => '23:59',
                'address' => 'Bãi Dài, Xã Gành Dầu, Thành phố Phú Quốc, Kiên Giang',
                'description' => 'Khu nghỉ dưỡng 5 sao sang trọng bên bờ Bãi Dài với bể bơi ngoài trời rộng lớn và dịch vụ chuẩn quốc tế.',
                'image' => 'https://images.unsplash.com/photo-1589308078059-be1415eab4c3?auto=format&fit=crop&w=400&q=80',
                'has_hotel' => true,
                'hotel_name' => 'Phòng Deluxe King Hướng Biển',
            ],
            [
                'id' => 4,
                'name' => 'Phố Cổ Hội An',
                'slug' => 'pho-co-hoi-an',
                'city_name' => 'Hội An',
                'category_name' => 'Di sản',
                'rating' => 4.9,
                'entrance_fee' => 150000,
                'opening_time' => '07:00',
                'closing_time' => '22:00',
                'address' => 'Phường Minh An, Thành phố Hội An, Tỉnh Quảng Nam',
                'description' => 'Di sản văn hóa thế giới UNESCO với những ngôi nhà cổ mái ngói rêu phong, cầu Chùa và những đêm hoa đăng lung linh.',
                'image' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=400&q=80',
                'has_hotel' => false,
                'hotel_name' => null,
            ],
            [
                'id' => 5,
                'name' => 'Bán đảo Sơn Trà & Chùa Linh Ứng',
                'slug' => 'ban-dao-son-tra-chua-linh-ung',
                'city_name' => 'Đà Nẵng',
                'category_name' => 'Nghỉ dưỡng',
                'rating' => 4.7,
                'entrance_fee' => 0,
                'opening_time' => '06:00',
                'closing_time' => '19:00',
                'address' => 'Bán đảo Sơn Trà, Quận Sơn Trà, Thành phố Đà Nẵng',
                'description' => 'Lá phổi xanh của Đà Nẵng với tượng Phật Bà Quan Âm cao nhất Việt Nam và cảnh quan thiên nhiên tráng lệ.',
                'image' => 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=400&q=80',
                'has_hotel' => false,
                'hotel_name' => null,
            ],
            [
                'id' => 6,
                'name' => 'Đảo Tuần Châu & Công Viên Nước',
                'slug' => 'dao-tuan-chau-cong-vien-nuoc',
                'city_name' => 'Quảng Ninh',
                'category_name' => 'Biển đảo',
                'rating' => 4.6,
                'entrance_fee' => 350000,
                'opening_time' => '08:00',
                'closing_time' => '21:00',
                'address' => 'Đảo Tuần Châu, Thành phố Hạ Long, Tỉnh Quảng Ninh',
                'description' => 'Khu du lịch giải trí quốc tế với bãi biển nhân tạo cát trắng trải dài và biểu diễn cá heo đặc sắc.',
                'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=400&q=80',
                'has_hotel' => true,
                'hotel_name' => 'Tuần Châu Resort Hạ Long',
            ],
            [
                'id' => 7,
                'name' => 'Bãi Sao Phú Quốc',
                'slug' => 'bai-sao-phu-quoc',
                'city_name' => 'Phú Quốc',
                'category_name' => 'Biển đảo',
                'rating' => 4.8,
                'entrance_fee' => 0,
                'opening_time' => '06:00',
                'closing_time' => '18:30',
                'address' => 'Ấp Bãi Sao, Phường An Thới, Thành phố Phú Quốc, Kiên Giang',
                'description' => 'Một trong những bãi biển đẹp nhất Phú Quốc với bờ cát trắng mịn như kem và làn nước ngọc bích êm đềm.',
                'image' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80',
                'has_hotel' => false,
                'hotel_name' => null,
            ],
            [
                'id' => 8,
                'name' => 'Rừng Dừa Bảy Mẫu Cẩm Thanh',
                'slug' => 'rung-dua-bay-mau-cam-thanh',
                'city_name' => 'Hội An',
                'category_name' => 'Di sản',
                'rating' => 4.7,
                'entrance_fee' => 150000,
                'opening_time' => '07:30',
                'closing_time' => '17:30',
                'address' => 'Thôn Vạn Lăng, Xã Cẩm Thanh, Thành phố Hội An, Quảng Nam',
                'description' => 'Trải nghiệm du thuyền thúng len lỏi giữa rừng dừa nước bạt ngàn và xem múa thúng nghệ thuật đặc sắc.',
                'image' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=400&q=80',
                'has_hotel' => false,
                'hotel_name' => null,
            ],
            [
                'id' => 9,
                'name' => 'Danh Thắng Ngũ Hành Sơn',
                'slug' => 'danh-thang-ngu-hanh-son',
                'city_name' => 'Đà Nẵng',
                'category_name' => 'Di sản',
                'rating' => 4.5,
                'entrance_fee' => 40000,
                'opening_time' => '07:00',
                'closing_time' => '17:30',
                'address' => '81 Huyền Trân Công Chúa, Hòa Hải, Ngũ Hành Sơn, Đà Nẵng',
                'description' => 'Quần thể 5 ngọn núi đá vôi mang tên ngũ hành với các hang động huyền bí và chùa chiền cổ kính.',
                'image' => 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=400&q=80',
                'has_hotel' => false,
                'hotel_name' => null,
            ],
            [
                'id' => 10,
                'name' => 'Hang Sửng Sốt Vịnh Hạ Long',
                'slug' => 'hang-sung-sot-vinh-ha-long',
                'city_name' => 'Quảng Ninh',
                'category_name' => 'Biển đảo',
                'rating' => 4.9,
                'entrance_fee' => 250000,
                'opening_time' => '08:00',
                'closing_time' => '17:00',
                'address' => 'Đảo Bồ Hòn, Vịnh Hạ Long, Tỉnh Quảng Ninh',
                'description' => 'Hang động rộng và đẹp bậc nhất vịnh Hạ Long với hàng ngàn nhũ đá thạch nhũ lung linh muôn hình vạn trạng.',
                'image' => 'https://images.unsplash.com/photo-1512100356356-de1b84283e18?auto=format&fit=crop&w=400&q=80',
                'has_hotel' => false,
                'hotel_name' => null,
            ],
            [
                'id' => 11,
                'name' => 'VinWonders & Safari Phú Quốc',
                'slug' => 'vinwonders-safari-phu-quoc',
                'city_name' => 'Phú Quốc',
                'category_name' => 'Nghỉ dưỡng',
                'rating' => 4.9,
                'entrance_fee' => 950000,
                'opening_time' => '09:00',
                'closing_time' => '20:00',
                'address' => 'Khu Bãi Dài, Xã Gành Dầu, Thành phố Phú Quốc, Kiên Giang',
                'description' => 'Công viên chủ đề lớn nhất Việt Nam và vườn thú bán hoang dã đầu tiên mở cửa cho khách khám phá.',
                'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=400&q=80',
                'has_hotel' => true,
                'hotel_name' => 'VinHolidays Fiesta Phú Quốc',
            ],
            [
                'id' => 12,
                'name' => 'Chùa Cầu & Làng Gốm Thanh Hà',
                'slug' => 'chua-cau-lang-gom-thanh-ha',
                'city_name' => 'Hội An',
                'category_name' => 'Di sản',
                'rating' => 4.6,
                'entrance_fee' => 50000,
                'opening_time' => '08:00',
                'closing_time' => '18:00',
                'address' => 'Đường Duy Tân, Phường Thanh Hà, Thành phố Hội An, Quảng Nam',
                'description' => 'Biểu tượng lịch sử văn hóa phố cổ cùng làng nghề gốm truyền thống hơn 500 năm tuổi.',
                'image' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=400&q=80',
                'has_hotel' => false,
                'hotel_name' => null,
            ],
        ];

        foreach ($destinations as $destData) {
            $city = $cities[$destData['city_name']];
            $category = $categories[$destData['category_name']];

            $destination = Destination::updateOrCreate(
                ['slug' => $destData['slug']],
                [
                    'city_id' => $city->id,
                    'category_id' => $category->id,
                    'name' => $destData['name'],
                    'slug' => $destData['slug'],
                    'description' => $destData['description'],
                    'address' => $destData['address'],
                    'entrance_fee' => $destData['entrance_fee'],
                    'opening_time' => $destData['opening_time'],
                    'closing_time' => $destData['closing_time'],
                    'rating' => $destData['rating'],
                    'is_featured' => true,
                ]
            );

            // Cập nhật thư viện hình ảnh
            if (! empty($destData['gallery_images'])) {
                $destination->images()->delete();
                foreach ($destData['gallery_images'] as $index => $imgPath) {
                    $destination->images()->create([
                        'image_path' => $imgPath,
                        'is_primary' => $index === 0,
                    ]);
                }
            } else {
                $destination->images()->updateOrCreate(
                    ['is_primary' => true],
                    ['image_path' => $destData['image']]
                );
            }

            // Cập nhật phòng khách sạn lưu trú
            if ($destData['has_hotel']) {
                $destination->roomTypes()->updateOrCreate(
                    ['name' => $destData['hotel_name']],
                    [
                        'capacity' => 2,
                        'price_per_night' => $destData['entrance_fee'],
                        'quantity' => 15,
                        'description' => 'Phòng nghỉ tiện nghi tại ' . $destData['name'],
                    ]
                );
            }
        }
    }
}
