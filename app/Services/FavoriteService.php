<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\Favorite;
use App\Models\ItineraryItem;
use App\Models\Trip;
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
    public function getUserFavorites(User $user, int $perPage = 9): LengthAwarePaginator
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
                'message' => 'Đã gỡ địa điểm khỏi danh sách yêu thích.'
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
     * Thêm địa điểm vào lịch trình chuyến đi của người dùng (FR15)
     */
    public function addDestinationToTrip(User $user, int $tripId, int $destinationId, int $dayNumber = 1, ?string $note = null): array
    {
        $trip = Trip::where('id', $tripId)->where('user_id', $user->id)->first();
        if (!$trip) {
            return [
                'success' => false,
                'message' => 'Chuyến đi không tồn tại hoặc bạn không có quyền thao tác trên chuyến đi này.'
            ];
        }

        $destination = Destination::find($destinationId);
        if (!$destination) {
            return [
                'success' => false,
                'message' => 'Địa điểm du lịch không tồn tại trong hệ thống.'
            ];
        }

        // Đảm bảo số ngày hợp lệ
        $dayNumber = max(1, $dayNumber);
        if ($trip->start_date && $trip->end_date) {
            $totalDays = (int) $trip->start_date->diffInDays($trip->end_date) + 1;
            if ($totalDays > 0 && $dayNumber > $totalDays) {
                $dayNumber = $totalDays;
            }
        }

        // Lấy thứ tự hiển thị tiếp theo trong ngày
        $maxSortOrder = ItineraryItem::where('trip_id', $trip->id)
            ->where('day_number', $dayNumber)
            ->max('sort_order') ?? 0;

        $itineraryItem = ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $destination->id,
            'day_number' => $dayNumber,
            'note' => $note,
            'sort_order' => $maxSortOrder + 1,
        ]);

        return [
            'success' => true,
            'message' => "Đã thêm \"{$destination->name}\" vào ngày {$dayNumber} của chuyến đi \"{$trip->name}\"!",
            'item_id' => $itineraryItem->id
        ];
    }
}
