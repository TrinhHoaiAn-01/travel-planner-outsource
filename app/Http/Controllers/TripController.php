<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripNotesRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Http\Requests\UpdateTripStatusRequest;
use App\Models\Trip;
use App\Services\Interfaces\TripServiceInterface;
use Illuminate\Http\JsonResponse;
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
        $validatedData['status'] = $request->input('status', Trip::STATUS_PLANNED);

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

        // Phân tích trạng thái ảnh hưởng hiện tại đến chi phí và booking liên quan
        $impact = $this->tripService->checkTripChangeImpact($trip->id, [
            'start_date' => $trip->start_date ? $trip->start_date->format('Y-m-d') : null,
            'end_date' => $trip->end_date ? $trip->end_date->format('Y-m-d') : null,
            'budget' => (float) $trip->budget,
            'status' => $trip->status,
        ]);

        return view('trips.show', [
            'trip' => $tripDetails,
            'summary' => $summary,
            'daysList' => $daysList,
            'impact' => $impact,
        ]);
    }

    /**
     * Hiển thị màn hình chỉnh sửa thông tin chuyến đi.
     * Tải kèm các hoạt động lịch trình phụ thuộc, chi phí và đơn đặt phòng để kiểm tra ảnh hưởng.
     */
    public function edit(Trip $trip): View
    {
        Gate::authorize('update', $trip);

        // Nạp trước danh sách hoạt động lịch trình, chi phí và đơn đặt phòng liên quan
        $trip->load(['itineraryItems.destination', 'expenses', 'bookings.roomType.destination']);

        $affectedPreview = $this->tripService->getAffectedItineraryItems(
            $trip->id,
            $trip->start_date ? $trip->start_date->format('Y-m-d') : null,
            $trip->end_date ? $trip->end_date->format('Y-m-d') : null
        );

        $initialImpact = $this->tripService->checkTripChangeImpact($trip->id, [
            'start_date' => $trip->start_date ? $trip->start_date->format('Y-m-d') : null,
            'end_date' => $trip->end_date ? $trip->end_date->format('Y-m-d') : null,
            'budget' => (float) $trip->budget,
            'status' => $trip->status,
        ]);

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

        $bookingsList = $trip->bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code ?? '#BK-' . $booking->id,
                'destination_name' => $booking->roomType && $booking->roomType->destination ? $booking->roomType->destination->name : 'Điểm lưu trú',
                'room_type_name' => $booking->roomType ? $booking->roomType->name : 'Phòng #' . $booking->room_type_id,
                'check_in_date' => $booking->check_in_date ? $booking->check_in_date->format('Y-m-d') : '',
                'check_in_formatted' => $booking->check_in_date ? $booking->check_in_date->format('d/m/Y') : '',
                'check_out_date' => $booking->check_out_date ? $booking->check_out_date->format('Y-m-d') : '',
                'check_out_formatted' => $booking->check_out_date ? $booking->check_out_date->format('d/m/Y') : '',
                'room_count' => (int) $booking->room_count,
                'total_price' => (float) $booking->total_price,
                'status' => $booking->status,
                'status_label' => match ($booking->status) {
                    'pending' => 'Chờ xử lý',
                    'confirmed' => 'Đã xác nhận',
                    'cancelled' => 'Đã hủy',
                    default => $booking->status,
                },
            ];
        })->values()->toArray();

        $expensesList = $trip->expenses->map(function ($expense) {
            return [
                'id' => $expense->id,
                'category' => $expense->category,
                'description' => $expense->description,
                'amount' => (float) $expense->amount,
                'expense_date' => $expense->expense_date ? $expense->expense_date->format('Y-m-d') : '',
                'expense_date_formatted' => $expense->expense_date ? $expense->expense_date->format('d/m/Y') : '',
            ];
        })->values()->toArray();

        $totalExpenses = (float) $trip->expenses->sum('amount');

        return view('trips.edit', [
            'trip' => $trip,
            'affectedPreview' => $affectedPreview,
            'itineraryItemsList' => $itineraryItemsList,
            'bookingsList' => $bookingsList,
            'expensesList' => $expensesList,
            'totalExpenses' => $totalExpenses,
            'initialImpact' => $initialImpact,
        ]);
    }

    /**
     * Cập nhật thông tin chuyến đi qua Form Request và Service Interface.
     * Tự động phản hồi thông điệp chi tiết nếu có hoạt động lịch trình được bảo toàn hoặc ảnh hưởng chi phí, booking.
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

        $redirect = redirect()
            ->route('trips.show', $trip->id)
            ->with('success', $successMessage);

        // Nếu có cảnh báo ảnh hưởng đến Booking hoặc Chi phí, đính kèm thông báo cảnh báo vào session flash
        if (isset($updatedTrip->changeImpact) && $updatedTrip->changeImpact['has_impact']) {
            if (!empty($updatedTrip->changeImpact['summary_messages'])) {
                $redirect->with('impact_warning', implode(' ', $updatedTrip->changeImpact['summary_messages']));
            }
            $redirect->with('impact_details', $updatedTrip->changeImpact);
        }

        return $redirect;
    }

    /**
     * API Endpoint kiểm tra phân tích ảnh hưởng của việc thay đổi Trip đến Chi phí và Booking liên quan theo thời gian thực.
     */
    public function checkImpact(Request $request, Trip $trip): JsonResponse
    {
        Gate::authorize('update', $trip);

        $data = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'budget' => $request->has('budget') && $request->input('budget') !== '' ? (float) $request->input('budget') : null,
            'status' => $request->input('status'),
        ];

        $impact = $this->tripService->checkTripChangeImpact($trip->id, $data);

        return response()->json($impact);
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

    /**
     * Cập nhật trạng thái của chuyến đi (nháp, đã lập kế hoạch, hoàn thành...).
     * Phục vụ chức năng Quản lý trạng thái chuyến đi của Nguyễn Trần Thành.
     */
    public function updateStatus(UpdateTripStatusRequest $request, Trip $trip): RedirectResponse
    {
        // Phân quyền theo Policy chống lỗ hổng IDOR (BR-01 & BR-03)
        Gate::authorize('update', $trip);

        // Lấy trạng thái mới từ Form Request đã được xác thực
        $newStatus = $request->validated('status');

        // Gọi Service Interface xử lý thay đổi trạng thái
        $this->tripService->updateTripStatus($trip->id, $newStatus);

        // Chuẩn bị nhãn tiếng Việt tương ứng cho flash message
        $statusLabels = [
            Trip::STATUS_DRAFT => 'Bản nháp',
            Trip::STATUS_PLANNED => 'Đã lên kế hoạch',
            Trip::STATUS_ONGOING => 'Đang diễn ra',
            Trip::STATUS_COMPLETED => 'Đã hoàn thành',
            Trip::STATUS_CANCELLED => 'Đã hủy',
        ];
        $statusText = $statusLabels[$newStatus] ?? $newStatus;

        return back()->with('success', "Cập nhật trạng thái chuyến đi thành \"{$statusText}\" thành công!");
    }
}

