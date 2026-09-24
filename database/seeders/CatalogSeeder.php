<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'Ha Noi', 'slug' => 'ha-noi', 'description' => 'Thu do nghin nam van hien voi nhieu di san, ho nuoc va pho co.', 'image' => 'https://picsum.photos/seed/ha-noi/1200/800'],
            ['name' => 'Da Nang', 'slug' => 'da-nang', 'description' => 'Thanh pho bien hien dai, noi tieng voi cau dep va ban dao Son Tra.', 'image' => 'https://picsum.photos/seed/da-nang/1200/800'],
            ['name' => 'Hoi An', 'slug' => 'hoi-an', 'description' => 'Do thi co yen binh voi kien truc truyen thong va nhung dem den long.', 'image' => 'https://picsum.photos/seed/hoi-an/1200/800'],
            ['name' => 'Ho Chi Minh City', 'slug' => 'ho-chi-minh-city', 'description' => 'Do thi soi dong voi am thuc, mua sam va nhieu cong trinh lich su.', 'image' => 'https://picsum.photos/seed/ho-chi-minh-city/1200/800'],
            ['name' => 'Da Lat', 'slug' => 'da-lat', 'description' => 'Thanh pho cao nguyen mat me, noi tieng voi hoa, thong va ho.', 'image' => 'https://picsum.photos/seed/da-lat/1200/800'],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(['slug' => $city['slug']], $city);
        }

        $categories = [
            ['name' => 'Nature', 'slug' => 'nature', 'description' => 'Beaches, mountains, lakes and outdoor attractions.'],
            ['name' => 'Culture & History', 'slug' => 'culture-history', 'description' => 'Museums, monuments, temples and heritage sites.'],
            ['name' => 'Food & Drink', 'slug' => 'food-drink', 'description' => 'Local markets, restaurants and culinary experiences.'],
            ['name' => 'Entertainment', 'slug' => 'entertainment', 'description' => 'Theme parks, shows and family activities.'],
            ['name' => 'Shopping', 'slug' => 'shopping', 'description' => 'Markets, malls and local craft stores.'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }

        $destinations = [
            [
                'city' => 'ha-noi', 'category' => 'culture-history', 'name' => 'Hoan Kiem Lake', 'slug' => 'hoan-kiem-lake',
                'description' => 'A historic lake in central Ha Noi, ideal for a walk around the Old Quarter.',
                'address' => 'Hoan Kiem District, Ha Noi', 'price' => 0, 'opening_time' => '00:00', 'closing_time' => '23:59',
                'duration' => 90, 'rating' => 4.7, 'is_featured' => true,
            ],
            [
                'city' => 'ha-noi', 'category' => 'culture-history', 'name' => 'Temple of Literature', 'slug' => 'temple-of-literature',
                'description' => 'Viet Nam\'s first national university and a landmark of traditional architecture.',
                'address' => '58 Quoc Tu Giam, Dong Da, Ha Noi', 'price' => 70000, 'opening_time' => '08:00', 'closing_time' => '17:00',
                'duration' => 120, 'rating' => 4.6, 'is_featured' => true,
            ],
            [
                'city' => 'da-nang', 'category' => 'nature', 'name' => 'My Khe Beach', 'slug' => 'my-khe-beach',
                'description' => 'A long sandy beach close to the city centre with sunrise views.',
                'address' => 'Vo Nguyen Giap Street, Da Nang', 'price' => 0, 'opening_time' => '00:00', 'closing_time' => '23:59',
                'duration' => 180, 'rating' => 4.7, 'is_featured' => true,
            ],
            [
                'city' => 'da-nang', 'category' => 'entertainment', 'name' => 'Ba Na Hills', 'slug' => 'ba-na-hills',
                'description' => 'A mountain resort with cable cars, gardens and the Golden Bridge.',
                'address' => 'Hoa Vang District, Da Nang', 'price' => 900000, 'opening_time' => '08:00', 'closing_time' => '22:00',
                'duration' => 480, 'rating' => 4.5, 'is_featured' => true,
            ],
            [
                'city' => 'hoi-an', 'category' => 'culture-history', 'name' => 'Hoi An Ancient Town', 'slug' => 'hoi-an-ancient-town',
                'description' => 'A UNESCO-listed trading port known for preserved houses and lantern-lit streets.',
                'address' => 'Minh An Ward, Hoi An', 'price' => 120000, 'opening_time' => '07:00', 'closing_time' => '21:30',
                'duration' => 240, 'rating' => 4.9, 'is_featured' => true,
            ],
            [
                'city' => 'hoi-an', 'category' => 'food-drink', 'name' => 'Hoi An Central Market', 'slug' => 'hoi-an-central-market',
                'description' => 'A riverside market for local produce, street food and regional specialties.',
                'address' => 'Tran Quy Cap Street, Hoi An', 'price' => 0, 'opening_time' => '06:00', 'closing_time' => '20:00',
                'duration' => 90, 'rating' => 4.4, 'is_featured' => false,
            ],
            [
                'city' => 'ho-chi-minh-city', 'category' => 'culture-history', 'name' => 'Independence Palace', 'slug' => 'independence-palace',
                'description' => 'A major historical landmark with preserved state rooms and exhibits.',
                'address' => '135 Nam Ky Khoi Nghia, District 1, Ho Chi Minh City', 'price' => 40000, 'opening_time' => '08:00', 'closing_time' => '15:30',
                'duration' => 120, 'rating' => 4.6, 'is_featured' => true,
            ],
            [
                'city' => 'ho-chi-minh-city', 'category' => 'shopping', 'name' => 'Ben Thanh Market', 'slug' => 'ben-thanh-market',
                'description' => 'A city-centre market offering food, souvenirs, textiles and local products.',
                'address' => 'Le Loi, District 1, Ho Chi Minh City', 'price' => 0, 'opening_time' => '06:00', 'closing_time' => '22:00',
                'duration' => 120, 'rating' => 4.2, 'is_featured' => false,
            ],
            [
                'city' => 'da-lat', 'category' => 'nature', 'name' => 'Xuan Huong Lake', 'slug' => 'xuan-huong-lake',
                'description' => 'A scenic lake in central Da Lat surrounded by gardens and pine trees.',
                'address' => 'Ward 1, Da Lat', 'price' => 0, 'opening_time' => '00:00', 'closing_time' => '23:59',
                'duration' => 120, 'rating' => 4.6, 'is_featured' => true,
            ],
            [
                'city' => 'da-lat', 'category' => 'nature', 'name' => 'Datanla Waterfall', 'slug' => 'datanla-waterfall',
                'description' => 'A forest waterfall with walking trails and an alpine coaster.',
                'address' => 'Prenn Pass, Ward 3, Da Lat', 'price' => 50000, 'opening_time' => '07:00', 'closing_time' => '17:00',
                'duration' => 180, 'rating' => 4.5, 'is_featured' => false,
            ],
        ];

        foreach ($destinations as $data) {
            $city = City::where('slug', $data['city'])->firstOrFail();
            $category = Category::where('slug', $data['category'])->firstOrFail();
            unset($data['city'], $data['category']);

            $destination = Destination::updateOrCreate(
                ['slug' => $data['slug']],
                [...$data, 'city_id' => $city->id, 'category_id' => $category->id],
            );

            foreach ([1, 2, 3] as $position) {
                $destination->images()->updateOrCreate(
                    ['image_path' => "https://picsum.photos/seed/{$destination->slug}-{$position}/1200/800"],
                    ['is_primary' => $position === 1],
                );
            }
        }
    }
}
