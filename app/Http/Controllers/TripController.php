<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripNotesRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Models\Trip;
use App\Services\Interfaces\TripServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TripController extends Controller
{
    /**
     * Khởi tạo TripController với ràng buộc TripServiceInterface.
     */
    public function __construct(
        private TripServiceInterface $tripService
    ) {
    }

    /**
     * Hiển thị danh sách chuyến đi của người dùng kèm tìm kiếm và lọc.
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();

        // Thu thập các tiêu chí lọc từ truy vấn HTTP
        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status', 'all'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        // Lấy danh sách chuyến đi có phân trang qua Service Interface
        $trips = $this->tripService->getUserTrips($userId, $filters, 9);

        // Lấy thống kê số lượng theo từng trạng thái để hiển thị badge số lượng trên các tab
        $counts = $this->tripService->getTripCountsByStatus($userId);

        return view('trips.index', [
            'trips' => $trips,
            'counts' => $counts,
            'currentStatus' => $filters['status'],
            'searchKeyword' => $filters['search'],
            'startDate' => $filters['start_date'],
            'endDate' => $filters['end_date'],
        ]);
    }

    /**
     * Hiển thị màn hình tạo chuyến đi mới.
     */
    public function create(): View
    {
        return view('trips.create');
    }

    /**
     * Xử lý lưu chuyến đi mới qua Form Request và Service Interface.
     */
    public function store(StoreTripRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();
        $validatedData['user_id'] = Auth::id();
        $validatedData['status'] = Trip::STATUS_PLANNED;

        $trip = $this->tripService->createTrip($validatedData);

        return redirect()
            ->route('trips.index')
            ->with('success', 'Tạo chuyến đi mới thành công!');
    }

    /**
     * Hiển thị chi tiết chuyến đi của người dùng.
     * Tuân thủ quy tắc bảo mật BR-01 và BR-03 chống lỗi IDOR.
     */
    public function show(Trip $trip): View
    {
        Gate::authorize('view', $trip);

        $tripDetails = $this->tripService->getTripDetails($trip->id);
        $summary = $this->tripService->getTripSummary($trip->id);
        $daysList = $this->tripService->getTripDaysList($trip->id);

        return view('trips.show', [
            'trip' => $tripDetails,
            'summary' => $summary,
            'daysList' => $daysList,
        ]);
    }

    /**
     * Hiển thị màn hình chỉnh sửa thông tin chuyến đi.
     * Tải kèm các hoạt động lịch trình phụ thuộc để người dùng nắm rõ khi có thay đổi ngày.
     */
    public function edit(Trip $trip): View
    {
        Gate::authorize('update', $trip);

        // Nạp trước danh sách hoạt động lịch trình và điểm đến tương ứng
        $trip->load(['itineraryItems.destination']);
        $affectedPreview = $this->tripService->getAffectedItineraryItems(
            $trip->id,
            $trip->start_date ? $trip->start_date->format('Y-m-d') : null,
            $trip->end_date ? $trip->end_date->format('Y-m-d') : null
        );

        $itineraryItemsList = $trip->itineraryItems->map(function ($item) {
            return [
                'id' => $item->id,
                'day_number' => (int) $item->day_number,
                'destination_name' => $item->destination ? $item->destination->name : 'Điểm tham quan #' . $item->destination_id,
                'start_time' => $item->start_time ? substr($item->start_time, 0, 5) : '',
                'end_time' => $item->end_time ? substr($item->end_time, 0, 5) : '',
                'note' => $item->note,
                'sort_order' => (int) $item->sort_order,
            ];
        })->values()->toArray();

        return view('trips.edit', [
            'trip' => $trip,
            'affectedPreview' => $affectedPreview,
            'itineraryItemsList' => $itineraryItemsList,
        ]);
    }

    /**
     * Cập nhật thông tin chuyến đi qua Form Request và Service Interface.
     * Tự động phản hồi thông điệp chi tiết nếu có hoạt động lịch trình được bảo toàn do rút ngắn ngày.
     */
    public function update(UpdateTripRequest $request, Trip $trip): RedirectResponse
    {
        Gate::authorize('update', $trip);

        $validatedData = $request->validated();
        $updatedTrip = $this->tripService->updateTrip($trip->id, $validatedData);

        // Thông điệp mặc định khi cập nhật thành công
        $successMessage = 'Cập nhật chuyến đi thành công!';

        // Nếu có hoạt động lịch trình phụ thuộc được tự động bảo toàn dời ngày an toàn
        if (isset($updatedTrip->dateChangeResult['reassigned_count']) && $updatedTrip->dateChangeResult['reassigned_count'] > 0) {
            $successMessage = $updatedTrip->dateChangeResult['message'];
        }

        return redirect()
            ->route('trips.show', $trip->id)
            ->with('success', $successMessage);
    }

    /**
     * Xóa chuyến đi khỏi hệ thống.
     */
    public function destroy(Trip $trip): RedirectResponse
    {
        Gate::authorize('delete', $trip);

        $this->tripService->deleteTrip($trip->id);

        return redirect()
            ->route('trips.index')
            ->with('success', 'Xóa chuyến đi thành công!');
    }

    /**
     * Nhân bản chuyến đi đã có (Nghiệp vụ của Nguyễn Trần Thành).
     */
    public function clone(Trip $trip): RedirectResponse
    {
        Gate::authorize('view', $trip);

        $this->tripService->cloneTrip($trip->id);

        return redirect()
            ->route('trips.index')
            ->with('success', 'Đã nhân bản chuyến đi thành công!');
    }

    /**
     * Mở lại chuyến đi đã hoàn thành (Nghiệp vụ của Nguyễn Trần Thành).
     */
    public function reopen(Trip $trip): RedirectResponse
    {
        Gate::authorize('update', $trip);

        $this->tripService->reopenTrip($trip->id);

        return redirect()
            ->route('trips.index')
            ->with('success', 'Đã mở lại chuyến đi thành công!');
    }

    /**
     * Cập nhật nhanh mô tả và ghi chú tổng quát của chuyến đi.
     * Thuộc chức năng Quản lý mô tả và ghi chú tổng quát của Nguyễn Trần Thành.
     */
    public function updateNotes(UpdateTripNotesRequest $request, Trip $trip): RedirectResponse
    {
        Gate::authorize('update', $trip);

        $description = $request->validated('description');
        $this->tripService->updateTripNotes($trip->id, $description);

        return redirect()
            ->route('trips.show', $trip->id)
            ->with('success', 'Đã cập nhật mô tả và ghi chú tổng quát chuyến đi thành công!');
    }
}

