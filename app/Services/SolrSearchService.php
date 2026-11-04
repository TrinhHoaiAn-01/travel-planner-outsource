<?php

namespace App\Services;

use App\Exceptions\SearchUnavailableException;
use App\Models\{ActivityLog, Booking, Category, City, Destination, Payment, Review, RoomType, Trip, User};
use App\Services\Interfaces\SearchServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SolrSearchService implements SearchServiceInterface
{
    private const MODELS = [
        'destination' => Destination::class, 'city' => City::class, 'category' => Category::class,
        'trip' => Trip::class, 'user' => User::class, 'booking' => Booking::class,
        'payment' => Payment::class, 'activity_log' => ActivityLog::class, 'review' => Review::class,
    ];

    public function apply(Builder $query, string $type, string $keyword, ?int $ownerId = null): Builder
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return $query;
        }
        if (! isset(self::MODELS[$type])) {
            throw new \InvalidArgumentException('Unknown search entity.');
        }

        // Chỉ truyền từ khóa thuần văn bản, không cho phép cú pháp truy vấn Solr từ người dùng.
        preg_match_all('/[\p{L}\p{N}]+/u', mb_substr($keyword, 0, 200), $tokens);
        if ($tokens[0] === []) {
            return $query->whereRaw('1 = 0');
        }
        $filters = ['entity_type:'.$type];
        if ($ownerId !== null) {
            $filters[] = 'owner_id:'.$ownerId;
        }
        $identifiers = [];
        $cursor = '*';
        try {
            do {
                $response = Http::timeout(10)->acceptJson()->get(config('search.solr_url').'/select', [
                    'q' => implode(' AND ', array_map(fn ($token) => '"'.$token.'"', $tokens[0])),
                    'defType' => 'edismax', 'qf' => 'search_text', 'uf' => '-*',
                    'fq' => implode(' AND ', $filters), 'fl' => 'record_id', 'sort' => 'id asc',
                    'rows' => 1000, 'cursorMark' => $cursor, 'wt' => 'json',
                ])->throw();
                $payload = $response->json();
                if (! is_array($payload['response']['docs'] ?? null) ||
                    ! is_string($payload['nextCursorMark'] ?? null) ||
                    ($payload['responseHeader']['partialResults'] ?? false)) {
                    throw new \UnexpectedValueException('Invalid or partial Solr response.');
                }
                foreach ($payload['response']['docs'] as $document) {
                    if (! isset($document['record_id']) || ! is_numeric($document['record_id'])) {
                        throw new \UnexpectedValueException('Invalid Solr record identifier.');
                    }
                    $identifiers[] = (int) $document['record_id'];
                }
                $nextCursor = $payload['nextCursorMark'];
                $finished = $nextCursor === $cursor;
                $cursor = $nextCursor;
            } while (! $finished);
        } catch (\Throwable $exception) {
            Log::warning('Không thể tìm kiếm Solr.', ['entity_type' => $type]);
            throw new SearchUnavailableException($exception);
        }

        // Dữ liệu và quyền truy cập luôn được kiểm tra lại trong database.
        return $query->whereIntegerInRaw($query->getModel()->getQualifiedKeyName(), array_unique($identifiers));
    }

    private function type(Model $model): string
    {
        $type = array_search($model::class, self::MODELS, true);
        if ($type === false) {
            throw new \InvalidArgumentException('Unknown search model.');
        }

        return $type;
    }

    public function suggestions(string $type, string $keyword, ?User $user, array $filters = []): array
    {
        if (trim($keyword) === '') {
            return [];
        }
        if ($type === 'property') {
            $listings = (new CommerceBackendService($user))->execute('search_listings', ['query' => $keyword, 'limit' => 6]);

            return array_map(fn ($listing) => [
                'label' => Str::limit(strip_tags($listing['title']), 100),
                'description' => strip_tags($listing['attributes']['city'] ?? ''),
                'value' => mb_substr(strip_tags($listing['title']), 0, 200),
            ], $listings);
        }
        $filters['search'] = $keyword;
        // Dùng lại bộ lọc và phân quyền dữ liệu của từng danh sách hiện có.
        $results = match ($type) {
            'destination' => app(DestinationService::class)->getDestinations($filters, 6),
            'city' => app(CityManagementService::class)->getCities($filters, 6),
            'category' => app(CategoryManagementService::class)->getCategories($filters, 6),
            'trip' => app(TripService::class)->getUserTrips($user->id, $filters, 6),
            'user' => app(UserManagementService::class)->getUsers($filters, 6),
            'booking' => app(BookingService::class)->getAllBookings($filters['status'] ?? null, $keyword, 6),
            'payment' => app(PaymentService::class)->getAllPayments($filters['status'] ?? null, $keyword, 6),
            'activity_log' => app(ActivityLogService::class)->getActivityLogs($filters, 6),
            'review' => app(ReviewModerationService::class)->getReviews($filters, 6),
        };

        return $results->getCollection()->map(function ($model) {
            [$label, $description, $value] = match (true) {
                $model instanceof Destination => [$model->name, $model->city?->name, $model->name],
                $model instanceof City => [$model->name, $model->region, $model->name],
                $model instanceof Category => [$model->name, $model->slug, $model->name],
                $model instanceof Trip => [$model->name, $model->start_date?->format('d/m/Y'), $model->name],
                $model instanceof User => [$model->name, $model->email, $model->name],
                $model instanceof Booking => [$model->booking_code, $model->user?->name, $model->booking_code],
                $model instanceof Payment => [$model->transaction_id ?: ($model->booking?->booking_code ?: $model->payment_method),
                    $model->payment_method, $model->transaction_id ?: ($model->booking?->booking_code ?: $model->payment_method)],
                $model instanceof ActivityLog => [$model->action, $model->ip_address, $model->action],
                $model instanceof Review => [$model->comment, $model->destination?->name, $model->comment],
            };

            return ['label' => Str::limit(strip_tags((string) $label), 100),
                'description' => Str::limit(strip_tags((string) $description), 100),
                'value' => mb_substr(strip_tags((string) $value), 0, 200)];
        })->values()->all();
    }

    private function document(Model $model): array
    {
        // Danh sách trường tường minh; không lập chỉ mục mật khẩu, token hoặc metadata.
        $text = match (true) {
            $model instanceof Destination => $this->destinationText($model),
            $model instanceof City => [$model->name, $model->code, $model->region],
            $model instanceof Category => [$model->name, $model->slug, $model->description],
            $model instanceof Trip => [$model->name, $model->description],
            $model instanceof User => [$model->name, $model->email, $model->phone],
            $model instanceof Booking => $this->bookingText($model),
            $model instanceof Payment => $this->paymentText($model),
            $model instanceof ActivityLog => $this->activityText($model),
            $model instanceof Review => $this->reviewText($model),
        };
        $type = $this->type($model);
        $document = [
            'id' => $type.':'.$model->getKey(), 'record_id' => (int) $model->getKey(),
            'entity_type' => $type,
            'search_text' => array_values(array_map(fn ($value) => strip_tags((string) $value), $text)),
        ];
        if ($model instanceof Trip) {
            $document['owner_id'] = (int) $model->user_id;
        }

        return $document;
    }

    private function destinationText(Destination $destination): array
    {
        $destination->load(['city', 'category']);

        return [$destination->name, $destination->description, $destination->address,
            $destination->city?->name, $destination->category?->name, 'destination:'.$destination->id];
    }

    private function bookingText(Booking $booking): array
    {
        $booking->load(['user', 'roomType.destination']);

        return [$booking->booking_code, $booking->user?->name, $booking->user?->email,
            $booking->roomType?->destination?->name];
    }

    private function paymentText(Payment $payment): array
    {
        $payment->load('booking.user');

        return [$payment->transaction_id, $payment->payment_method, $payment->booking?->booking_code,
            $payment->booking?->user?->name, $payment->booking?->user?->email];
    }

    private function activityText(ActivityLog $activity): array
    {
        $activity->load('user');

        return [$activity->action, $activity->ip_address, $activity->user?->name, $activity->user?->email];
    }

    private function reviewText(Review $review): array
    {
        $review->load(['user', 'destination']);

        return [$review->comment, $review->user?->name, $review->user?->email, $review->destination?->name];
    }

    private function update(array $payload): void
    {
        $response = Http::timeout(30)->post(config('search.solr_url').'/update?commit=true', $payload)->throw();
        if ($response->json('responseHeader.status') !== 0) {
            throw new \UnexpectedValueException('Solr rejected the index update.');
        }
    }

    public function index(Model $model): void
    {
        $this->update([$this->document($model)]);
    }

    public function remove(Model $model): void
    {
        $this->update(['delete' => ['id' => $this->type($model).':'.$model->getKey()]]);
    }

    public function rebuild(): int
    {
        $this->update(['delete' => ['query' => '*:*']]);
        $count = 0;
        foreach (self::MODELS as $modelClass) {
            $query = $modelClass::query();
            if ($modelClass === User::class) {
                $query->withTrashed();
            }
            $query->chunkById(100, function ($models) use (&$count) {
                $documents = $models->map(fn ($model) => $this->document($model))->all();
                $this->update($documents);
                $count += count($documents);
            });
        }

        return $count;
    }

    private function indexQuery(Builder $query): void
    {
        $query->chunkById(100, function ($models) {
            $this->update($models->map(fn ($model) => $this->document($model))->all());
        });
    }

    public function synchronize(Model $model, bool $deleted = false): void
    {
        if (! $model instanceof RoomType) {
            if ($deleted && ! ($model instanceof User && $model->exists)) {
                $this->remove($model);
            } else {
                $this->index($model);
            }
        }
        // Đồng bộ các bản ghi tìm kiếm theo thông tin của quan hệ vừa thay đổi.
        if ($model instanceof City || $model instanceof Category) {
            $this->indexQuery(Destination::where($model instanceof City ? 'city_id' : 'category_id', $model->id));
        }
        if ($model instanceof User) {
            $this->indexQuery(Booking::where('user_id', $model->id));
            $this->indexQuery(Payment::whereHas('booking', fn ($query) => $query->where('user_id', $model->id)));
            $this->indexQuery(Review::where('user_id', $model->id));
            $this->indexQuery(ActivityLog::where('user_id', $model->id));
        }
        if ($model instanceof Destination) {
            $this->indexQuery(Booking::whereHas('roomType', fn ($query) => $query->where('destination_id', $model->id)));
            $this->indexQuery(Review::where('destination_id', $model->id));
        }
        if ($model instanceof Booking) {
            $this->indexQuery(Payment::where('booking_id', $model->id));
        }
        if ($model instanceof RoomType) {
            $this->indexQuery(Booking::where('room_type_id', $model->id));
        }
    }
}
