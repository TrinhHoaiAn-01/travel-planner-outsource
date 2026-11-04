<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Interfaces\PaymentServiceInterface;
use App\Services\Interfaces\VnPayGatewayServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VnPayGatewayService implements VnPayGatewayServiceInterface
{
    private string $tmnCode;
    private string $hashSecret;
    private string $vnpUrl;

    /**
     * Khởi tạo VnPayGatewayService với các khóa cấu hình chuẩn VNPay API V2.1.0.
     */
    public function __construct(
        private PaymentServiceInterface $paymentService
    ) {
        $this->tmnCode = (string) config('vnpay.tmn_code', 'UTSQQSRL');
        $this->hashSecret = (string) config('vnpay.hash_secret', 'VZWQDFXHAPZUJZHIAGELBGWXFDOZVKFB');
        $this->vnpUrl = (string) config('vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
    }

    /**
     * Tạo liên kết thanh toán sang cổng VNPay Sandbox kèm chữ ký số HMAC-SHA512.
     */
    public function createPaymentUrl(Booking $booking, ?string $ipAddress = null): array
    {
        // 1. Kiểm tra trạng thái đơn: Không thể thanh toán nếu đơn đã bị hủy
        if ($booking->status === 'cancelled') {
            throw ValidationException::withMessages([
                'booking' => 'Không thể thực hiện thanh toán cho đơn đặt phòng đã bị hủy.',
            ]);
        }

        // 2. Chuẩn bị các tham số giao dịch theo quy chuẩn VNPay API 2.1.0
        $txnRef = $booking->booking_code . '_' . time();
        $orderInfo = 'Thanh toan don dat phong #' . $booking->booking_code;
        // VNPay quy định số tiền thanh toán nhân 100 (đơn vị: xu/đồng)
        $amount = (int) round($booking->total_price * 100);
        $clientIp = $ipAddress ?: (request()?->ip() ?: '127.0.0.1');

        $vnpData = [
            'vnp_Version' => (string) config('vnpay.version', '2.1.0'),
            'vnp_TmnCode' => $this->tmnCode,
            'vnp_Amount' => (string) $amount,
            'vnp_Command' => (string) config('vnpay.command', 'pay'),
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode' => (string) config('vnpay.curr_code', 'VND'),
            'vnp_IpAddr' => $clientIp,
            'vnp_Locale' => (string) config('vnpay.locale', 'vn'),
            'vnp_OrderInfo' => $orderInfo,
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => route('payments.vnpay.callback'),
            'vnp_TxnRef' => $txnRef,
            'vnp_ExpireDate' => date('YmdHis', strtotime('+' . config('vnpay.expire_minutes', 15) . ' minutes')),
        ];

        // 3. Sắp xếp mảng theo thứ tự bảng chữ cái A-Z (bắt buộc theo quy chuẩn VNPay)
        ksort($vnpData);

        // 4. Ghép chuỗi truy vấn dữ liệu thô để sinh mã băm kiểm tra toàn vẹn
        $query = '';
        $i = 0;
        $hashData = '';
        foreach ($vnpData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode((string) $key) . '=' . urlencode((string) $value);
            } else {
                $hashData .= urlencode((string) $key) . '=' . urlencode((string) $value);
                $i = 1;
            }
            $query .= urlencode((string) $key) . '=' . urlencode((string) $value) . '&';
        }

        // 5. Ký số bằng thuật toán HMAC-SHA512 với Secret Key do VNPay cấp
        $vnpSecureHash = hash_hmac('sha512', $hashData, $this->hashSecret);
        $payUrl = $this->vnpUrl . '?' . $query . 'vnp_SecureHash=' . $vnpSecureHash;

        return [
            'success' => true,
            'payUrl' => $payUrl,
            'txnRef' => $txnRef,
            'message' => 'Khởi tạo liên kết thanh toán VNPay thành công.',
        ];
    }

    /**
     * Xác thực chữ ký số HMAC-SHA512 của dữ liệu VNPay phản hồi qua Return URL hoặc IPN Webhook.
     */
    public function verifySignature(array $data): bool
    {
        $vnpSecureHash = $data['vnp_SecureHash'] ?? '';
        if (empty($vnpSecureHash)) {
            return false;
        }

        // Thu thập các trường bắt đầu bằng vnp_, loại bỏ vnp_SecureHash và vnp_SecureHashType
        $vnpData = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'vnp_') && $key !== 'vnp_SecureHash' && $key !== 'vnp_SecureHashType') {
                $vnpData[$key] = $value;
            }
        }

        // Sắp xếp các tham số theo bảng chữ cái A-Z
        ksort($vnpData);

        $i = 0;
        $hashData = '';
        foreach ($vnpData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode((string) $key) . '=' . urlencode((string) $value);
            } else {
                $hashData .= urlencode((string) $key) . '=' . urlencode((string) $value);
                $i = 1;
            }
        }

        // Tạo chữ ký số mong đợi từ chuỗi dữ liệu nhận được
        $expectedHash = hash_hmac('sha512', $hashData, $this->hashSecret);

        return hash_equals(strtolower($expectedHash), strtolower($vnpSecureHash));
    }

    /**
     * Xử lý kết quả giao dịch từ VNPay, cập nhật trạng thái thanh toán và đơn đặt phòng.
     */
    public function processCallback(array $callbackData): Payment
    {
        $txnRef = $callbackData['vnp_TxnRef'] ?? null;
        $responseCode = $callbackData['vnp_ResponseCode'] ?? null;
        $rawAmount = (float) ($callbackData['vnp_Amount'] ?? 0);
        $vnpAmount = $rawAmount > 0 ? ($rawAmount / 100) : 0;
        $transactionNo = $callbackData['vnp_TransactionNo'] ?? ('VNPAY_' . time());

        // Phân tích mã đơn đặt phòng từ chuỗi vnp_TxnRef dạng {booking_code}_{timestamp}
        $booking = null;
        if ($txnRef) {
            $parts = explode('_', $txnRef);
            $bookingCode = $parts[0] ?? null;
            if ($bookingCode) {
                $booking = Booking::where('booking_code', $bookingCode)->first();
            }
        }

        if (!$booking) {
            throw ValidationException::withMessages([
                'booking' => 'Không tìm thấy đơn đặt phòng liên kết với mã giao dịch VNPay.',
            ]);
        }

        // Kiểm tra đối soát số tiền khớp Database theo quy tắc chống gian lận BR-21
        if ($vnpAmount > 0 && abs($vnpAmount - (float) $booking->total_price) >= 0.01) {
            throw ValidationException::withMessages([
                'amount' => 'Số tiền thanh toán từ cổng VNPay không khớp với giá trị đơn đặt phòng trong hệ thống (Quy tắc BR-21).',
            ]);
        }

        // Mã phản hồi vnp_ResponseCode = '00' biểu thị giao dịch thanh toán thành công
        $status = ($responseCode === '00') ? 'completed' : 'failed';

        return DB::transaction(function () use ($booking, $vnpAmount, $transactionNo, $status) {
            $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->firstOrFail();

            // Chống xử lý trùng lặp khi người dùng hoặc IPN gọi nhiều lần
            if (($lockedBooking->status === 'confirmed' || $lockedBooking->status === 'completed') && $status === 'completed') {
                $existingPayment = Payment::where('booking_id', $lockedBooking->id)
                    ->where('status', 'completed')
                    ->where('payment_method', 'vnpay')
                    ->latest('id')
                    ->first();
                if ($existingPayment) {
                    return $existingPayment;
                }
            }

            // Cập nhật trạng thái đơn đặt phòng sang confirmed khi thanh toán thành công
            if ($status === 'completed') {
                $lockedBooking->update([
                    'status' => 'confirmed',
                ]);
            }

            // Ghi nhận bản ghi thanh toán VNPay
            $payment = Payment::create([
                'booking_id' => $lockedBooking->id,
                'amount' => $vnpAmount > 0 ? $vnpAmount : $lockedBooking->total_price,
                'payment_method' => 'vnpay',
                'transaction_id' => (string) $transactionNo,
                'status' => $status,
                'payment_date' => now(),
            ]);

            return $payment;
        }, 5);
    }
}
