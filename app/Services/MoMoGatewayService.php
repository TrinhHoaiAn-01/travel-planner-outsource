<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Interfaces\MoMoGatewayServiceInterface;
use App\Services\Interfaces\PaymentServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MoMoGatewayService implements MoMoGatewayServiceInterface
{
    private string $partnerCode;
    private string $accessKey;
    private string $secretKey;
    private string $endpoint;

    /**
     * Khởi tạo MoMoGatewayService với các khóa cấu hình chuẩn MoMo API V2.
     */
    public function __construct(
        private PaymentServiceInterface $paymentService
    ) {
        $this->partnerCode = (string) config('momo.partner_code', 'MOMOBKUN20180529');
        $this->accessKey = (string) config('momo.access_key', 'klm05TvNBzhg7h7j');
        $this->secretKey = (string) config('momo.secret_key', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2Ac');
        $this->endpoint = (string) config('momo.endpoint', 'https://test-payment.momo.vn/v2/gateway/api/create');
    }

    /**
     * Tạo yêu cầu thanh toán sang cổng MoMo và nhận URL thanh toán (payUrl).
     */
    public function createPaymentUrl(Booking $booking): array
    {
        // 1. Kiểm tra trạng thái đơn: Không thể thanh toán nếu đơn đã bị hủy
        if ($booking->status === 'cancelled') {
            throw ValidationException::withMessages([
                'booking' => 'Không thể thực hiện thanh toán cho đơn đặt phòng đã bị hủy.',
            ]);
        }

        // 2. Chuẩn bị các trường dữ liệu theo quy chuẩn MoMo V2
        $orderId = 'MOMO_' . $booking->booking_code . '_' . time();
        $requestId = (string) Str::uuid();
        $amount = (int) round($booking->total_price);
        $orderInfo = 'Thanh toan don dat phong #' . $booking->booking_code;
        $redirectUrl = route('payments.momo.callback');
        $ipnUrl = route('payments.momo.ipn');
        $extraData = base64_encode(json_encode(['booking_id' => $booking->id]));
        $requestType = 'captureWallet';

        // 3. Ghép chuỗi ký tự thô theo bảng chữ cái để tạo mã băm HMAC-SHA256
        $rawHash = "accessKey={$this->accessKey}&amount={$amount}&extraData={$extraData}&ipnUrl={$ipnUrl}&orderId={$orderId}&orderInfo={$orderInfo}&partnerCode={$this->partnerCode}&redirectUrl={$redirectUrl}&requestId={$requestId}&requestType={$requestType}";

        // 4. Ký số bằng thuật toán HMAC-SHA256 với Secret Key được MoMo cấp
        $signature = hash_hmac('sha256', $rawHash, $this->secretKey);

        $payload = [
            'partnerCode' => $this->partnerCode,
            'partnerName' => 'Travel Planner Outsource',
            'storeId' => 'TravelPlannerStore',
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature,
        ];

        try {
            // Gửi HTTP POST request tới cổng MoMo Sandbox (không xác minh SSL nghiêm ngặt trên môi trường dev)
            $response = Http::withoutVerifying()
                ->timeout(8)
                ->post($this->endpoint, $payload);

            if ($response->successful()) {
                $result = $response->json();

                // Mã resultCode = 0 nghĩa là MoMo đã tiếp nhận và sinh URL thanh toán thành công
                if (isset($result['resultCode']) && (int) $result['resultCode'] === 0) {
                    return [
                        'success' => true,
                        'payUrl' => $result['payUrl'] ?? null,
                        'qrCodeUrl' => $result['qrCodeUrl'] ?? null,
                        'orderId' => $orderId,
                        'message' => 'Khởi tạo giao dịch MoMo thành công.',
                    ];
                }

                return [
                    'success' => false,
                    'payUrl' => null,
                    'orderId' => $orderId,
                    'message' => $result['message'] ?? 'Cổng MoMo từ chối yêu cầu khởi tạo giao dịch.',
                ];
            }

            return [
                'success' => false,
                'payUrl' => null,
                'orderId' => $orderId,
                'message' => 'Máy chủ MoMo Sandbox phản hồi lỗi (Mã HTTP: ' . $response->status() . ').',
            ];
        } catch (\Throwable $e) {
            Log::warning('Không thể kết nối đến cổng MoMo Sandbox: ' . $e->getMessage());

            return [
                'success' => false,
                'payUrl' => null,
                'orderId' => $orderId,
                'message' => 'Không thể kết nối tới máy chủ MoMo Sandbox (Kiểm tra kết nối Internet): ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Xác thực chữ ký số HMAC-SHA256 của MoMo gửi về qua Callback hoặc IPN.
     */
    public function verifySignature(array $data): bool
    {
        $signature = $data['signature'] ?? '';
        if (empty($signature)) {
            return false;
        }

        // Định dạng chuỗi xác thực Callback quy chuẩn của MoMo V2:
        // accessKey=$accessKey&amount=$amount&extraData=$extraData&message=$message&orderId=$orderId&orderInfo=$orderInfo&orderType=$orderType&partnerCode=$partnerCode&payType=$payType&requestId=$requestId&responseTime=$responseTime&resultCode=$resultCode&transId=$transId
        $rawHash = 'accessKey=' . $this->accessKey .
            '&amount=' . ($data['amount'] ?? '') .
            '&extraData=' . ($data['extraData'] ?? '') .
            '&message=' . ($data['message'] ?? '') .
            '&orderId=' . ($data['orderId'] ?? '') .
            '&orderInfo=' . ($data['orderInfo'] ?? '') .
            '&orderType=' . ($data['orderType'] ?? '') .
            '&partnerCode=' . ($data['partnerCode'] ?? '') .
            '&payType=' . ($data['payType'] ?? '') .
            '&requestId=' . ($data['requestId'] ?? '') .
            '&responseTime=' . ($data['responseTime'] ?? '') .
            '&resultCode=' . ($data['resultCode'] ?? '') .
            '&transId=' . ($data['transId'] ?? '');

        $calculatedSignature = hash_hmac('sha256', $rawHash, $this->secretKey);

        return hash_equals($calculatedSignature, $signature);
    }

    /**
     * Xử lý kết quả trả về từ MoMo, cập nhật giao dịch thanh toán và đơn đặt phòng.
     */
    public function processCallback(array $callbackData): Payment
    {
        $resultCode = (int) ($callbackData['resultCode'] ?? -1);
        $orderId = $callbackData['orderId'] ?? null;
        $transId = $callbackData['transId'] ?? ('MOMO_' . time());
        $amount = (float) ($callbackData['amount'] ?? 0);

        // Giải mã extraData để xác định booking_id
        $bookingId = null;
        if (!empty($callbackData['extraData'])) {
            $extra = json_decode(base64_decode($callbackData['extraData']), true);
            $bookingId = $extra['booking_id'] ?? null;
        }

        // Nếu extraData không có hoặc bị lỗi, phân tích chuỗi orderId dạng MOMO_{booking_code}_{timestamp}
        if (!$bookingId && $orderId) {
            $parts = explode('_', $orderId);
            if (count($parts) >= 2) {
                $bookingCode = $parts[1];
                $booking = Booking::where('booking_code', $bookingCode)->first();
                $bookingId = $booking?->id;
            }
        }

        $booking = Booking::findOrFail($bookingId);

        // resultCode = 0 là giao dịch thanh toán thành công
        $status = ($resultCode === 0) ? 'completed' : 'failed';

        return DB::transaction(function () use ($bookingId, $amount, $transId, $status) {
            $lockedBooking = Booking::where('id', $bookingId)->lockForUpdate()->firstOrFail();

            if ($status === 'completed') {
                $lockedBooking->update([
                    'status' => 'confirmed',
                ]);
            }

            $payment = Payment::create([
                'booking_id' => $lockedBooking->id,
                'amount' => $amount > 0 ? $amount : $lockedBooking->total_price,
                'payment_method' => 'momo',
                'transaction_id' => (string) $transId,
                'status' => $status,
                'payment_date' => now(),
            ]);

            return $payment;
        }, 5);
    }
}
