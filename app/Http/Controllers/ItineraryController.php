<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItineraryItemRequest;
use App\Http\Requests\UpdateItineraryItemRequest;
use App\Models\ItineraryItem;
use App\Models\Trip;
use App\Services\Interfaces\ItineraryServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Controller quản lý hiển thị và thao tác lịch trình chuyến đi theo từng ngày.
 * Thành viên phụ trách: Phạm Thị Ngọc Ái (MSSV: 24211TT3767 - Chức năng 5 & 6).
 * Tuân thủ quy chuẩn MVC mỏng (Thin Controller) và gọi qua Service Interface.
 */
class ItineraryController extends Controller
{
    /**
     * Khởi tạo ItineraryController với ràng buộc ItineraryServiceInterface.
     */
    public function __construct(
        private ItineraryServiceInterface $itineraryService
    ) {
    }

    /**
     * Hiển thị chi tiết lịch trình chuyến đi theo từng ngày.
     * Tuyến: GET /trips/{trip}/itinerary (Bảng 22 trong báo cáo).
     *
     * @param Request $request
     * @param Trip $trip Chuyến đi cần xem lịch trình
     * @return View
     */
    public function index(Request $request, Trip $trip): View
    {
        // 1. Kiểm tra quyền sở hữu chuyến đi (Quy tắc BR-01, BR-03 chống lỗi bảo mật IDOR)
        Gate::authorize('view', $trip);

        // 2. Nhận tham số day_number từ query string (nếu có)
        $dayNumber = $request->query('day_number') ? (int) $request->query('day_number') : null;

        // 3. Gọi Service Interface xử lý toàn bộ logic nghiệp vụ
        $data = $this->itineraryService->getTripItinerary($trip, $dayNumber);

        // 4. Trả về giao diện Blade hiển thị lịch trình theo ngày
        return view('itinerary.index', $data);
    }

    /**
     * Mở biểu mẫu thêm hoạt động mới vào lịch trình chuyến đi.
     * Tuyến: GET /trips/{trip}/itinerary/create (Bảng 22 trong báo cáo).
     *
     * @param Request $request
     * @param Trip $trip
     * @return View
     */
    public function create(Request $request, Trip $trip): View
    {
        // 1. Kiểm tra quyền sở hữu chuyến đi
        Gate::authorize('update', $trip);

        // 2. Nhận các tham số truyền sẵn (nếu có từ trang yêu thích hoặc địa điểm)
        $dayNumber = $request->query('day_number') ? (int) $request->query('day_number') : null;
        $destinationId = $request->query('destination_id') ? (int) $request->query('destination_id') : null;

        // 3. Lấy dữ liệu khởi tạo biểu mẫu
        $data = $this->itineraryService->getCreateActivityData($trip, $dayNumber, $destinationId);

        // 4. Trả về giao diện biểu mẫu thêm hoạt động
        return view('itinerary.create', $data);
    }

    /**
     * Lưu hoạt động mới vào lịch trình chuyến đi.
     * Tuyến: POST /trips/{trip}/itinerary (Bảng 22 trong báo cáo - Chức năng 6).
     *
     * @param StoreItineraryItemRequest $request
     * @param Trip $trip
     * @return RedirectResponse
     */
    public function store(StoreItineraryItemRequest $request, Trip $trip): RedirectResponse
    {
        // 1. Kiểm tra quyền cập nhật chuyến đi
        Gate::authorize('update', $trip);

        // 2. Gọi Service thực hiện kiểm tra nghiệp vụ và lưu hoạt động
        $item = $this->itineraryService->storeActivity($trip, $request->validated());

        // 3. Chuyển hướng về trang lịch trình chi tiết kèm thông báo thành công
        return redirect()
            ->route('itinerary.index', ['trip' => $trip->id, 'day_number' => $item->day_number])
            ->with('success', 'Đã thêm hoạt động "' . ($item->custom_title ?: 'mới') . '" vào lịch trình Ngày ' . $item->day_number . ' thành công!');
    }

