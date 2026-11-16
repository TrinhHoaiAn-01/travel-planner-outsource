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
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use UnexpectedValueException;

/**
 * Lớp Đồng bộ Chỉ mục Solr (Solr Index Synchronizer).
 * Thuộc Vendor Package: Search\HomeSearch\Solr
 *
 * Nhiệm vụ:
 * 1. Đẩy dữ liệu Model vào Apache Solr khi tạo mới hoặc cập nhật.
 * 2. Xóa dữ liệu khỏi Solr khi bản ghi bị xóa khỏi CSDL.
 * 3. Đồng bộ phân cấp theo quan hệ (Cascading Sync): ví dụ khi sửa tên Thành phố, tự động cập nhật lại các Điểm đến thuộc thành phố đó.
 * 4. Tái xây dựng toàn bộ chỉ mục (Rebuild Index) theo từng lô (Batch / Chunk 100 bản ghi) để tránh tràn bộ nhớ RAM.
 */
class SolrIndexSynchronizer
{
    public function __construct(
        private readonly SolrDocumentBuilder $documentBuilder
    ) {
    }

    /**
     * Gửi yêu cầu cập nhật hoặc xóa dữ liệu tới endpoint /update?commit=true của Solr.
     *
     * @param array<string, mixed> $payload
     * @throws UnexpectedValueException
     */
    public function postUpdate(array $payload): void
    {
        $solrUpdateUrl = config('search.solr_url') . '/update?commit=true';

        $response = Http::timeout(30)->post($solrUpdateUrl, $payload)->throw();

        if ($response->json('responseHeader.status') !== 0) {
            throw new UnexpectedValueException('Máy chủ Solr từ chối yêu cầu cập nhật chỉ mục.');
        }
    }

    /**
     * Thêm mới hoặc cập nhật chỉ mục cho một bản ghi Model trong Solr.
     */
    public function index(Model $model): void
    {
        $document = $this->documentBuilder->build($model);
        $this->postUpdate([$document]);
    }

    /**
     * Xóa chỉ mục của một bản ghi Model khỏi Solr.
     */
    public function remove(Model $model): void
    {
        $entityType = $this->documentBuilder->getEntityType($model);
        $documentId = $entityType . ':' . $model->getKey();

        $this->postUpdate([
            'delete' => ['id' => $documentId],
        ]);
    }

    /**
     * Đồng bộ lại một nhóm các bản ghi theo câu truy vấn Eloquent Builder.
     */
    public function indexQuery(Builder $query): void
    {
        $query->chunkById(100, function ($models) {
            $documents = $models->map(fn ($model) => $this->documentBuilder->build($model))->all();
            $this->postUpdate($documents);
        });
    }

    /**
     * Xóa sạch toàn bộ chỉ mục hiện tại và lập chỉ mục lại tất cả các bảng từ CSDL sang Solr.
     *
     * @return int Tổng số lượng bản ghi đã được đồng bộ vào Solr
     */
    public function rebuild(): int
    {
        // 1. Gửi lệnh xóa sạch mọi tài liệu trong core Solr
        $this->postUpdate([
            'delete' => ['query' => '*:*'],
        ]);

        $totalIndexedCount = 0;

        // 2. Lặp qua tất cả các lớp Model đã đăng ký để lập chỉ mục theo từng lô 100 bản ghi
        foreach (SolrDocumentBuilder::MODEL_MAPPING as $modelClass) {
            $query = $modelClass::query();

            // Nếu là User, bao gồm cả những tài khoản đã soft-deleted để Admin vẫn tìm kiếm được khi cần
            if ($modelClass === User::class) {
                $query->withTrashed();
            }

            $query->chunkById(100, function ($models) use (&$totalIndexedCount) {
                $documents = $models->map(fn ($model) => $this->documentBuilder->build($model))->all();
                $this->postUpdate($documents);
                $totalIndexedCount += count($documents);
            });
        }

        return $totalIndexedCount;
    }

    /**
     * Tự động đồng bộ các bản ghi phụ thuộc khi một Model thay đổi hoặc bị xóa.
     */
    public function synchronize(Model $model, bool $isDeleted = false): void
    {
        // 1. Cập nhật hoặc xóa chính bản ghi đó trong Solr
        if (! $model instanceof RoomType) {
            if ($isDeleted && ! ($model instanceof User && $model->exists)) {
                $this->remove($model);
            } else {
                $this->index($model);
            }
        }

        // 2. Đồng bộ các bản ghi có liên quan theo quan hệ ràng buộc:
        // Khi Thành phố hoặc Danh mục thay đổi -> cập nhật lại các Điểm đến liên quan
        if ($model instanceof City || $model instanceof Category) {
            $foreignKey = $model instanceof City ? 'city_id' : 'category_id';
            $this->indexQuery(Destination::where($foreignKey, $model->id));
        }

        // Khi Người dùng thay đổi -> cập nhật lại Booking, Payment, Review, ActivityLog của người dùng đó
        if ($model instanceof User) {
            $this->indexQuery(Booking::where('user_id', $model->id));
            $this->indexQuery(Payment::whereHas('booking', fn ($q) => $q->where('user_id', $model->id)));
            $this->indexQuery(Review::where('user_id', $model->id));
            $this->indexQuery(ActivityLog::where('user_id', $model->id));
        }

        // Khi Điểm đến thay đổi -> cập nhật lại Booking và Review của điểm đến đó
        if ($model instanceof Destination) {
            $this->indexQuery(Booking::whereHas('roomType', fn ($q) => $q->where('destination_id', $model->id)));
            $this->indexQuery(Review::where('destination_id', $model->id));
        }

        // Khi Đơn đặt phòng thay đổi -> cập nhật lại Giao dịch thanh toán
        if ($model instanceof Booking) {
            $this->indexQuery(Payment::where('booking_id', $model->id));
        }

        // Khi Hạng phòng thay đổi -> cập nhật lại các Booking tương ứng
        if ($model instanceof RoomType) {
            $this->indexQuery(Booking::where('room_type_id', $model->id));
        }
    }
}
