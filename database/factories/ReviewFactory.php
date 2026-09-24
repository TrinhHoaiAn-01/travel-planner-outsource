<?php

namespace Database\Factories;

use App\Models\Destination;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'destination_id' => Destination::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->paragraph(),
            'status' => fake()->randomElement([
                Review::STATUS_PENDING,
                Review::STATUS_APPROVED,
                Review::STATUS_APPROVED,
                Review::STATUS_HIDDEN,
            ]),
        ];
    }
}
