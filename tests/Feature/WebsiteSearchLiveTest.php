<?php

namespace Tests\Feature;

use App\Models\{ActivityLog, Booking, City, Destination, Payment, Review, RoomType, Trip, User};
use App\Services\{DestinationService, SolrSearchService, TripService};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('solr-integration')]
class WebsiteSearchLiveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $url = getenv('SOLR_INTEGRATION_URL');
        if (! $url || ! str_ends_with($url, '/website_search_test')) {
            $this->markTestSkipped('Set SOLR_INTEGRATION_URL to the dedicated website_search_test core.');
        }
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->artisan('migrate:fresh')->assertSuccessful();
        config(['search.solr_url' => $url, 'search.sync_in_tests' => true, 'search.sync' => true]);
        Http::post($url.'/update?commit=true', ['delete' => ['query' => '*:*']])->throw();
    }

    protected function tearDown(): void
    {
        $url = getenv('SOLR_INTEGRATION_URL');
        if ($url && str_ends_with($url, '/website_search_test')) {
            Http::post($url.'/update?commit=true', ['delete' => ['query' => '*:*']])->throw();
        }
        parent::tearDown();
    }

    public function test_real_solr_partial_vietnamese_autocomplete_and_related_record_updates(): void
    {
        $city = City::factory()->create(['name' => 'Đà Nẵng']);
        $destination = Destination::factory()->create(['city_id' => $city->id, 'name' => 'Bà Nà Hills', 'description' => 'Mountain landmark']);
        $search = app(DestinationService::class);
        $this->assertSame([$destination->id], $search->searchDestinations(['keyword' => 'ba na'])->modelKeys());
        $this->assertSame([$destination->id], $search->searchDestinations(['keyword' => 'ill'])->modelKeys());
        $this->assertSame($city->id, $search->quickSearch('da nang')['cities'][0]['id']);
        $city->update(['name' => 'RenamedUniqueCity']);
        $this->assertSame([$destination->id], $search->searchDestinations(['keyword' => 'RenamedUniqueCity'])->modelKeys());
        $this->assertCount(0, $search->searchDestinations(['keyword' => 'da nang']));
        $destination->delete();
        $this->assertCount(0, $search->searchDestinations(['keyword' => 'ba na']));
        $response = Http::get(config('search.solr_url').'/select', ['q' => 'id:destination\\:'.$destination->id, 'wt' => 'json']);
        $this->assertSame(0, $response->json('response.numFound'));
    }

    public function test_real_solr_trip_scope_and_rebuild(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $user->id, 'name' => 'PrivateSolrTrip']);
        Trip::factory()->create(['name' => 'PrivateSolrTrip']);
        $results = app(TripService::class)->getUserTrips($user->id, ['search' => 'PrivateSolrTrip']);
        $this->assertSame([$trip->id], $results->getCollection()->modelKeys());
        $this->assertGreaterThanOrEqual(4, app(SolrSearchService::class)->rebuild());
        $this->assertSame([$trip->id], app(TripService::class)->getUserTrips($user->id, ['search' => 'PrivateSolrTrip'])->getCollection()->modelKeys());
    }

    public function test_related_admin_records_follow_user_and_booking_changes(): void
    {
        $user = User::factory()->create(['name' => 'OriginalUniqueGuest', 'email' => 'guest@example.test']);
        $destination = Destination::factory()->create();
        $room = RoomType::create(['destination_id' => $destination->id, 'name' => 'Suite', 'capacity' => 2,
            'price_per_night' => 100, 'quantity' => 3]);
        $booking = Booking::create(['user_id' => $user->id, 'room_type_id' => $room->id, 'room_count' => 1,
            'check_in_date' => '2027-01-01', 'check_out_date' => '2027-01-02',
            'total_price' => 100, 'status' => 'pending', 'booking_code' => 'BK-OriginalUnique']);
        $payment = Payment::create(['booking_id' => $booking->id, 'amount' => 100, 'payment_method' => 'cash', 'status' => 'pending']);
        $review = Review::factory()->create(['destination_id' => $destination->id, 'user_id' => $user->id]);
        $log = ActivityLog::create(['user_id' => $user->id, 'action' => 'login']);
        $user->update(['name' => 'RenamedUniqueGuest']);
        $search = app(SolrSearchService::class);
        foreach (['booking' => Booking::class, 'payment' => Payment::class, 'review' => Review::class, 'activity_log' => ActivityLog::class] as $type => $modelClass) {
            $this->assertSame(1, $search->apply($modelClass::query(), $type, 'RenamedUniqueGuest')->count());
            $this->assertSame(0, $search->apply($modelClass::query(), $type, 'OriginalUniqueGuest')->count());
        }
        $booking->update(['booking_code' => 'BK-RenamedUnique']);
        $this->assertSame([$payment->id], $search->apply(Payment::query(), 'payment', 'RenamedUnique')->pluck('id')->all());
        $user->delete();
        $this->assertSame([$user->id], $search->apply(User::onlyTrashed(), 'user', 'RenamedUniqueGuest')->pluck('id')->all());
        $this->assertSame(0, $search->apply(Booking::query(), 'booking', 'RenamedUniqueGuest')->count());
        $user->restore();
        $this->assertSame(1, $search->apply(Booking::query(), 'booking', 'RenamedUniqueGuest')->count());
    }

    public function test_rolled_back_records_are_not_indexed(): void
    {
        try {
            DB::transaction(function () {
                City::factory()->create(['name' => 'RolledBackUniqueCity']);
                throw new \RuntimeException('Rollback test');
            });
        } catch (\RuntimeException $exception) {
            $this->assertSame('Rollback test', $exception->getMessage());
        }
        $response = Http::get(config('search.solr_url').'/select', [
            'q' => 'search_text:RolledBackUniqueCity', 'wt' => 'json',
        ])->throw();
        $this->assertSame(0, $response->json('response.numFound'));
    }
}
