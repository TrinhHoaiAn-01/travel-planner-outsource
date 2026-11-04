<?php

namespace Tests\Feature;

use App\Models\{ActivityLog, Booking, Category, City, Destination, Payment, Review, RoomType, Trip, User};
use App\Services\{ActivityLogService, BookingService, CategoryManagementService, CityManagementService,
    CommerceBackendService, DestinationManagementService, DestinationService, PaymentService,
    ReviewModerationService, SolrSearchService, TripService, UserManagementService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WebsiteSearchTest extends TestCase
{
    use RefreshDatabase;

    private function fakeMatches(array $identifiers): void
    {
        Http::preventStrayRequests();
        Http::fake([config('search.solr_url').'/select*' => function ($request) use ($identifiers) {
            return Http::response([
                'response' => ['docs' => array_map(fn ($id) => ['record_id' => $id], $identifiers)],
                'nextCursorMark' => $request['cursorMark'],
            ]);
        }]);
    }

    public function test_homepage_and_destination_search_hydrate_solr_matches(): void
    {
        $destination = Destination::factory()->create(['name' => 'A database title']);
        Destination::factory()->create(['name' => 'Unmatched title']);
        $this->fakeMatches([$destination->id]);

        $this->get('/?keyword=solr-only-match')->assertOk()->assertViewHas('searchResults',
            fn ($results) => $results->modelKeys() === [$destination->id]);
        $this->get('/destinations?search=solr-only-match')->assertOk()->assertViewHas('destinations',
            fn ($results) => $results->getCollection()->modelKeys() === [$destination->id]);
        Http::assertSent(fn ($request) => $request['fq'] === 'entity_type:destination');
    }

    public function test_autocomplete_searches_both_cities_and_destinations(): void
    {
        $city = City::factory()->create();
        $destination = Destination::factory()->create(['city_id' => $city->id]);
        Http::fake([config('search.solr_url').'/select*' => fn ($request) => Http::response([
            'response' => ['docs' => [['record_id' => $request['fq'] === 'entity_type:city' ? $city->id : $destination->id]]],
            'nextCursorMark' => $request['cursorMark'],
        ])]);

        $this->getJson('/quick-search?keyword=solr-only-match')->assertOk()
            ->assertJsonPath('cities.0.id', $city->id)->assertJsonPath('destinations.0.id', $destination->id);
        Http::assertSent(fn ($request) => $request['fq'] === 'entity_type:city');
        Http::assertSent(fn ($request) => $request['fq'] === 'entity_type:destination');
    }

    public function test_trip_search_enforces_ownership_even_if_solr_returns_another_users_trip(): void
    {
        $user = User::factory()->create();
        $own = Trip::factory()->create(['user_id' => $user->id]);
        $other = Trip::factory()->create();
        $this->fakeMatches([$own->id, $other->id]);

        $results = app(TripService::class)->getUserTrips($user->id, ['search' => 'solr-match']);
        $this->assertSame([$own->id], $results->getCollection()->modelKeys());
        Http::assertSent(fn ($request) => $request['fq'] === 'entity_type:trip AND owner_id:'.$user->id);
    }

    public function test_pagination_and_exact_filters_are_preserved_after_search(): void
    {
        $city = City::factory()->create();
        $destinations = Destination::factory()->count(8)->create(['city_id' => $city->id, 'entrance_fee' => 100]);
        $excluded = Destination::factory()->create(['entrance_fee' => 2000000]);
        $this->fakeMatches([...$destinations->modelKeys(), $excluded->id, 999999]);
        $this->get('/destinations?search=solr-match&city_id='.$city->id.'&budget=1&per_page=6&page=2')
            ->assertOk()->assertViewHas('destinations', fn ($results) =>
                $results->total() === 8 && $results->currentPage() === 2 && $results->count() === 2);
    }

    public static function adminSearches(): array
    {
        return [
            'cities' => [CityManagementService::class, 'getCities', 'city'],
            'categories' => [CategoryManagementService::class, 'getCategories', 'category'],
            'destinations' => [DestinationManagementService::class, 'getDestinations', 'destination'],
            'users' => [UserManagementService::class, 'getUsers', 'user'],
            'reviews' => [ReviewModerationService::class, 'getReviews', 'review'],
            'logs' => [ActivityLogService::class, 'getActivityLogs', 'activity_log'],
            'bookings' => [BookingService::class, 'getAllBookings', 'booking'],
            'payments' => [PaymentService::class, 'getAllPayments', 'payment'],
        ];
    }

    #[DataProvider('adminSearches')]
    public function test_admin_search_uses_the_correct_solr_entity(string $service, string $method, string $type): void
    {
        $user = User::factory()->create();
        $destination = Destination::factory()->create();
        $room = RoomType::create(['destination_id' => $destination->id, 'name' => 'Suite', 'capacity' => 2,
            'price_per_night' => 100, 'quantity' => 3]);
        $booking = Booking::create(['user_id' => $user->id, 'room_type_id' => $room->id, 'room_count' => 1,
            'check_in_date' => '2027-01-01', 'check_out_date' => '2027-01-02',
            'total_price' => 100, 'status' => 'pending', 'booking_code' => 'BK-123']);
        $payment = Payment::create(['booking_id' => $booking->id, 'amount' => 100, 'payment_method' => 'cash', 'status' => 'pending']);
        $review = Review::factory()->create(['destination_id' => $destination->id, 'user_id' => $user->id]);
        $log = ActivityLog::create(['user_id' => $user->id, 'action' => 'login', 'ip_address' => '127.0.0.1']);
        $models = ['city' => $destination->city, 'category' => $destination->category, 'destination' => $destination,
            'user' => $user, 'booking' => $booking, 'payment' => $payment, 'review' => $review, 'activity_log' => $log];
        $this->fakeMatches([$models[$type]->id]);
        $results = in_array($type, ['booking', 'payment'], true)
            ? app($service)->$method(null, 'solr-only-match')
            : app($service)->$method(['search' => 'solr-only-match']);

        $this->assertSame([$models[$type]->id], $results->getCollection()->modelKeys());
        Http::assertSent(fn ($request) => $request['fq'] === 'entity_type:'.$type);
    }

    public function test_merchant_property_search_calls_solr_and_returns_canonical_listing_ids(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $destination = Destination::factory()->create();
        $this->fakeMatches([$destination->id]);
        $results = (new CommerceBackendService($admin))->execute('search_listings', ['query' => 'solr-only-match']);
        $this->assertSame('destination:'.$destination->id, $results[0]['listing_id']);
        Http::assertSent(fn ($request) => $request['fq'] === 'entity_type:destination');
    }

    public function test_search_failure_returns_readable_503_without_sql_fallback(): void
    {
        Destination::factory()->create(['name' => 'Solr']);
        Http::fake([config('search.solr_url').'*' => Http::response([], 500)]);
        $this->getJson('/quick-search?keyword=Solr')->assertStatus(503)->assertJsonStructure(['message']);
        $this->get('/destinations?search=Solr')->assertStatus(503)->assertSee('Tìm kiếm hiện chưa sẵn sàng');
    }

    public function test_malformed_or_partial_solr_results_fail_explicitly(): void
    {
        Http::fake([config('search.solr_url').'*' => Http::response([
            'responseHeader' => ['partialResults' => true], 'response' => ['docs' => []], 'nextCursorMark' => '*',
        ])]);
        $this->getJson('/quick-search?keyword=Solr')->assertStatus(503);
    }

    public function test_search_input_validation_rejects_arrays_and_overlong_terms(): void
    {
        Http::fake();
        $this->getJson('/quick-search?keyword[]=bad')->assertUnprocessable()->assertJsonValidationErrors('keyword');
        $this->getJson('/destinations?search='.str_repeat('a', 201))->assertUnprocessable()->assertJsonValidationErrors('search');
        Http::assertNothingSent();
    }

    public function test_blank_and_punctuation_only_searches_do_not_query_solr(): void
    {
        Http::fake();
        $this->getJson('/quick-search?keyword=%20%20')->assertOk()->assertJsonPath('destinations', []);
        $this->assertSame(0, app(DestinationService::class)->getDestinations(['search' => '*:*'])->total());
        Http::assertNothingSent();
    }

    public function test_empty_solr_results_do_not_return_database_matches(): void
    {
        Destination::factory()->create(['name' => 'Solr']);
        $this->fakeMatches([]);
        $this->assertSame(0, app(DestinationService::class)->getDestinations(['search' => 'Solr'])->total());
    }

    public function test_cursor_reads_all_matches_instead_of_truncating_the_first_page(): void
    {
        $first = Destination::factory()->create();
        $second = Destination::factory()->create();
        Http::fakeSequence()->push(['response' => ['docs' => [['record_id' => $first->id]]], 'nextCursorMark' => 'next'])
            ->push(['response' => ['docs' => [['record_id' => $second->id]]], 'nextCursorMark' => 'next']);
        $this->assertSame(2, app(DestinationService::class)->getDestinations(['search' => 'test'])->total());
        Http::assertSentCount(2);
    }

    public function test_indexing_whitelists_search_fields_and_scopes_trip_documents(): void
    {
        Http::fake(['*' => Http::response(['responseHeader' => ['status' => 0]])]);
        $user = User::factory()->create(['name' => 'Search User', 'password' => 'secret-value']);
        $trip = Trip::factory()->create(['user_id' => $user->id]);
        $search = app(SolrSearchService::class);
        $search->index($user);
        $search->index($trip);
        Http::assertSent(function ($request) use ($user) {
            $document = $request->data()[0];
            return $document['id'] === 'user:'.$user->id &&
                ! str_contains(json_encode($document), $user->password) && ! array_key_exists('password', $document);
        });
        Http::assertSent(fn ($request) => ($request->data()[0]['owner_id'] ?? null) === $user->id);
        $search->remove($trip);
        Http::assertSent(fn ($request) => ($request->data()['delete']['id'] ?? null) === 'trip:'.$trip->id);
    }
}
