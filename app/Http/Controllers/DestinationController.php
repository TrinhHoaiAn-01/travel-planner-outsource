<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Services\Interfaces\DestinationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DestinationController extends Controller
{
    protected DestinationServiceInterface $destinationService;

    /**
     * Khởi tạo Controller với Service Interface tiêm phụ thuộc (Dependency Injection).
     */
    public function __construct(DestinationServiceInterface $destinationService)
    {
        $this->destinationService = $destinationService;
    }

    /**
     * Hiển thị màn hình Khám phá địa điểm du lịch & Nơi lưu trú (Khách sạn / Resort) có phân trang và bộ lọc.
     * Tái hiện theo Hình 12 trong tài liệu báo cáo.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'keyword', 'city_id', 'category_id', 'budget', 'is_lodging', 'sort']);
        
        $destinations = $this->destinationService->getDestinations($filters, 6);
        $cities = $this->destinationService->getPopularCities(20);
        $categories = $this->destinationService->getCategories();

        return view('destinations.index', [
            'destinations' => $destinations,
            'cities' => $cities,
            'categories' => $categories,
            'currentFilters' => $filters,
        ]);
    }

    /**
     * Hiển thị trang Chi tiết địa điểm du lịch, thông tin nơi lưu trú, thư viện ảnh và danh sách các loại phòng (Room Types).
     * Tái hiện theo Hình 13 trong tài liệu báo cáo.
     *
     * @param string $slug
     * @return View
     */
    public function show(string $slug): View
    {
        $destination = $this->destinationService->getDestinationBySlug($slug);

        if (! $destination) {
            abort(404, 'Không tìm thấy địa điểm du lịch yêu cầu.');
        }

        $relatedDestinations = $this->destinationService->getRelatedDestinations($destination, 4);

        // Lấy danh sách chuyến đi của người dùng nếu đã đăng nhập để hỗ trợ modal "Thêm vào Trip"
        $userTrips = auth()->check()
            ? auth()->user()->trips()->whereIn('status', ['draft', 'planned'])->latest()->get()
            : collect();

        return view('destinations.show', [
            'destination' => $destination,
            'relatedDestinations' => $relatedDestinations,
            'userTrips' => $userTrips,
        ]);
    }

    /**
     * API xem thông tin chi tiết một loại phòng (Room Type) cho Modal và đặt phòng.
     *
     * @param int $roomTypeId
     * @return JsonResponse
     */
    public function getRoomType(int $roomTypeId): JsonResponse
    {
        $roomType = $this->destinationService->getRoomTypeById($roomTypeId);

        if (! $roomType) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin loại phòng.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $roomType->id,
                'destination_id' => $roomType->destination_id,
                'destination_name' => $roomType->destination?->name,
                'name' => $roomType->name,
                'capacity' => $roomType->capacity,
                'capacity_label' => $roomType->capacity . ' người lớn',
                'price_per_night' => (float) $roomType->price_per_night,
                'formatted_price' => number_format((float) $roomType->price_per_night, 0, ',', '.') . 'đ',
                'quantity' => $roomType->quantity,
                'description' => $roomType->description ?? 'Không gian nghỉ dưỡng thoải mái, sang trọng với đầy đủ tiện nghi chuẩn cao cấp.',
                'amenities' => [
                    'Wi-Fi tốc độ cao miễn phí',
                    'Điều hòa nhiệt độ 2 chiều',
                    'Phòng tắm riêng có nước nóng và máy sấy',
                    'Bữa sáng buffet phong phú mỗi sáng',
                    'Tivi màn hình phẳng truyền hình cáp',
                    'Miễn phí hủy phòng trước 48h nhận phòng',
                ],
            ],
        ]);
    }
}