    /**
     * Mở biểu mẫu hoặc lấy dữ liệu chỉnh sửa hoạt động trong lịch trình.
     * Tuyến: GET /itinerary/{itinerary}/edit (Bảng 22 trong báo cáo - Chức năng 7).
     *
     * @param Request $request
     * @param ItineraryItem $itinerary
     * @return View|JsonResponse
     */
    public function edit(Request $request, ItineraryItem $itinerary): View|JsonResponse
    {
        // 1. Kiểm tra quyền sở hữu Trip chứa {itinerary}
        Gate::authorize('update', $itinerary->trip);

        // 2. Lấy dữ liệu mục lịch trình cần sửa
        $data = $this->itineraryService->getEditActivityData($itinerary);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($data);
        }

        // 3. Trả về màn hình chỉnh sửa hoạt động
        return view('itinerary.edit', $data);
    }

    /**
     * Cập nhật thông tin hoạt động trong lịch trình chuyến đi.
     * Tuyến: PATCH /itinerary/{itinerary} (Bảng 22 trong báo cáo - Chức năng 7 & 8).
     *
     * @param UpdateItineraryItemRequest $request
     * @param ItineraryItem $itinerary
     * @return RedirectResponse|JsonResponse
     */
    public function update(UpdateItineraryItemRequest $request, ItineraryItem $itinerary): RedirectResponse|JsonResponse
    {
        // 1. Kiểm tra quyền sở hữu Trip chứa {itinerary}
        Gate::authorize('update', $itinerary->trip);

        $oldDay = (int) $itinerary->day_number;
        $item = $this->itineraryService->updateActivity($itinerary, $request->validated());

        $msg = ($oldDay !== (int) $item->day_number)
            ? 'Đã chuyển hoạt động "' . ($item->custom_title ?: 'hoạt động') . '" sang Ngày ' . $item->day_number . ' thành công!'
            : 'Đã cập nhật hoạt động "' . ($item->custom_title ?: 'thành công') . '" trong lịch trình Ngày ' . $item->day_number . '!';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'item' => $item,
            ]);
        }

        // 3. Chuyển hướng về trang xem lịch trình kèm thông báo thành công
        return redirect()
            ->route('itinerary.index', ['trip' => $itinerary->trip_id, 'day_number' => $item->day_number])
            ->with('success', $msg);
    }

    /**
     * Xóa mục hoạt động khỏi lịch trình chuyến đi.
     * Tuyến: DELETE /itinerary/{itinerary} (Bảng 22 trong báo cáo - Chức năng 7).
     *
     * @param Request $request
     * @param ItineraryItem $itinerary
     * @return RedirectResponse|JsonResponse
     */
    public function destroy(Request $request, ItineraryItem $itinerary): RedirectResponse|JsonResponse
    {
        // 1. Kiểm tra quyền sở hữu Trip chứa {itinerary}
        Gate::authorize('update', $itinerary->trip);

        $tripId = $itinerary->trip_id;
        $dayNumber = $itinerary->day_number;
        $activityTitle = $itinerary->custom_title ?: ($itinerary->destination ? $itinerary->destination->name : 'Hoạt động');

        // 2. Thực hiện xóa mục hoạt động
        $this->itineraryService->deleteActivity($itinerary);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa hoạt động "' . $activityTitle . '" khỏi lịch trình thành công!',
            ]);
        }

        // 3. Chuyển hướng về trang lịch trình chi tiết kèm thông báo
        return redirect()
            ->route('itinerary.index', ['trip' => $tripId, 'day_number' => $dayNumber])
            ->with('success', 'Đã xóa hoạt động "' . $activityTitle . '" khỏi lịch trình thành công!');
    }

    /**
     * Thay đổi thứ tự hiển thị các hoạt động trong một ngày của chuyến đi (FR21).
     * Tuyến: PATCH /trips/{trip}/itinerary/reorder (Bảng 22 trong báo cáo - Chức năng 8).
     *
     * @param Request $request
     * @param Trip $trip
     * @return JsonResponse
     */
    public function reorder(Request $request, Trip $trip): JsonResponse
    {
        // 1. Kiểm tra quyền sở hữu Trip
        Gate::authorize('update', $trip);

        // 2. Xác thực danh sách ID theo thứ tự
        $validated = $request->validate([
            'day_number' => ['required', 'integer', 'min:1'],
            'item_ids' => ['required', 'array'],
            'item_ids.*' => ['required', 'integer'],
        ]);

        // 3. Sắp xếp lại thứ tự trong transaction
        $this->itineraryService->reorderActivities($trip, (int) $validated['day_number'], $validated['item_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thứ tự các hoạt động Ngày ' . $validated['day_number'] . ' thành công!',
        ]);
    }
}
