<?php

namespace Database\Factories;

use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'category' => fake()->randomElement(['transport', 'food', 'accommodation', 'ticket', 'shopping', 'other']),
            'description' => fake()->sentence(4),
            'amount' => fake()->numberBetween(1, 30) * 50000,
            'expense_date' => fake()->optional()->date(),
        ];
    }
}
