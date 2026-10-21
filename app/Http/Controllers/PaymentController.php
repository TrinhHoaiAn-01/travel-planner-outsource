<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessPaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Interfaces\MoMoGatewayServiceInterface;
use App\Services\Interfaces\PaymentServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    /**
     * Khởi tạo Controller với dependency PaymentServiceInterface và MoMoGatewayServiceInterface.
     */
    public function __construct(
        private PaymentServiceInterface $paymentService,
        private MoMoGatewayServiceInterface $momoGatewayService
    ) {
    }

    /**
     * Hiển thị giao diện chọn phương thức thanh toán cho đơn đặt phòng (STT 9).
     */
    public function create(Booking $booking)
    {
        // 1. Kiểm tra quyền thanh toán đơn (BR-11, BookingPolicy)
        Gate::authorize('pay', $booking);

        // 2. Nạp dữ liệu quan hệ cần thiết
        $booking->load(['roomType.destination', 'trip', 'user', 'payments']);

        return view('payments.create', [
            'booking' => $booking,
        ]);
    }

    /**
     * Xử lý thực hiện thanh toán cho đơn đặt phòng (STT 9, 10 - BR-21).
     */
    public function store(ProcessPaymentRequest $request, Booking $booking)
    {
        // 1. Kiểm tra quyền thanh toán đơn (BR-11, BookingPolicy)
        Gate::authorize('pay', $booking);

        // 2. Gọi Service xử lý thanh toán và lưu giao dịch
        $payment = $this->paymentService->processPayment($booking, $request->validated());

        return redirect()
            ->route('payments.show', $payment)
            ->with('success', 'Thanh toán đơn đặt phòng thành công. Trạng thái đơn đã được cập nhật.');
    }

    /**
     * Hiển thị thông tin chi tiết hóa đơn / biên lai thanh toán (STT 10).
     */
    public function show(Payment $payment)
    {
        // 1. Kiểm tra quyền xem thông tin thanh toán (BR-11, BR-12, PaymentPolicy)
        Gate::authorize('view', $payment);

        $payment->load(['booking.user', 'booking.roomType.destination', 'booking.trip']);

        return view('payments.show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Khởi tạo liên kết thanh toán và chuyển hướng sang cổng MoMo Sandbox API (STT 9 - MoMo Live).
     */
    public function payWithMoMo(Booking $booking)
    {
        // 1. Kiểm tra quyền thanh toán đơn (BR-11, BookingPolicy)
        Gate::authorize('pay', $booking);

        // 2. Gọi MoMoGatewayService tạo URL thanh toán
        $result = $this->momoGatewayService->createPaymentUrl($booking);

        if ($result['success'] && !empty($result['payUrl'])) {
            return redirect()->away($result['payUrl']);
        }

        // Trường hợp lỗi kết nối hoặc sandbox MoMo bảo trì, thông báo và gợi ý dùng sandbox nội bộ
        return redirect()
            ->route('payments.create', $booking)
            ->with('warning', 'Không thể chuyển tiếp sang cổng MoMo Live Sandbox (' . $result['message'] . '). Bạn có thể sử dụng chế độ Sandbox Nội Bộ bên dưới để hoàn tất thử nghiệm ngay lập tức.');
    }

    /**
     * Xử lý chuyển hướng trở về từ cổng MoMo sau khi người dùng thực hiện giao dịch.
     */
    public function handleMoMoCallback(Request $request)
    {
        $data = $request->all();

        // 1. Xác thực chữ ký số HMAC-SHA256 gửi từ MoMo
        $isValid = $this->momoGatewayService->verifySignature($data);

        if (!$isValid) {
            return redirect()
                ->route('bookings.index')
                ->with('error', 'Chữ ký số xác thực từ MoMo không hợp lệ (Phát hiện nguy cơ giả mạo dữ liệu - BR-21).');
        }

        $resultCode = (int) ($data['resultCode'] ?? -1);

        // 2. Nếu thanh toán thành công (resultCode = 0)
        if ($resultCode === 0) {
            $payment = $this->momoGatewayService->processCallback($data);

            return redirect()
                ->route('payments.show', $payment)
                ->with('success', 'Giao dịch qua ví MoMo thành công! Đơn đặt phòng đã được xác nhận.');
        }

        // Giao dịch bị hủy hoặc thất bại
        return redirect()
            ->route('bookings.index')
            ->with('error', 'Giao dịch MoMo không thành công hoặc người dùng đã hủy (Mã lỗi: ' . $resultCode . ' - ' . ($data['message'] ?? '') . ').');
    }

    /**
     * Xử lý thông báo tức thì (IPN Webhook) gửi từ máy chủ MoMo chạy ngầm.
     */
    public function handleMoMoIpn(Request $request)
    {
        $data = $request->all();

        if (!$this->momoGatewayService->verifySignature($data)) {
            return response()->json(['message' => 'Invalid MoMo Signature'], 400);
        }

        $this->momoGatewayService->processCallback($data);

        return response()->json([
            'message' => 'MoMo IPN Processed Successfully',
            'resultCode' => 0,
        ]);
    }

    /**
     * Kiểm tra trạng thái thanh toán thời gian thực của đơn đặt phòng (Realtime Polling).
     */
    public function checkStatus(Booking $booking)
    {
        Gate::authorize('view', $booking);

        $freshBooking = $booking->fresh();
        $latestPayment = Payment::where('booking_id', $freshBooking->id)
            ->where('status', 'completed')
            ->latest('id')
            ->first();

        $isPaid = ($freshBooking->status === 'confirmed' || $freshBooking->status === 'completed') && $latestPayment !== null;

        return response()->json([
            'is_paid' => $isPaid,
            'status' => $freshBooking->status,
            'redirect_url' => $latestPayment ? route('payments.show', $latestPayment->id) : null,
            'message' => $isPaid 
                ? 'Đã nhận được thanh toán thành công cho đơn #' . $freshBooking->booking_code
                : 'Chưa ghi nhận biến động số dư cho đơn #' . $freshBooking->booking_code,
        ]);
    }

    /**
     * Giả lập tín hiệu nhận tiền thành công từ ví MoMo hoặc tài khoản ngân hàng (Webhook Simulator cho Dev & Demo).
     */
    public function simulateReceive(Request $request, Booking $booking)
    {
        Gate::authorize('pay', $booking);

        $paymentMethod = $request->input('payment_method', 'bank_transfer');

        $payment = $this->paymentService->processPayment($booking, [
            'amount' => $booking->total_price,
            'payment_method' => $paymentMethod,
            'note' => 'Giao dịch chuyển tiền xác nhận tự động qua cổng thanh toán.',
        ]);

        return response()->json([
            'success' => true,
            'is_paid' => true,
            'message' => 'Hệ thống đã nhận được tiền thành công từ ví MoMo / tài khoản ngân hàng!',
            'redirect_url' => route('payments.show', $payment->id),
        ]);
    }
}
