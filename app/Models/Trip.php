<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use HasFactory;

    // Trạng thái chuyến đi: Bản nháp
    public const STATUS_DRAFT = 'draft';

    // Trạng thái chuyến đi: Đã lên kế hoạch / Sắp tới
    public const STATUS_PLANNED = 'planned';

    // Trạng thái chuyến đi: Đang diễn ra
    public const STATUS_ONGOING = 'ongoing';

    // Trạng thái chuyến đi: Đã hoàn thành
    public const STATUS_COMPLETED = 'completed';

    // Trạng thái chuyến đi: Đã hủy
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'budget',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'budget' => 'decimal:2',
        ];
    }

    /**
     * Tính toán tổng số ngày của chuyến đi (bao gồm cả ngày bắt đầu và kết thúc).
     * Ví dụ: 10/10 đến 15/10 là 6 ngày.
     */
    public function getTotalDaysAttribute(): int
    {
        if (! $this->start_date || ! $this->end_date) {
            return 0;
        }

        return (int) $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Tính toán tổng số đêm của chuyến đi (bằng số ngày trừ 1, tối thiểu 0 đêm).
     * Ví dụ: 6 ngày sẽ có 5 đêm.
     */
    public function getTotalNightsAttribute(): int
    {
        if (! $this->start_date || ! $this->end_date) {
            return 0;
        }

        return max(0, $this->total_days - 1);
    }

    /**
     * Trả về chuỗi thời lượng chuẩn tiếng Việt (ví dụ: '6 ngày 5 đêm' hoặc '1 ngày').
     */
    public function getDurationLabelAttribute(): string
    {
        if (! $this->start_date || ! $this->end_date) {
            return 'Chưa xác định';
        }

        $days = $this->total_days;
        $nights = $this->total_nights;

        if ($nights > 0) {
            return "{$days} ngày {$nights} đêm";
        }

        return "{$days} ngày";
    }

    /**
     * Định dạng khoảng ngày bắt đầu — ngày kết thúc theo chuẩn Việt Nam (dd/mm/yyyy — dd/mm/yyyy).
     */
    public function getDateRangeFormattedAttribute(): string
    {
        if (! $this->start_date || ! $this->end_date) {
            return 'Chưa xác định';
        }

        $start = $this->start_date->format('d/m/Y');
        $end = $this->end_date->format('d/m/Y');

        return "{$start} — {$end}";
    }

    /**
     * Kiểm tra khoảng thời gian ngày bắt đầu và kết thúc có hợp lệ (bắt đầu <= kết thúc).
     */
    public function isDatesValid(): bool
    {
        if (! $this->start_date || ! $this->end_date) {
            return false;
        }

        return $this->start_date->lte($this->end_date);
    }

    /**
     * Kiểm tra chuyến đi có đang diễn ra hôm nay hay không.
     */
    public function isOngoing(): bool
    {
        if ($this->status === self::STATUS_COMPLETED || $this->status === self::STATUS_CANCELLED) {
            return false;
        }

        if (! $this->start_date || ! $this->end_date) {
            return $this->status === self::STATUS_ONGOING;
        }

        return now()->between($this->start_date->startOfDay(), $this->end_date->endOfDay())
            || $this->status === self::STATUS_ONGOING;
    }

    /**
     * Kiểm tra chuyến đi sắp tới (chưa đến ngày khởi hành).
     */
    public function isUpcoming(): bool
    {
        if ($this->status === self::STATUS_COMPLETED || $this->status === self::STATUS_DRAFT) {
            return false;
        }

        if (! $this->start_date) {
            return $this->status === self::STATUS_PLANNED;
        }

        return $this->start_date->isFuture() && $this->status !== self::STATUS_ONGOING;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function itineraryItems(): HasMany
    {
        return $this->hasMany(ItineraryItem::class)
            ->orderBy('day_number')
            ->orderBy('sort_order');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Danh sách các đơn đặt phòng thuộc chuyến đi.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Kiểm tra xem chuyến đi có phải là bản nháp hay không.
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Kiểm tra xem chuyến đi đã được lên kế hoạch hay chưa.
     */
    public function isPlanned(): bool
    {
        return $this->status === self::STATUS_PLANNED;
    }

    /**
     * Kiểm tra xem chuyến đi đã hoàn thành hay chưa.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Lấy nhãn tiếng Việt tương ứng cho trạng thái hiện tại của chuyến đi.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Bản nháp',
            self::STATUS_PLANNED => 'Đã lên kế hoạch',
            self::STATUS_ONGOING => 'Đang diễn ra',
            self::STATUS_COMPLETED => 'Đã hoàn thành',
            self::STATUS_CANCELLED => 'Đã hủy',
            default => 'Chưa xác định',
        };
    }
}
