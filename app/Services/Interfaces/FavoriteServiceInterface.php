<?php

namespace App\Services\Interfaces;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface định nghĩa các nghiệp vụ quản lý địa điểm yêu thích
 */
interface FavoriteServiceInterface
{
    /**
     * Lấy danh sách địa điểm yêu thích của người dùng kèm phân trang
     *
     * @param User $user Người dùng hiện tại
     * @param int $perPage Số lượng mỗi trang
     * @return LengthAwarePaginator
     */
    public function getUserFavorites(User $user, int $perPage = 8): LengthAwarePaginator;

    /**
     * Thêm hoặc xóa địa điểm khỏi danh sách yêu thích (Toggle)
     *
     * @param User $user Người dùng hiện tại
     * @param int $destinationId Mã địa điểm
     * @return array Trả về trạng thái ['is_favorited' => bool, 'message' => string]
     */
    public function toggleFavorite(User $user, int $destinationId): array;

    /**
     * Kiểm tra xem người dùng đã yêu thích địa điểm hay chưa
     *
     * @param User|null $user
     * @param int $destinationId
     * @return bool
     */
    public function isFavorited(?User $user, int $destinationId): bool;

    /**
     * Lấy danh sách ID các địa điểm người dùng đã yêu thích
     *
     * @param User|null $user
     * @return array
     */
    public function getUserFavoriteDestinationIds(?User $user): array;

    /**
     * Thêm địa điểm vào lịch trình chuyến đi của người dùng
     *
     * @param User $user Người dùng hiện tại
     * @param array $data Dữ liệu bao gồm: trip_id, destination_id, day_number, note, start_time, end_time
     * @return \App\Models\ItineraryItem
     */
    public function addDestinationToTrip(User $user, array $data): \App\Models\ItineraryItem;
}
