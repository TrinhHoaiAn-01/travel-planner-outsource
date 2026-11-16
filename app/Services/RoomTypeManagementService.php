<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\RoomType;
use App\Services\Interfaces\RoomTypeManagementServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Service xử lý logic nghiệp vụ Quản lý Loại phòng dành cho Admin.
 * Thành viên phụ trách: Trần Văn Trọng (22211TT0337) - Mục 10.
 * Tuân thủ quy chuẩn MVC 3 lớp, bảo vệ toàn vẹn dữ liệu giao dịch và kiểm tra ràng buộc.
 */
class RoomTypeManagementService implements RoomTypeManagementServiceInterface
{
    /**
     * Lấy danh sách phân trang các loại phòng kèm bộ lọc.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getRoomTypes(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = RoomType::query()->with('destination');

        // 1. Tìm kiếm bằng Apache Solr thông qua SearchServiceInterface
        if (! empty($filters['search'])) {
            $keyword = trim((string) $filters['search']);
            app(\App\Services\Interfaces\SearchServiceInterface::class)->apply($query, 'room_type', $keyword);
        }

        // Lọc theo địa điểm cụ thể
        if (! empty($filters['destination_id'])) {
            $query->where('destination_id', (int) $filters['destination_id']);
        }

        return $query->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    /**
     * Lấy thống kê tổng quan về loại phòng và cơ sở lưu trú.
     *
     * @return array<string, mixed>
     */
    public function getRoomTypeStatistics(): array
    {
        $totalRoomTypes = RoomType::count();
        $totalRooms = (int) RoomType::sum('quantity');
        $averagePrice = (float) RoomType::avg('price_per_night');
        $destinationsWithRooms = Destination::has('roomTypes')->count();

        return [
            'total_room_types' => $totalRoomTypes,
            'total_rooms' => $totalRooms,
            'average_price' => $averagePrice,
            'destinations_with_rooms' => $destinationsWithRooms,
        ];
    }

    /**
     * Lấy danh sách địa điểm để hiển thị trong select dropdown.
     *
     * @return Collection
     */
    public function getDestinationsForSelect(): Collection
    {
        return Destination::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    /**
     * Thêm mới một loại phòng.
     *
     * @param array<string, mixed> $data
     * @return RoomType
     */
    public function createRoomType(array $data): RoomType
    {
        return DB::transaction(function () use ($data) {
            return RoomType::create([
                'destination_id' => (int) $data['destination_id'],
                'name' => trim((string) $data['name']),
                'capacity' => (int) $data['capacity'],
                'price_per_night' => (float) $data['price_per_night'],
                'quantity' => (int) $data['quantity'],
                'description' => isset($data['description']) ? trim((string) $data['description']) : null,
                'commerce_paused' => false,
            ]);
        });
    }

    /**
     * Cập nhật thông tin loại phòng.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return RoomType
     */
    public function updateRoomType(int $id, array $data): RoomType
    {
        $roomType = RoomType::findOrFail($id);

        return DB::transaction(function () use ($roomType, $data) {
            $roomType->update([
                'destination_id' => (int) $data['destination_id'],
                'name' => trim((string) $data['name']),
                'capacity' => (int) $data['capacity'],
                'price_per_night' => (float) $data['price_per_night'],
                'quantity' => (int) $data['quantity'],
                'description' => isset($data['description']) ? trim((string) $data['description']) : null,
            ]);

            return $roomType;
        });
    }

    /**
     * Xóa loại phòng (kiểm tra an toàn đơn đặt phòng để bảo vệ toàn vẹn dữ liệu BR-17).
     *
     * @param int $id
     * @return bool
     * @throws ValidationException
     */
    public function deleteRoomType(int $id): bool
    {
        $roomType = RoomType::findOrFail($id);

        // Kiểm tra xem loại phòng có đơn đặt phòng nào đang hoạt động không
        $hasActiveBookings = $roomType->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($hasActiveBookings) {
            throw ValidationException::withMessages([
                'room_type' => 'Không thể xóa loại phòng đang có đơn đặt phòng đang chờ hoặc đã xác nhận.',
            ]);
        }

        return DB::transaction(function () use ($roomType) {
            return (bool) $roomType->delete();
        });
    }
}

