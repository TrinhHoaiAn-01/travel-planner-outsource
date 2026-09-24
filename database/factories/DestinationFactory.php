<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DestinationFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company().' '.fake()->randomElement(['Park', 'Museum', 'Beach', 'Market']);

        return [
            'city_id' => City::factory(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->paragraphs(2, true),
            'address' => fake()->address(),
            'price' => fake()->randomElement([0, 50000, 100000, 200000, 350000]),
            'opening_time' => '08:00',
            'closing_time' => '18:00',
            'duration' => fake()->randomElement([60, 90, 120, 180]),
            'rating' => fake()->randomFloat(1, 3, 5),
            'is_featured' => fake()->boolean(25),
        ];
    }
}
