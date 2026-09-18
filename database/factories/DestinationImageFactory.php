<?php

namespace Database\Factories;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Factories\Factory;

class DestinationImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'destination_id' => Destination::factory(),
            'image_path' => 'https://picsum.photos/seed/'.fake()->unique()->word().'/1200/800',
            'is_primary' => false,
        ];
    }
}
