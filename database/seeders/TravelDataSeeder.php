<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Review;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;

class TravelDataSeeder extends Seeder
{
    public function run(): void
    {
        $demoUser = User::where('email', 'user@travelplanner.test')->firstOrFail();
        $reviewers = User::where('role', 'user')->get();
        $destinations = Destination::all()->keyBy('slug');

        foreach (['hoi-an-ancient-town', 'my-khe-beach', 'xuan-huong-lake', 'hoan-kiem-lake'] as $slug) {
            $demoUser->favorites()->firstOrCreate(['destination_id' => $destinations[$slug]->id]);
        }

        $reviewData = [
            ['email' => 'user@travelplanner.test', 'destination' => 'hoi-an-ancient-town', 'rating' => 5, 'comment' => 'Beautiful old streets and a wonderful atmosphere after sunset.', 'status' => Review::STATUS_APPROVED],
            ['email' => 'han@travelplanner.test', 'destination' => 'my-khe-beach', 'rating' => 5, 'comment' => 'Clean beach and a great place to see the sunrise.', 'status' => Review::STATUS_APPROVED],
            ['email' => 'bao@travelplanner.test', 'destination' => 'ba-na-hills', 'rating' => 4, 'comment' => 'The cable car views were excellent, but it was busy.', 'status' => Review::STATUS_APPROVED],
            ['email' => 'linh@travelplanner.test', 'destination' => 'temple-of-literature', 'rating' => 5, 'comment' => 'A peaceful and meaningful historical site.', 'status' => Review::STATUS_PENDING],
            ['email' => 'kiet@travelplanner.test', 'destination' => 'ben-thanh-market', 'rating' => 4, 'comment' => 'Many food choices and souvenirs in one place.', 'status' => Review::STATUS_APPROVED],
        ];

        foreach ($reviewData as $review) {
            $user = $reviewers->firstWhere('email', $review['email']);
            $destination = $destinations[$review['destination']];

            Review::updateOrCreate(
                ['user_id' => $user->id, 'destination_id' => $destination->id],
                ['rating' => $review['rating'], 'comment' => $review['comment'], 'status' => $review['status']],
            );
        }

        $startDate = now()->addDays(30)->startOfDay();
        $trip = Trip::updateOrCreate(
            ['user_id' => $demoUser->id, 'name' => 'Da Nang & Hoi An Getaway'],
            [
                'description' => 'A sample three-day central Viet Nam itinerary.',
                'start_date' => $startDate,
                'end_date' => $startDate->copy()->addDays(2),
                'budget' => 8000000,
                'status' => Trip::STATUS_PLANNED,
            ],
        );

        $items = [
            ['destination' => 'my-khe-beach', 'day_number' => 1, 'start_time' => '06:00', 'end_time' => '08:00', 'sort_order' => 1, 'note' => 'Watch the sunrise.'],
            ['destination' => 'ba-na-hills', 'day_number' => 1, 'start_time' => '09:30', 'end_time' => '17:00', 'sort_order' => 2, 'note' => 'Buy cable car tickets in advance.'],
            ['destination' => 'hoi-an-central-market', 'day_number' => 2, 'start_time' => '08:00', 'end_time' => '10:00', 'sort_order' => 1, 'note' => 'Try cao lau for breakfast.'],
            ['destination' => 'hoi-an-ancient-town', 'day_number' => 2, 'start_time' => '15:00', 'end_time' => '21:00', 'sort_order' => 2, 'note' => 'Stay until the lanterns are lit.'],
        ];

        foreach ($items as $item) {
            $destination = $destinations[$item['destination']];
            unset($item['destination']);

            $trip->itineraryItems()->updateOrCreate(
                ['destination_id' => $destination->id, 'day_number' => $item['day_number']],
                $item,
            );
        }

        $expenses = [
            ['category' => 'transport', 'description' => 'Round-trip flight', 'amount' => 2400000, 'expense_date' => $startDate],
            ['category' => 'accommodation', 'description' => 'Hotel for two nights', 'amount' => 1800000, 'expense_date' => $startDate],
            ['category' => 'ticket', 'description' => 'Ba Na Hills ticket', 'amount' => 900000, 'expense_date' => $startDate],
            ['category' => 'food', 'description' => 'Food budget', 'amount' => 1200000, 'expense_date' => $startDate],
        ];

        foreach ($expenses as $expense) {
            $trip->expenses()->updateOrCreate(['description' => $expense['description']], $expense);
        }
    }
}
