<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Services\Interfaces\BookingServiceInterface;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Khởi tạo Controller với dependency injection BookingServiceInterface.
     */
    public function __construct(
        private BookingServiceInterface $bookingService
    ) {}

    /**
     * Xử lý lưu đơn đặt phòng mới.
     */
    public function store(StoreBookingRequest $request)
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
}
