<?php

namespace Search\HomeSearch\Solr;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\Review;
use App\Models\RoomType;
use App\Models\Trip;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\BookingService;
use App\Services\CategoryManagementService;
use App\Services\CityManagementService;
use App\Services\CommerceBackendService;
use App\Services\DestinationService;
use App\Services\PaymentService;
use App\Services\ReviewModerationService;
use App\Services\RoomTypeManagementService;
use App\Services\TripService;
use App\Services\UserManagementService;
use Illuminate\Support\Str;

/**
 * Lớp Định dạng Gợi ý Tìm kiếm Solr (Solr Suggestion Formatter).
 * Thuộc Vendor Package: Search\HomeSearch\Solr
 *
 * Nhiệm vụ:
 * 1. Tiếp nhận yêu cầu gợi ý từ khóa (Autocomplete Suggestions) khi người dùng đang gõ phím.
 * 2. Tái sử dụng các phương thức lọc và phân quyền bảo mật có sẵn trong từng Service nghiệp vụ.
 * 3. Chuẩn hóa kết quả đầu ra thành cấu trúc thống nhất: ['label' => ..., 'description' => ..., 'value' => ...].
 * 4. Hỗ trợ hiển thị gợi ý cho cả người dùng công khai (Điểm đến, Thành phố) và tài khoản Admin/Cá nhân (Chuyến đi, Đơn hàng...).
 */
class SolrSuggestionFormatter
{
    /**
     * Lấy danh sách tối đa 6 gợi ý tìm kiếm theo thực thể và từ khóa.
     *
     * @param string $type
     * @param string $keyword
     * @param User|null $user
     * @param array<string, mixed> $filters
     * @return array<int, array{label: string, description: string, value: string}>
     */
    public function format(string $type, string $keyword, ?User $user, array $filters = []): array
    {
        $cleanKeyword = trim($keyword);

        if ($cleanKeyword === '') {
            return [];
        }

        // Trường hợp đặc biệt: Tìm kiếm khách sạn cho thương gia (Merchant Property Search)
        if ($type === 'property') {
            $listings = (new CommerceBackendService($user))->execute('search_listings', [
                'query' => $cleanKeyword,
                'limit' => 6,
            ]);

            return array_map(fn ($listing) => [
                'label'       => Str::limit(strip_tags((string) $listing['title']), 100),
                'description' => strip_tags((string) ($listing['attributes']['city'] ?? '')),
                'value'       => mb_substr(strip_tags((string) $listing['title']), 0, 200),
            ], $listings);
        }

        $filters['search'] = $cleanKeyword;

        // Tái sử dụng bộ lọc nghiệp vụ của từng Service
        $results = match ($type) {
            'destination'  => app(DestinationService::class)->getDestinations($filters, 6),
            'city'         => app(CityManagementService::class)->getCities($filters, 6),
            'category'     => app(CategoryManagementService::class)->getCategories($filters, 6),
            'trip'         => app(TripService::class)->getUserTrips($user ? $user->id : 0, $filters, 6),
            'user'         => app(UserManagementService::class)->getUsers($filters, 6),
            'booking'      => app(BookingService::class)->getAllBookings($filters['status'] ?? null, $cleanKeyword, 6),
            'payment'      => app(PaymentService::class)->getAllPayments($filters['status'] ?? null, $cleanKeyword, 6),
            'activity_log' => app(ActivityLogService::class)->getActivityLogs($filters, 6),
            'review'       => app(ReviewModerationService::class)->getReviews($filters, 6),
            'room_type'    => app(RoomTypeManagementService::class)->getRoomTypes($filters, 6),
            default        => null,
        };

        if ($results === null) {
            return [];
        }

        return $results->getCollection()->map(function ($model) {
            [$label, $description, $value] = match (true) {
                $model instanceof Destination => [$model->name, $model->city?->name, $model->name],
                $model instanceof City        => [$model->name, $model->region, $model->name],
                $model instanceof Category    => [$model->name, $model->slug, $model->name],
                $model instanceof Trip        => [$model->name, $model->start_date?->format('d/m/Y'), $model->name],
                $model instanceof User        => [$model->name, $model->email, $model->name],
                $model instanceof Booking     => [$model->booking_code, $model->user?->name, $model->booking_code],
                $model instanceof Payment     => [
                    $model->transaction_id ?: ($model->booking?->booking_code ?: $model->payment_method),
                    $model->payment_method,
                    $model->transaction_id ?: ($model->booking?->booking_code ?: $model->payment_method),
                ],
                $model instanceof ActivityLog => [$model->action, $model->ip_address, $model->action],
                $model instanceof Review      => [$model->comment, $model->destination?->name, $model->comment],
                $model instanceof RoomType    => [$model->name, $model->destination?->name, $model->name],
                default                       => ['', '', ''],
            };

            return [
                'label'       => Str::limit(strip_tags((string) $label), 100),
                'description' => Str::limit(strip_tags((string) $description), 100),
                'value'       => mb_substr(strip_tags((string) $value), 0, 200),
            ];
        })->values()->all();
    }
}
