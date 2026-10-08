<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Khách chưa đăng nhập không thể xem trang danh sách yêu thích
     */
    public function test_guest_cannot_access_favorites_page(): void
    {
        $response = $this->get(route('favorites.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng đã đăng nhập có thể xem danh sách địa điểm yêu thích của mình
     */
    public function test_authenticated_user_can_view_favorites(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        $category = Category::factory()->create();
        $destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Bà Nà Hills',
        ]);

        Favorite::create([
            'user_id' => $user->id,
            'destination_id' => $destination->id,
        ]);

        $response = $this->actingAs($user)->get(route('favorites.index'));

        $response->assertStatus(200);
        $response->assertSee('Bà Nà Hills');
    }

    /**
     * Người dùng có thể thả tim (toggle) thêm và xóa địa điểm khỏi danh sách yêu thích
     */
    public function test_user_can_toggle_favorite_destination(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        $category = Category::factory()->create();
        $destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Phố cổ Hội An',
        ]);

        // Thao tác 1: Thêm vào yêu thích
        $responseAdd = $this->actingAs($user)->post(route('favorites.toggle', $destination->id));
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'destination_id' => $destination->id,
        ]);

        // Thao tác 2: Gỡ khỏi yêu thích
        $responseRemove = $this->actingAs($user)->post(route('favorites.toggle', $destination->id));
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'destination_id' => $destination->id,
        ]);
    }

    /**
     * Người dùng có thể thêm địa điểm yêu thích vào chuyến đi của mình
     */
    public function test_user_can_add_favorite_destination_to_trip(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        $category = Category::factory()->create();
        $destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
        ]);

        $trip = \App\Models\Trip::factory()->create([
            'user_id' => $user->id,
            'name' => 'Chuyến đi Đà Nẵng 3N2Đ',
        ]);

        $postData = [
            'trip_id' => $trip->id,
            'destination_id' => $destination->id,
            'day_number' => 1,
            'note' => 'Tham quan buổi sáng, chụp ảnh check-in',
        ];

        $response = $this->actingAs($user)->post(route('favorites.add-to-trip'), $postData);

        $response->assertRedirect();
        $this->assertDatabaseHas('itinerary_items', [
            'trip_id' => $trip->id,
            'destination_id' => $destination->id,
            'day_number' => 1,
            'sort_order' => 1,
            'note' => 'Tham quan buổi sáng, chụp ảnh check-in',
        ]);
    }

    /**
     * Người dùng không thể thêm địa điểm vào chuyến đi của người khác
     */
    public function test_user_cannot_add_destination_to_other_users_trip(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $city = City::factory()->create();
        $category = Category::factory()->create();
        $destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
        ]);

        $otherTrip = \App\Models\Trip::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $postData = [
            'trip_id' => $otherTrip->id,
            'destination_id' => $destination->id,
            'day_number' => 1,
        ];

        $response = $this->actingAs($user)->post(route('favorites.add-to-trip'), $postData);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('itinerary_items', [
            'trip_id' => $otherTrip->id,
            'destination_id' => $destination->id,
        ]);
    }
}
