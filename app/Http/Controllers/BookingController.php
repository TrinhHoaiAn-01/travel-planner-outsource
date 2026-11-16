<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Services\Interfaces\BookingServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Khởi tạo Controller với dependency injection BookingServiceInterface.
     */
    public function __construct(
        private BookingServiceInterface $bookingService
    ) {}

    /**
     * Hiển thị danh sách các đơn đặt phòng của người dùng hiện tại (Hình 29 trong Báo cáo).
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $bookings = $this->bookingService->getUserBookings(Auth::id(), $status);

        return view('bookings.index', [
            'bookings'      => $bookings,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Hiển thị thông tin chi tiết một đơn đặt phòng (Hình 28 trong Báo cáo).
     */
    public function show(Booking $booking): View
    {
        Gate::authorize('view', $booking);

        $booking = $this->bookingService->getBookingDetails($booking->id, Auth::id());

        return view('bookings.show', [
            'booking' => $booking,
        ]);
    }

    /**
     * Xử lý lưu đơn đặt phòng mới (Widget Hình 13).
     */
    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $booking = $this->bookingService->createBooking(
            $request->validated(),
            Auth::id()
        );

        if ($booking->trip_id) {
            return redirect()
                ->route('trips.show', $booking->trip_id)
                ->with('success', "Đặt phòng thành công! Mã đơn đặt: {$booking->booking_code}");
        }

        return redirect()
            ->route('home')
            ->with('success', "Đặt phòng thành công! Mã đơn đặt: {$booking->booking_code}");
    }

    /**
     * Hủy đơn đặt phòng của người dùng (BR-11).
     */
    public function cancel(Booking $booking): RedirectResponse
    {
        Gate::authorize('cancel', $booking);

        $this->bookingService->cancelBooking($booking->id, Auth::id());

        return redirect()
            ->back()
            ->with('success', 'Đã hủy đơn đặt phòng thành công.');
    }

    /**
     * API kiểm tra trạng thái thời gian thực cho danh sách đơn đặt phòng của người dùng (Realtime Polling).
     */
    public function userRealtimeStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['bookings' => []]);
        }

        $bookings = $user->bookings()
            ->with(['payments'])
            ->latest('id')
            ->limit(30)
            ->get();

        return response()->json([
            'bookings' => $bookings->map(function ($b) {
                $isPaid = $b->status === 'confirmed' || $b->status === 'completed' || $b->payments->contains('status', 'completed');
                $effectiveStatus = $isPaid && $b->status === 'pending' ? 'confirmed' : $b->status;
                return [
                    'id' => $b->id,
                    'booking_code' => $b->booking_code,
                    'status' => $effectiveStatus,
                    'is_paid' => $isPaid,
                    'status_label' => match ($effectiveStatus) {
                        'completed' => 'Completed',
                        'confirmed' => 'Đã xác nhận',
                        'cancelled' => 'Cancelled',
                        default     => 'Chờ duyệt',
                    },
                    'badge_html' => match ($effectiveStatus) {
                        'completed' => '<span class="badge-status badge-completed"><i class="bi bi-check-circle"></i> Completed</span>',
                        'confirmed' => '<span class="badge-status badge-confirmed"><i class="bi bi-check-circle-fill"></i> Đã xác nhận</span>',
                        'cancelled' => '<span class="badge-status badge-cancelled"><i class="bi bi-x-circle"></i> Cancelled</span>',
                        default     => '<span class="badge-status badge-upcoming"><i class="bi bi-clock"></i> Chờ duyệt</span>',
                    },
                ];
            }),
        ]);
    }
}
