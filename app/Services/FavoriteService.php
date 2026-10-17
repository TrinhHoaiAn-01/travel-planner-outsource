<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\Favorite;
use App\Models\User;
use App\Services\Interfaces\FavoriteServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service xử lý logic danh sách địa điểm yêu thích
 */
class FavoriteService implements FavoriteServiceInterface
{
    /**
     * Lấy danh sách địa điểm yêu thích của người dùng với Eager Loading tối ưu truy vấn
     */
    public function getUserFavorites(User $user, int $perPage = 8): LengthAwarePaginator
    {
        return Favorite::where('user_id', $user->id)
            ->with([
                'destination.city',
                'destination.category',
                'destination.primaryImage'
            ])
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Thêm hoặc gỡ địa điểm khỏi danh sách yêu thích (Toggle)
     */
    public function toggleFavorite(User $user, int $destinationId): array
    {
        $destination = Destination::find($destinationId);
        if (!$destination) {
            return [
                'success' => false,
                'is_favorited' => false,
                'message' => 'Địa điểm du lịch không tồn tại trong hệ thống.'
            ];
        }

        $existingFavorite = Favorite::where('user_id', $user->id)
            ->where('destination_id', $destinationId)
            ->first();

        if ($existingFavorite) {
            $existingFavorite->delete();
            return [
                'success' => true,
                'is_favorited' => false,
                'message' => 'Đã xóa địa điểm khỏi danh sách yêu thích.'
            ];
        }

        Favorite::create([
            'user_id' => $user->id,
            'destination_id' => $destinationId,
        ]);

        return [
            'success' => true,
            'is_favorited' => true,
            'message' => 'Đã lưu địa điểm vào danh sách yêu thích thành công!'
        ];
    }

    /**
     * Kiểm tra địa điểm đã được yêu thích chưa
     */
    public function isFavorited(?User $user, int $destinationId): bool
    {
        if (!$user) {
            return false;
        }

        return Favorite::where('user_id', $user->id)
            ->where('destination_id', $destinationId)
            ->exists();
    }

    /**
     * Lấy danh sách mã địa điểm yêu thích của người dùng
     */
    public function getUserFavoriteDestinationIds(?User $user): array
    {
        if (!$user) {
            return [];
        }

        return Favorite::where('user_id', $user->id)
            ->pluck('destination_id')
            ->toArray();
    }

    /**
     * Thêm địa điểm vào chuyến đi cụ thể của người dùng
     */
    public function addDestinationToTrip(User $user, array $data): \App\Models\ItineraryItem
    {
        $trip = \App\Models\Trip::where('id', $data['trip_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $dayNumber = (int) ($data['day_number'] ?? 1);

        // Tự động tính số thứ tự lớn nhất trong ngày để xếp vào cuối ngày
        $maxSortOrder = \App\Models\ItineraryItem::where('trip_id', $trip->id)
            ->where('day_number', $dayNumber)
            ->max('sort_order') ?? 0;

        return \App\Models\ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $data['destination_id'],
            'day_number' => $dayNumber,
            'sort_order' => $maxSortOrder + 1,
            'note' => $data['note'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
        ]);
    }
}
