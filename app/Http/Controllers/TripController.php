<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripRequest;
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

        return view('trips.show', [
            'trip' => $tripDetails,
            'summary' => $summary,
        ]);
    }

    /**
     * Hiển thị màn hình chỉnh sửa thông tin chuyến đi.
     */
    public function edit(Trip $trip): View
    {
        Gate::authorize('update', $trip);

        return view('trips.edit', [
            'trip' => $trip,
        ]);
    }

    /**
     * Cập nhật thông tin chuyến đi qua Form Request và Service Interface.
     */
    public function update(UpdateTripRequest $request, Trip $trip): RedirectResponse
    {
        Gate::authorize('update', $trip);

        $validatedData = $request->validated();
        $this->tripService->updateTrip($trip->id, $validatedData);

        return redirect()
            ->route('trips.show', $trip->id)
            ->with('success', 'Cập nhật chuyến đi thành công!');
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
}
