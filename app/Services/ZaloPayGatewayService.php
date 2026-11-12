<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Interfaces\PaymentServiceInterface;
use App\Services\Interfaces\ZaloPayGatewayServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class ZaloPayGatewayService implements ZaloPayGatewayServiceInterface
{
    private int $appId;
    private string $key1;
    private string $key2;
    private string $endpoint;
    private string $queryEndpoint;

    /**
     * Khởi tạo ZaloPayGatewayService với các cấu hình Sandbox API V2.
     */
    public function __construct(
        private PaymentServiceInterface $paymentService
    ) {
        $this->appId = (int) config('zalopay.app_id', 2553);
        $this->key1 = (string) config('zalopay.key1', 'PcY4iZIKFCIdgZvA6ueMcMHHUbRLYjPL');
        $this->key2 = (string) config('zalopay.key2', 'kLtgPl8YESDmyABkQgeZByOUJsbcpNI2');
        $this->endpoint = (string) config('zalopay.endpoint', 'https://sb-openapi.zalopay.vn/v2/create');
        $this->queryEndpoint = (string) config('zalopay.query_endpoint', 'https://sb-openapi.zalopay.vn/v2/query');
    }

    /**
     * Tạo yêu cầu thanh toán sang cổng ZaloPay V2 và nhận URL thanh toán (order_url).
     */
    public function createPaymentUrl(Booking $booking): array
    {
        // 1. Kiểm tra trạng thái đơn: Không thể thanh toán nếu đơn đã bị hủy
        if ($booking->status === 'cancelled') {
            throw ValidationException::withMessages([
                'booking' => 'Không thể thực hiện thanh toán cho đơn đặt phòng đã bị hủy.',
            ]);
        }

        // 2. Chuẩn bị các tham số giao dịch theo tài liệu ZaloPay V2
        $appTime = (int) round(microtime(true) * 1000);
        // Định dạng app_trans_id: YYMMDD_xxxx
        $appTransId = date('ymd') . '_' . $booking->booking_code . '_' . time();
        $amount = (int) round($booking->total_price);
        $appUser = 'user_' . $booking->user_id;

        $embedData = json_encode([
            'booking_id' => $booking->id,
            'redirecturl' => route('payments.zalopay.callback'),
        ]);

        $items = json_encode([
            [
                'itemid' => (string) $booking->room_type_id,
                'itemname' => $booking->roomType->name ?? 'Phòng nghỉ',
                'itemprice' => $amount,
                'itemquantity' => 1,
            ],
        ]);

        $description = 'TravelPlanner - Thanh toan don dat phong #' . $booking->booking_code;
        $callbackUrl = route('payments.zalopay.callback-ipn');

        // 3. Tạo chữ ký số mac bằng HMAC-SHA256 với Key1
        // Công thức: app_id|app_trans_id|app_user|amount|app_time|embed_data|item
        $hmacInput = $this->appId . '|' . $appTransId . '|' . $appUser . '|' . $amount . '|' . $appTime . '|' . $embedData . '|' . $items;
        $mac = hash_hmac('sha256', $hmacInput, $this->key1);

        $orderPayload = [
            'app_id' => $this->appId,
            'app_user' => $appUser,
            'app_time' => $appTime,
            'amount' => $amount,
            'app_trans_id' => $appTransId,
            'embed_data' => $embedData,
            'item' => $items,
            'description' => $description,
            'bank_code' => '',
            'callback_url' => $callbackUrl,
            'mac' => $mac,
        ];

        try {
            // Gửi HTTP POST request dạng application/x-www-form-urlencoded tới ZaloPay Sandbox
            $response = Http::withoutVerifying()
                ->asForm()
                ->timeout(10)
                ->post($this->endpoint, $orderPayload);

            if ($response->successful()) {
                $result = $response->json();

                // Mã return_code = 1 biểu thị ZaloPay tạo đơn thành công
                if (isset($result['return_code']) && (int) $result['return_code'] === 1) {
                    return [
                        'success' => true,
                        'payUrl' => $result['order_url'] ?? null,
                        'zpTransToken' => $result['zp_trans_token'] ?? null,
                        'orderId' => $appTransId,
                        'message' => 'Khởi tạo đơn hàng ZaloPay thành công.',
                    ];
                }

                return [
                    'success' => false,
                    'payUrl' => null,
                    'message' => $result['return_message'] ?? 'ZaloPay từ chối khởi tạo giao dịch.',
                ];
            }

            return [
                'success' => false,
                'payUrl' => null,
                'message' => 'Không thể kết nối đến máy chủ ZaloPay (HTTP ' . $response->status() . ').',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'payUrl' => null,
                'message' => 'Lỗi kết nối cổng ZaloPay: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Xác thực chữ ký số (Checksum) khi người dùng hoàn tất thanh toán và chuyển hướng về hệ thống.
     */
    public function verifyRedirectChecksum(array $data): bool
    {
        $checksum = $data['checksum'] ?? '';
        if (empty($checksum)) {
            return false;
        }

        // Công thức ZaloPay: appid|apptransid|pmcid|bankcode|amount|discountamount|status
        $dataString = ($data['appid'] ?? '') . '|' .
            ($data['apptransid'] ?? '') . '|' .
            ($data['pmcid'] ?? '') . '|' .
            ($data['bankcode'] ?? '') . '|' .
            ($data['amount'] ?? '') . '|' .
            ($data['discountamount'] ?? '') . '|' .
            ($data['status'] ?? '');

        $calculatedChecksum = hash_hmac('sha256', $dataString, $this->key2);

        return hash_equals(strtolower($calculatedChecksum), strtolower($checksum));
    }

    /**
     * Xác thực chữ ký số MAC khi ZaloPay gửi Webhook Callback (IPN) ngầm tới hệ thống.
     */
    public function verifyCallbackMac(string $dataJson, string $mac): bool
    {
        if (empty($mac) || empty($dataJson)) {
            return false;
        }

        $calculatedMac = hash_hmac('sha256', $dataJson, $this->key2);

        return hash_equals(strtolower($calculatedMac), strtolower($mac));
    }

    /**
     * Xử lý kết quả trả về từ ZaloPay (Return URL hoặc Callback IPN), cập nhật Payment và Booking.
     */
    public function processCallback(array $payload): Payment
    {
        $appTransId = $payload['app_trans_id'] ?? ($payload['apptransid'] ?? null);
        $amount = (float) ($payload['amount'] ?? 0);
        $zpTransId = $payload['zp_trans_id'] ?? ($payload['apptransid'] ?? ('ZALOPAY_' . time()));
        $statusParam = $payload['status'] ?? 1;

        // Tìm đơn đặt phòng tương ứng
        $booking = null;
        if (!empty($payload['embed_data'])) {
            $embed = is_array($payload['embed_data']) ? $payload['embed_data'] : json_decode($payload['embed_data'], true);
            $bookingId = $embed['booking_id'] ?? null;
            if ($bookingId) {
                $booking = Booking::find($bookingId);
            }
        }

        if (!$booking && $appTransId) {
            $parts = explode('_', $appTransId);
            if (count($parts) >= 2) {
                $bookingCode = $parts[1];
                $booking = Booking::where('booking_code', $bookingCode)->first();
            }
        }

        if (!$booking) {
            throw ValidationException::withMessages([
                'booking' => 'Không tìm thấy đơn đặt phòng liên kết với mã giao dịch ZaloPay.',
            ]);
        }

        // Kiểm tra đối soát số tiền khớp Database theo quy tắc chống gian lận BR-21
        if ($amount > 0 && abs($amount - (float) $booking->total_price) >= 0.01) {
            throw ValidationException::withMessages([
                'amount' => 'Số tiền thanh toán từ cổng ZaloPay không khớp với giá trị đơn đặt phòng trong hệ thống (Quy tắc BR-21).',
            ]);
        }

        // Trạng thái thành công: status = 1 (ZaloPay)
        $status = ((int) $statusParam === 1) ? 'completed' : 'failed';

        return DB::transaction(function () use ($booking, $amount, $zpTransId, $status) {
            $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->firstOrFail();

            // Chống xử lý trùng lặp khi người dùng hoặc IPN gọi nhiều lần
            if (($lockedBooking->status === 'confirmed' || $lockedBooking->status === 'completed') && $status === 'completed') {
                $existingPayment = Payment::where('booking_id', $lockedBooking->id)
                    ->where('status', 'completed')
                    ->where('payment_method', 'zalopay')
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

            // Ghi nhận bản ghi thanh toán ZaloPay
            $payment = Payment::create([
                'booking_id' => $lockedBooking->id,
                'amount' => $amount > 0 ? $amount : $lockedBooking->total_price,
                'payment_method' => 'zalopay',
                'transaction_id' => (string) $zpTransId,
                'status' => $status,
                'payment_date' => now(),
            ]);

            return $payment;
        }, 5);
    }
}
