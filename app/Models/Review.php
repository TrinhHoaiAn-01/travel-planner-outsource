<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_HIDDEN = 'hidden';

    protected $fillable = ['user_id', 'destination_id', 'rating', 'comment', 'status'];

    protected function casts(): array
    {
        return ['rating' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    /**
     * Kiểm tra bài đánh giá có đang ở trạng thái chờ duyệt hay không.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Kiểm tra bài đánh giá đã được phê duyệt công khai hay chưa.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Kiểm tra bài đánh giá có đang bị ẩn hay không.
     */
    public function isHidden(): bool
    {
        return $this->status === self::STATUS_HIDDEN;
    }

    /**
     * Lấy nhãn hiển thị trạng thái bằng tiếng Việt thân thiện với người dùng.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'Đã được duyệt',
            self::STATUS_HIDDEN => 'Đã bị ẩn',
            default => 'Đang chờ duyệt',
        };
    }

    /**
     * Lấy class CSS tương ứng với trạng thái để hiển thị badge giao diện Section 4.3.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'badge-approved',
            self::STATUS_HIDDEN => 'badge-hidden',
            default => 'badge-pending',
        };
    }

    /**
     * Scope lọc các đánh giá đã được duyệt công khai.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope lọc các đánh giá đang chờ kiểm duyệt.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope lọc các đánh giá đã bị ẩn.
     */
    public function scopeHidden($query)
    {
        return $query->where('status', self::STATUS_HIDDEN);
    }
}
