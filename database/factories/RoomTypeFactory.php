<?php

namespace Database\Factories;

use App\Models\Destination;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomType>
 */
class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    /**
     * Định nghĩa trạng thái mặc định của RoomType.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roomNames = [
            'Phòng Deluxe King Hướng Núi',
            'Phòng Superior Twin Hướng Biển',
            'Phòng Suite Gia Đình Cao Cấp',
            'Phòng Tiêu Chuẩn Giường Đôi',
            'Bungalow Biệt Lập Ven Hồ',
            'Phòng Executive Hạng Sang',
        ];

        return [
            'destination_id' => Destination::factory(),
            'name' => fake()->randomElement($roomNames),
            'capacity' => fake()->numberBetween(2, 4),
            'price_per_night' => fake()->numberBetween(800000, 3500000),
            'quantity' => fake()->numberBetween(5, 20),
            'description' => fake()->paragraph(2),
        ];
    }
}
