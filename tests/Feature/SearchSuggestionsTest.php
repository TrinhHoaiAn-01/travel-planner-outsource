<?php

namespace Tests\Feature;

use App\Models\{ActivityLog, Booking, Destination, Payment, RoomType, Trip, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesWebsiteSearch;
use Tests\TestCase;

class SearchSuggestionsTest extends TestCase
{
    use RefreshDatabase, FakesWebsiteSearch;

    public function test_public_suggestions_are_limited_and_preserve_destination_filters(): void
    {
        $destinations = Destination::factory()->count(8)->create(['entrance_fee' => 100]);
        $excluded = Destination::factory()->create(['entrance_fee' => 2000000]);
        $this->fakeWebsiteSearch(['destination' => [...$destinations->modelKeys(), $excluded->id, 999999]]);
        $response = $this->getJson('/search/suggestions/destination?keyword=solr&budget=1')->assertOk()
            ->assertJsonCount(6, 'suggestions')->assertJsonStructure(['suggestions' => [['label', 'description', 'value']]])
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->assertNotContains($excluded->name, array_column($response->json('suggestions'), 'label'));
        $this->assertNotContains('999999', array_column($response->json('suggestions'), 'label'));
    }

    public function test_suggestions_are_hydrated_as_plain_text(): void
    {
        $destination = Destination::factory()->create(['name' => '<b>Landmark</b>']);
        $this->fakeWebsiteSearch(['destination' => [$destination->id]]);
        $this->getJson('/search/suggestions/destination?keyword=land')->assertOk()
            ->assertJsonPath('suggestions.0.label', 'Landmark')->assertJsonPath('suggestions.0.value', 'Landmark');
    }

    public function test_trip_suggestions_require_login_and_enforce_ownership(): void
    {
        $user = User::factory()->create();
        $own = Trip::factory()->create(['user_id' => $user->id]);
        $other = Trip::factory()->create();
        $this->fakeWebsiteSearch(['trip' => [$own->id, $other->id]]);
        $this->getJson('/search/suggestions/trip?keyword=trip')->assertUnauthorized();
        Http::assertNothingSent();
        $this->actingAs($user)->getJson('/search/suggestions/trip?keyword=trip&user_id='.$other->user_id)->assertOk()
            ->assertJsonCount(1, 'suggestions')->assertJsonPath('suggestions.0.value', $own->name);
        Http::assertSent(fn ($request) => $request['fq'] === 'entity_type:trip AND owner_id:'.$user->id);
    }

    public static function privateTypes(): array
    {
        return array_map(fn ($type) => [$type], ['user', 'booking', 'payment', 'activity_log', 'review', 'property']);
    }

    #[DataProvider('privateTypes')]
    public function test_admin_suggestions_reject_guests_and_regular_users(string $type): void
    {
        Http::fake();
        $this->getJson('/search/suggestions/'.$type.'?keyword=secret')->assertUnauthorized();
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->getJson('/search/suggestions/'.$type.'?keyword=secret')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_inactive_admin_cannot_request_private_suggestions(): void
    {
        Http::fake();
        $user = User::factory()->create(['role' => 'admin', 'is_active' => false]);
        $this->actingAs($user)->getJson('/search/suggestions/user?keyword=name')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_property_suggestions_follow_the_merchant_catalog_rules(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $destination = Destination::factory()->create(['name' => 'Real property']);
        $demo = Destination::factory()->create(['name' => 'Demo #12']);
        $this->fakeWebsiteSearch(['destination' => [$destination->id, $demo->id]]);
        $this->actingAs($admin)->getJson('/search/suggestions/property?keyword=test')->assertOk()
            ->assertJsonCount(1, 'suggestions')->assertJsonPath('suggestions.0.value', 'Real property');
    }

    public function test_admin_user_suggestions_respect_soft_delete_filter_and_exclude_secrets(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['name' => 'Deleted Guest']);
        $user->delete();
        $this->fakeWebsiteSearch(['user' => [$user->id]]);
        $this->actingAs($admin)->getJson('/search/suggestions/user?keyword=guest')->assertOk()->assertJsonCount(0, 'suggestions');
        $response = $this->getJson('/search/suggestions/user?keyword=guest&status=trashed')->assertOk()
            ->assertJsonPath('suggestions.0.value', 'Deleted Guest');
        $this->assertSame(['label', 'description', 'value'], array_keys($response->json('suggestions.0')));
        $this->assertStringNotContainsString($user->password, $response->getContent());
    }

    public function test_booking_payment_and_activity_suggestions_use_searchable_labels(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $destination = Destination::factory()->create();
        $room = RoomType::create(['destination_id' => $destination->id, 'name' => 'Suite', 'capacity' => 2,
            'price_per_night' => 100, 'quantity' => 3]);
        $booking = Booking::create(['user_id' => $admin->id, 'room_type_id' => $room->id, 'room_count' => 1,
            'check_in_date' => '2027-01-01', 'check_out_date' => '2027-01-02',
            'total_price' => 100, 'status' => 'pending', 'booking_code' => 'BK-SUGGEST']);
        $payment = Payment::create(['booking_id' => $booking->id, 'amount' => 100, 'payment_method' => 'cash', 'status' => 'pending']);
        $log = ActivityLog::create(['user_id' => $admin->id, 'action' => 'login', 'ip_address' => '127.0.0.1']);
        $this->fakeWebsiteSearch(['booking' => [$booking->id], 'payment' => [$payment->id], 'activity_log' => [$log->id]]);
        $this->actingAs($admin)->getJson('/search/suggestions/booking?keyword=test')->assertOk()->assertJsonPath('suggestions.0.value', 'BK-SUGGEST');
        $this->getJson('/search/suggestions/payment?keyword=test')->assertOk()->assertJsonPath('suggestions.0.value', 'BK-SUGGEST');
        $this->getJson('/search/suggestions/activity_log?keyword=test')->assertOk()->assertJsonPath('suggestions.0.value', 'login');
    }

    public function test_blank_and_empty_matches_return_an_empty_list(): void
    {
        Http::fake();
        $this->getJson('/search/suggestions/destination?keyword=%20')->assertOk()->assertExactJson(['suggestions' => []]);
        Http::assertNothingSent();
        $this->fakeWebsiteSearch([]);
        $this->getJson('/search/suggestions/destination?keyword=unknown')->assertOk()->assertExactJson(['suggestions' => []]);
    }

    public function test_invalid_keywords_filters_and_unknown_types_are_rejected(): void
    {
        Http::fake();
        $this->getJson('/search/suggestions/destination?keyword[]=bad')->assertUnprocessable();
        $this->getJson('/search/suggestions/destination?keyword='.str_repeat('a', 201))->assertUnprocessable();
        $this->getJson('/search/suggestions/destination?keyword=test&city_id[]=1')->assertUnprocessable();
        $this->getJson('/search/suggestions/password?keyword=test')->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_solr_failure_returns_503_for_suggestions(): void
    {
        Http::fake(['*' => Http::response([], 500)]);
        $this->getJson('/search/suggestions/destination?keyword=test')->assertStatus(503)->assertJsonStructure(['message']);
    }
}
