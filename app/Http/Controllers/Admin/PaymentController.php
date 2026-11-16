<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePaymentStatusRequest;
use App\Models\Payment;
use App\Services\Interfaces\PaymentServiceInterface;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Khởi tạo Controller với dependency PaymentServiceInterface.
     */
    public function __construct(
        private PaymentServiceInterface $paymentService
    ) {
    }

    /**
     * Danh sách tất cả các giao dịch thanh toán phục vụ quản trị (STT 11).
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $payments = $this->paymentService->getAllPayments($status, $search, 10);

        return view('admin.payments.index', [
            'payments' => $payments,
            'status' => $status,
            'search' => $search,
        ]);
    }

    /**
     * Xem chi tiết thông tin một giao dịch thanh toán (STT 11).
     */
    public function show(Payment $payment)
    {
        $payment->load(['booking.user', 'booking.roomType.destination', 'booking.trip']);

        return view('admin.payments.show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Quản trị viên cập nhật trạng thái giao dịch thanh toán (STT 11).
     */
    public function updateStatus(UpdatePaymentStatusRequest $request, Payment $payment)
    {
        $this->paymentService->updatePaymentStatus($payment->id, $request->validated()['status']);

        return redirect()
            ->back()
            ->with('success', "Cập nhật trạng thái giao dịch {$payment->transaction_id} thành công.");
    }
}
