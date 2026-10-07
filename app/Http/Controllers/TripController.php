<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripRequest;
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
