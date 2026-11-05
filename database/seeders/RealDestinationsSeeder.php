<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RealDestinationsSeeder extends Seeder
{
    public function run(): void
    {
        $hanoiCity = City::firstOrCreate(['slug' => 'ha-noi'], ['name' => 'Hà Nội', 'code' => 'CTY-HAN', 'region' => 'Miền Bắc', 'is_active' => true]);
        $saigonCity = City::whereIn('slug', ['tp-ho-chi-minh', 'ho-chi-minh-city'])->first() ?: City::firstOrCreate(['slug' => 'tp-ho-chi-minh'], ['name' => 'TP. Hồ Chí Minh', 'code' => 'CTY-SGN', 'region' => 'Miền Nam', 'is_active' => true]);
        $hueCity = City::firstOrCreate(['slug' => 'thua-thien-hue'], ['name' => 'Thừa Thiên Huế', 'code' => 'CTY-HUE', 'region' => 'Miền Trung', 'is_active' => true]);
        $halongCity = City::whereIn('slug', ['quang-ninh-ha-long', 'quang-ninh'])->first() ?: City::firstOrCreate(['slug' => 'quang-ninh-ha-long'], ['name' => 'Quảng Ninh (Hạ Long)', 'code' => 'CTY-QNH', 'region' => 'Miền Bắc', 'is_active' => true]);
        $danangCity = City::firstOrCreate(['slug' => 'da-nang'], ['name' => 'Đà Nẵng', 'code' => 'CTY-DAD', 'region' => 'Miền Trung', 'is_active' => true]);

        $cultureCategory = Category::whereIn('slug', ['culture-history', 'di-san-van-hoa'])->first() ?: Category::firstOrCreate(['slug' => 'culture-history'], ['name' => 'Culture & History']);
        $resortCategory = Category::whereIn('slug', ['resort-spa', 'nghi-duong'])->first() ?: Category::firstOrCreate(['slug' => 'resort-spa'], ['name' => 'Nghỉ dưỡng & Resort']);

        $realDestinations = [
            [
                'city_id' => $hanoiCity->id,
                'category_id' => $cultureCategory->id,
                'name' => 'Hoàng Thành Thăng Long',
                'slug' => 'hoang-thanh-thang-long',
                'description' => 'Di sản văn hóa thế giới với quần thể kiến trúc đồ sộ ghi dấu nghìn năm lịch sử thủ đô.',
                'address' => '19C Hoàng Diệu, Quán Thánh, Ba Đình, Hà Nội',
                'entrance_fee' => 30000,
                'rating' => 4.8,
                'is_featured' => true,
                'is_lodging' => false,
            ],
            [
                'city_id' => $hanoiCity->id,
                'category_id' => $cultureCategory->id,
                'name' => 'Chùa Trấn Quốc & Hồ Tây',
                'slug' => 'chua-tran-quoc-ho-tay',
                'description' => 'Ngôi chùa cổ nhất Thăng Long nằm trên hòn đảo xinh đẹp phía đông Hồ Tây với phong cảnh thanh tịnh.',
                'address' => 'Đường Thanh Niên, Yên Phụ, Tây Hồ, Hà Nội',
                'entrance_fee' => 0,
                'rating' => 4.7,
                'is_featured' => true,
                'is_lodging' => false,
            ],
            [
                'city_id' => $hanoiCity->id,
                'category_id' => $cultureCategory->id,
                'name' => 'Lăng Chủ tịch Hồ Chí Minh',
                'slug' => 'lang-chu-tich-ho-chi-minh',
                'description' => 'Quảng trường Ba Đình lịch sử và nơi an nghỉ của Chủ tịch Hồ Chí Minh, điểm viếng thăm trang trọng của thủ đô.',
                'address' => '2 Hùng Vương, Điện Biên, Ba Đình, Hà Nội',
                'entrance_fee' => 0,
                'rating' => 4.9,
                'is_featured' => true,
                'is_lodging' => false,
            ],
            [
                'city_id' => $hanoiCity->id,
                'category_id' => $resortCategory->id,
                'name' => 'Khách sạn Sofitel Legend Metropole Hà Nội',
                'slug' => 'sofitel-legend-metropole-hanoi',
                'description' => 'Khách sạn di sản 5 sao sang trọng bậc nhất Hà Nội nằm gần Hồ Gươm và Nhà Hát Lớn.',
                'address' => '15 Ngô Quyền, Hoàn Kiếm, Hà Nội',
                'entrance_fee' => 0,
                'rating' => 4.9,
                'is_featured' => true,
                'is_lodging' => true,
                'rooms' => [
                    [
                        'name' => 'Phòng Luxury Grand Metropole',
                        'price_per_night' => 3500000,
                        'capacity' => 2,
                        'quantity' => 15,
                        'description' => 'Phòng nghỉ 5 sao kiểu Pháp cổ điển, view trung tâm Hoàn Kiếm, buffet sáng cao cấp.',
                    ]
                ]
            ],
            [
                'city_id' => $hanoiCity->id,
                'category_id' => $resortCategory->id,
                'name' => 'Khách sạn Meliá Hà Nội',
                'slug' => 'melia-hanoi-hotel',
                'description' => 'Khách sạn 5 sao quốc tế ngay trung tâm quận Hoàn Kiếm, tiện nghi hiện đại.',
                'address' => '44 Lý Thường Kiệt, Trần Hưng Đạo, Hoàn Kiếm, Hà Nội',
                'entrance_fee' => 0,
                'rating' => 4.7,
                'is_featured' => true,
                'is_lodging' => true,
                'rooms' => [
                    [
                        'name' => 'Phòng Deluxe City View',
                        'price_per_night' => 2100000,
                        'capacity' => 2,
                        'quantity' => 20,
                        'description' => 'Phòng tiện nghi 5 sao, hồ bơi ngoài trời, dịch vụ spa chuẩn quốc tế.',
                    ]
                ]
            ],
            [
                'city_id' => $saigonCity->id,
                'category_id' => $resortCategory->id,
                'name' => 'Khách sạn Caravelle Sài Gòn',
                'slug' => 'caravelle-saigon-hotel',
                'description' => 'Khách sạn sang trọng nổi tiếng nhìn ra Nhà hát Thành phố tại trung tâm Quận 1.',
                'address' => '19-23 Lam Sơn Square, Bến Nghé, Quận 1, TP. Hồ Chí Minh',
                'entrance_fee' => 0,
                'rating' => 4.8,
                'is_featured' => true,
                'is_lodging' => true,
                'rooms' => [
                    [
                        'name' => 'Phòng Opera View Deluxe',
                        'price_per_night' => 2400000,
                        'capacity' => 2,
                        'quantity' => 18,
                        'description' => 'View trọn vẹn Nhà hát Thành phố, tiện nghi cao cấp, buffet sáng sang trọng.',
                    ]
                ]
            ],
            [
                'city_id' => $hueCity->id,
                'category_id' => $resortCategory->id,
                'name' => 'Khách sạn Sài Gòn Morin Huế',
                'slug' => 'saigon-morin-hotel-hue',
                'description' => 'Khách sạn lịch sử 4 sao ven bờ sông Hương và cầu Trường Tiền thơ mộng.',
                'address' => '30 Lê Lợi, Phú Nhuận, Thành phố Huế',
                'entrance_fee' => 0,
                'rating' => 4.6,
                'is_featured' => true,
                'is_lodging' => true,
                'rooms' => [
                    [
                        'name' => 'Phòng Premium Sông Hương View',
                        'price_per_night' => 1400000,
                        'capacity' => 2,
                        'quantity' => 25,
                        'description' => 'Tầm nhìn trực diện cầu Trường Tiền và sông Hương lãng mạn.',
                    ]
                ]
            ],
            [
                'city_id' => $halongCity->id,
                'category_id' => $resortCategory->id,
                'name' => 'Vinpearl Resort & Spa Hạ Long',
                'slug' => 'vinpearl-resort-spa-ha-long',
                'description' => 'Resort 5 sao độc đáo nằm hoàn toàn trên mặt biển đảo Đảo Rều tại Vịnh Hạ Long.',
                'address' => 'Đảo Rều, Bãi Cháy, Thành phố Hạ Long, Quảng Ninh',
                'entrance_fee' => 0,
                'rating' => 4.9,
                'is_featured' => true,
                'is_lodging' => true,
                'rooms' => [
                    [
                        'name' => 'Phòng Deluxe Hướng Biển Vịnh Hạ Long',
                        'price_per_night' => 2500000,
                        'capacity' => 2,
                        'quantity' => 30,
                        'description' => 'Tầm nhìn bao quát kỳ quan Vịnh Hạ Long, bãi tắm riêng, hồ bơi vô cực.',
                    ]
                ]
            ],
        ];

        foreach ($realDestinations as $data) {
            $rooms = $data['rooms'] ?? [];
            unset($data['rooms']);

            $dest = Destination::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            foreach ($rooms as $room) {
                RoomType::updateOrCreate(
                    ['destination_id' => $dest->id, 'name' => $room['name']],
                    $room
                );
            }
        }
    }
}

