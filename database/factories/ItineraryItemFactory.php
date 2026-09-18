<?php

namespace Database\Factories;

use App\Models\Destination;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItineraryItemFactory extends Factory
{
    public function definition(): array
    {
        $hour = fake()->numberBetween(7, 18);

        return [
            'trip_id' => Trip::factory(),
            'destination_id' => Destination::factory(),
            'day_number' => fake()->numberBetween(1, 5),
            'start_time' => sprintf('%02d:00', $hour),
            'end_time' => sprintf('%02d:00', min($hour + 2, 23)),
            'note' => fake()->optional()->sentence(),
            'sort_order' => fake()->numberBetween(0, 5),
        ];
    }
}
