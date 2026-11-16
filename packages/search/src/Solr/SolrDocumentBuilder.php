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
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Lớp Xây dựng Tài liệu Tìm kiếm Solr (Solr Document Builder).
 * Thuộc Vendor Package: Search\HomeSearch\Solr
 *
 * Nhiệm vụ:
 * 1. Chuyển đổi các Model Eloquent trong CSDL MySQL thành cấu trúc tài liệu JSON tương thích với Apache Solr.
 * 2. Trích xuất đúng các trường văn bản cho phép tìm kiếm (search_text).
 * 3. Tuyệt đối không lập chỉ mục các thông tin nhạy cảm như mật khẩu, mã token hay thông tin thanh toán bí mật.
 * 4. Tách biệt rõ ràng từng loại thực thể để sinh viên dễ dàng đọc, hiểu và mở rộng thêm các Model mới.
 */
class SolrDocumentBuilder
{
    /**
     * Bản đồ ánh xạ giữa tên thực thể (entity_type) và lớp Model Eloquent tương ứng.
     */
    public const MODEL_MAPPING = [
        'destination'  => Destination::class,
        'city'         => City::class,
        'category'     => Category::class,
        'trip'         => Trip::class,
        'user'         => User::class,
        'booking'      => Booking::class,
        'payment'      => Payment::class,
        'activity_log' => ActivityLog::class,
        'review'       => Review::class,
        'room_type'    => RoomType::class,
    ];

    /**
     * Xác định tên loại thực thể (entity_type) từ một đối tượng Model.
     *
     * @param Model $model
     * @return string
     * @throws InvalidArgumentException
     */
    public function getEntityType(Model $model): string
    {
        $entityType = array_search($model::class, self::MODEL_MAPPING, true);

        if ($entityType === false) {
            throw new InvalidArgumentException('Loại Model tìm kiếm không được hỗ trợ: ' . $model::class);
        }

        return $entityType;
    }

    /**
     * Xây dựng tài liệu Solr hoàn chỉnh từ một Model Eloquent.
     *
     * @param Model $model
     * @return array<string, mixed>
     */
    public function build(Model $model): array
    {
        $entityType = $this->getEntityType($model);

        // Trích xuất các trường văn bản theo từng loại Model cụ thể
        $searchTerms = match (true) {
            $model instanceof Destination => $this->extractDestinationTerms($model),
            $model instanceof City        => $this->extractCityTerms($model),
            $model instanceof Category    => $this->extractCategoryTerms($model),
            $model instanceof Trip        => $this->extractTripTerms($model),
            $model instanceof User        => $this->extractUserTerms($model),
            $model instanceof Booking     => $this->extractBookingTerms($model),
            $model instanceof Payment     => $this->extractPaymentTerms($model),
            $model instanceof ActivityLog => $this->extractActivityLogTerms($model),
            $model instanceof Review      => $this->extractReviewTerms($model),
            $model instanceof RoomType    => $this->extractRoomTypeTerms($model),
            default                       => [],
        };

        // Chuẩn hóa và làm sạch chuỗi (loại bỏ thẻ HTML và khoảng trắng thừa)
        $cleanSearchTerms = array_values(array_filter(array_map(function ($value) {
            return trim(strip_tags((string) $value));
        }, $searchTerms), fn ($term) => $term !== ''));

        $document = [
            'id'          => $entityType . ':' . $model->getKey(),
            'record_id'   => (int) $model->getKey(),
            'entity_type' => $entityType,
            'search_text' => $cleanSearchTerms,
        ];

        // Nếu là chuyến đi (Trip), gán thêm owner_id để phân quyền chỉ người sở hữu mới tìm thấy chuyến đi của mình
        if ($model instanceof Trip) {
            $document['owner_id'] = (int) $model->user_id;
        }

        return $document;
    }

    private function extractDestinationTerms(Destination $destination): array
    {
        return [
            $destination->name,
            $destination->address,
            $destination->description,
            $destination->city?->name,
            $destination->category?->name,
        ];
    }

    private function extractCityTerms(City $city): array
    {
        return [
            $city->name,
            $city->region,
            $city->description,
        ];
    }

    private function extractCategoryTerms(Category $category): array
    {
        return [
            $category->name,
            $category->slug,
            $category->description,
        ];
    }

    private function extractTripTerms(Trip $trip): array
    {
        return [
            $trip->name,
            $trip->description,
            $trip->destination_city,
            $trip->notes,
        ];
    }

    private function extractUserTerms(User $user): array
    {
        // Tuyệt đối không đưa password, remember_token hay dữ liệu nhạy cảm vào Solr
        return [
            $user->name,
            $user->email,
            $user->phone,
            $user->city,
            $user->role,
        ];
    }

    private function extractBookingTerms(Booking $booking): array
    {
        return [
            $booking->booking_code,
            $booking->status,
            $booking->user?->name,
            $booking->user?->email,
            $booking->roomType?->destination?->name ?? ($booking->destination?->name ?? null),
            $booking->roomType?->name,
        ];
    }

    private function extractPaymentTerms(Payment $payment): array
    {
        return [
            $payment->transaction_id,
            $payment->payment_method,
            $payment->status,
            $payment->booking?->booking_code,
            $payment->booking?->user?->name,
            $payment->booking?->user?->email,
        ];
    }

    private function extractActivityLogTerms(ActivityLog $activityLog): array
    {
        return [
            $activityLog->action,
            $activityLog->description,
            $activityLog->ip_address,
            $activityLog->user?->name,
            $activityLog->user?->email,
        ];
    }

    private function extractReviewTerms(Review $review): array
    {
        return [
            $review->comment,
            $review->status,
            $review->destination?->name,
            $review->user?->name,
            $review->user?->email,
        ];
    }

    private function extractRoomTypeTerms(RoomType $roomType): array
    {
        return [
            $roomType->name,
            $roomType->description,
            $roomType->destination?->name,
        ];
    }
}
