<?php

namespace Database\Factories;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TripFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+6 months');

        return [
            'user_id' => User::factory(),
            'name' => 'Trip to '.fake()->city(),
            'description' => fake()->sentence(),
            'start_date' => $startDate,
            'end_date' => (clone $startDate)->modify('+'.fake()->numberBetween(2, 7).' days'),
            'budget' => fake()->numberBetween(3, 30) * 1000000,
            'status' => fake()->randomElement([
                Trip::STATUS_DRAFT,
                Trip::STATUS_PLANNED,
                Trip::STATUS_COMPLETED,
            ]),
        ];
    }
}
