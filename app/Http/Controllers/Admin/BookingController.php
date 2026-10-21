<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBookingStatusRequest;
use App\Models\Booking;
use App\Services\Interfaces\BookingServiceInterface;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Khởi tạo Controller với dependency BookingServiceInterface.
     */
    public function __construct(
        private BookingServiceInterface $bookingService
    ) {
    }

    /**
     * Danh sách tất cả các đơn đặt phòng phục vụ quản trị (STT 11).
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $bookings = $this->bookingService->getAllBookings($status, $search, 10);

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'status' => $status,
            'search' => $search,
        ]);
    }

    /**
     * Xem thông tin chi tiết một đơn đặt phòng (STT 11).
     */
    public function show(Booking $booking)
    {
        $booking->load(['user', 'roomType.destination', 'trip', 'payments']);

        return view('admin.bookings.show', [
            'booking' => $booking,
        ]);
    }

    /**
     * Quản trị viên cập nhật trạng thái đơn đặt phòng (STT 11).
     */
    public function updateStatus(UpdateBookingStatusRequest $request, Booking $booking)
    {
        $this->bookingService->updateBookingStatus($booking->id, $request->validated()['status']);

        return redirect()
            ->back()
            ->with('success', "Cập nhật trạng thái đơn đặt phòng #{$booking->booking_code} thành công.");
    }
}
