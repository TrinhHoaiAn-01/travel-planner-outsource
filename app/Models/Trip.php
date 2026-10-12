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
