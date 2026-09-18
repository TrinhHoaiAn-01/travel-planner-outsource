<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TravelPlannerDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_can_be_migrated_and_seeded_with_expected_relationships(): void
    {
        $this->seed();

        $this->assertTrue(Schema::hasColumns('users', ['role', 'avatar', 'is_active']));
        $this->assertTrue(Schema::hasColumns('destinations', [
            'city_id',
            'category_id',
            'price',
            'rating',
            'is_featured',
        ]));

        $this->assertDatabaseCount('cities', 5);
        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('destinations', 10);
        $this->assertDatabaseCount('destination_images', 30);

        $admin = User::where('email', 'admin@travelplanner.test')->firstOrFail();
        $this->assertTrue($admin->isAdmin());

        $destination = Destination::where('slug', 'hoi-an-ancient-town')
            ->with(['city', 'category', 'primaryImage', 'approvedReviews'])
            ->firstOrFail();

        $this->assertNotNull($destination->city);
        $this->assertNotNull($destination->category);
        $this->assertNotNull($destination->primaryImage);
        $this->assertCount(1, $destination->approvedReviews);

        $trip = Trip::with(['user', 'itineraryItems.destination', 'expenses'])->firstOrFail();
        $this->assertSame('user@travelplanner.test', $trip->user->email);
        $this->assertCount(4, $trip->itineraryItems);
        $this->assertEquals(6300000, $trip->expenses->sum('amount'));
    }
}
