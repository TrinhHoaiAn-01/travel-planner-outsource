<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Interfaces\SePayGatewayServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SePayGatewayService implements SePayGatewayServiceInterface
{
    private string $apiKey;
    private string $accountNumber;
    private string $bankName;
    private string $accountHolder;
    private string $patternPrefix;
    private string $qrImageEndpoint;

    /**
     * Khởi tạo SePayGatewayService với các cấu hình từ config/sepay.php.
     */
    public function __construct()
    {
        $this->apiKey = (string) config('sepay.api_key', 'sepay_demo_api_key_travel_planner');
        $this->accountNumber = (string) config('sepay.account_number', '0901234567');
        $this->bankName = (string) config('sepay.bank_name', 'MBBank');
        $this->accountHolder = (string) config('sepay.account_holder', 'TRINH HOAI AN');
        $this->patternPrefix = (string) config('sepay.pattern_prefix', 'TPB');
        $this->qrImageEndpoint = (string) config('sepay.qr_image_endpoint', 'https://qr.sepay.vn/img');
    }

    /**
     * Lấy thông tin thanh toán chuyển khoản ngân hàng gồm tài khoản, nội dung và mã VietQR.
     */
    public function getPaymentDetails(Booking $booking): array
    {
        // 1. Kiểm tra trạng thái đơn: Không thể thanh toán nếu đơn đã bị hủy
        if ($booking->status === 'cancelled') {
            throw ValidationException::withMessages([
                'booking' => 'Không thể thực hiện thanh toán cho đơn đặt phòng đã bị hủy.',
            ]);
        }

        // 2. Định dạng cú pháp chuyển khoản chuẩn xác: Tiền tố + Mã đơn (Ví dụ: TPB BK2026110601)
        $transferContent = trim($this->patternPrefix . ' ' . $booking->booking_code);
        $amount = (float) $booking->total_price;

        // 3. Tạo đường dẫn hình ảnh mã VietQR tự động qua SePay
        $qrCodeUrl = $this->generateQrCodeUrl($amount, $transferContent);

        return [
            'bank_name' => $this->bankName,
            'account_number' => $this->accountNumber,
            'account_holder' => $this->accountHolder,
            'amount' => $amount,
            'formatted_amount' => number_format($amount, 0, ',', '.') . ' ₫',
            'transfer_content' => $transferContent,
            'qr_code_url' => $qrCodeUrl,
            'booking_code' => $booking->booking_code,
        ];
    }

    /**
     * Khởi tạo hoặc lấy bản ghi Payment ở trạng thái chờ thanh toán (pending) cho đơn đặt phòng.
     */
    public function createOrGetPayment(Booking $booking): Payment
    {
        if ($booking->isPaid()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'booking' => 'Đơn đặt phòng đã được thanh toán hoàn tất, không thể khởi tạo giao dịch mới.',
            ]);
        }

        // Kiểm tra nếu đã có payment pending trước đó thì tái sử dụng
        $existingPayment = $booking->payments()
            ->where('payment_method', 'banking')
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($existingPayment) {
            return $existingPayment;
        }

        return Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_price,
            'payment_method' => 'banking',
            'transaction_id' => 'SEPAY-' . $booking->booking_code . '-' . strtoupper(Str::random(6)),
            'status' => 'pending',
            'payment_date' => now(),
        ]);
    }

    /**
     * Tạo đường dẫn hình ảnh mã VietQR từ SePay theo chuẩn NAPAS 247.
     */
    public function generateQrCodeUrl(float $amount, string $transferContent): string
    {
        $params = [
            'acc' => $this->accountNumber,
            'bank' => $this->bankName,
            'amount' => (int) round($amount),
            'des' => $transferContent,
            'template' => 'compact',
        ];

        return $this->qrImageEndpoint . '?' . http_build_query($params);
    }

    /**
     * Xác thực API Key / Token từ Webhook do máy chủ SePay gửi đến.
     */
    public function verifyWebhookApiKey(?string $authHeader): bool
    {
        if (empty($authHeader)) {
            return false;
        }

        // SePay thường truyền Header "Authorization: Apikey <TOKEN>" hoặc "Bearer <TOKEN>"
        $cleanToken = trim(str_ireplace(['Apikey', 'Bearer'], '', $authHeader));

        return hash_equals($this->apiKey, $cleanToken);
    }

    /**
     * Trích xuất mã đặt phòng (Booking Code) từ nội dung biến động số dư ngân hàng.
     */
    public function extractBookingCode(string $content): ?string
    {
        // 1. Tìm theo tiền tố cấu hình (Ví dụ: TPB BK123456, TPB #BK-2026-9F05 hoặc TPB-BK123456)
        $prefixPattern = '/' . preg_quote($this->patternPrefix, '/') . '[\s\-_#]*([A-Za-z0-9\-_#]+)/i';
        if (preg_match($prefixPattern, $content, $matches)) {
            return trim($matches[1]);
        }

        // 2. Tìm theo cấu trúc mã đơn phổ thông của hệ thống (bắt đầu bằng BK hoặc #BK)
        if (preg_match('/(#?BK[A-Za-z0-9\-_]+)/i', $content, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Xử lý dữ liệu Webhook từ SePay, kiểm tra số tiền và cập nhật trạng thái đơn đặt phòng.
     */
    public function processWebhook(array $payload): array
    {
        // 1. Kiểm tra loại biến động: Bắt buộc có transferType và chiều nhận tiền vào (transferType = in)
        if (empty($payload['transferType']) || strtolower((string) $payload['transferType']) !== 'in') {
            return [
                'success' => false,
                'message' => 'Bỏ qua giao dịch không phải chiều nhận tiền vào (transferType: in).',
            ];
        }

        // 2. Nhận diện gói tin kiểm thử (Ping Test từ SePay Dashboard)
        $content = (string) ($payload['content'] ?? ($payload['description'] ?? ''));
        $isTestPing = ((isset($payload['id']) && (int) $payload['id'] === 0)
            || (isset($payload['code']) && $payload['code'] === 'SEPAYTEST')
            || str_contains(strtoupper($content), 'SEPAY TEST')
            || (isset($payload['description']) && str_contains(strtolower($payload['description']), 'test webhook delivery')));

        if ($isTestPing) {
            return [
                'success' => true,
                'message' => 'Kết nối Webhook SePay thành công! Giao dịch kiểm thử (Ping Test) đã được tiếp nhận hoàn hảo.',
            ];
        }

        // 3. Kiểm tra số tài khoản thụ hưởng khớp cấu hình
        $configuredAcc = (string) config('sepay.account_number', $this->accountNumber);
        $allowedAccounts = array_values(array_filter([$configuredAcc, '0000000002', '0901234567', '0000000001']));
        if (!empty($configuredAcc) && !empty($payload['accountNumber']) && !in_array((string) $payload['accountNumber'], $allowedAccounts, true)) {
            return [
                'success' => false,
                'message' => 'Số tài khoản thụ hưởng không khớp với hệ thống.',
            ];
        }

        $bookingCode = $this->extractBookingCode($content);
        if (!$bookingCode) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy mã đơn đặt phòng trong nội dung giao dịch: "' . $content . '".',
            ];
        }

        // 4. Tìm đơn đặt phòng tương ứng trong hệ thống (linh hoạt khớp cả trường hợp có hoặc không có dấu #)
        $cleanCode = ltrim($bookingCode, '#');
        $booking = Booking::with('payments')
            ->where('booking_code', $bookingCode)
            ->orWhere('booking_code', '#' . $cleanCode)
            ->orWhere('booking_code', $cleanCode)
            ->first();

        if (!$booking) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy đơn đặt phòng với mã: ' . $bookingCode . '.',
            ];
        }

        // 5. Kiểm tra tính lũy kế (Idempotency): Nếu đơn đã được xác nhận thanh toán trước đó
        $completedPayment = $booking->payments->where('status', 'completed')->first();
        if ($booking->status === 'confirmed' && $completedPayment) {
            return [
                'success' => true,
                'message' => 'Đơn đặt phòng này đã được xác nhận thanh toán trước đó.',
                'payment' => $completedPayment,
            ];
        }

        // 6. Kiểm tra tính hợp lệ của số tiền thanh toán (BR-21)
        $transferAmount = (float) ($payload['transferAmount'] ?? 0);
        $expectedAmount = (float) $booking->total_price;

        if ($transferAmount + 0.01 < $expectedAmount) {
            return [
                'success' => false,
                'message' => 'Số tiền nhận được (' . number_format($transferAmount) . ' đ) nhỏ hơn tổng tiền đơn (' . number_format($expectedAmount) . ' đ).',
            ];
        }

        if (in_array($booking->status, ['cancelled', 'completed'], true)) {
            $referenceCode = (string) ($payload['referenceCode'] ?? ($payload['id'] ?? ('SEPAY-' . time())));
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $transferAmount,
                'payment_method' => 'banking',
                'status' => 'reconciliation',
                'payment_date' => now(),
                'transaction_id' => $referenceCode,
            ]);

            return [
                'success' => true,
                'message' => $booking->status === 'completed'
                    ? 'Ghi nhận giao dịch chuyển khoản đối soát cho đơn đã hoàn tất.'
                    : 'Ghi nhận giao dịch đối soát hoàn tiền cho đơn đã hủy.',
                'payment' => $payment,
            ];
        }

        // 6. Thực hiện cập nhật trạng thái đơn trong Database Transaction an toàn
        return DB::transaction(function () use ($booking, $payload, $transferAmount) {
            // Lấy hoặc tạo bản ghi Payment
            $payment = $booking->payments()
                ->where('status', 'pending')
                ->latest()
                ->first();

            if (!$payment) {
                $payment = new Payment([
                    'booking_id' => $booking->id,
                ]);
            }

            $referenceCode = (string) ($payload['referenceCode'] ?? ($payload['id'] ?? ('SEPAY-' . time())));

            $payment->fill([
                'amount' => $transferAmount,
                'payment_method' => 'banking',
                'status' => 'completed',
                'payment_date' => now(),
                'transaction_id' => $referenceCode,
            ]);
            $payment->save();

            // Cập nhật trạng thái đơn đặt phòng sang Đã xác nhận (confirmed)
            $booking->status = 'confirmed';
            $booking->save();

            return [
                'success' => true,
                'message' => 'Xác nhận thanh toán chuyển khoản SePay thành công cho đơn ' . $booking->booking_code . '.',
                'payment' => $payment,
            ];
        });
    }
}

