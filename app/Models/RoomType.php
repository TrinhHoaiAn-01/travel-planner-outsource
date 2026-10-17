<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = ['destination_id', 'name', 'capacity', 'price_per_night', 'quantity', 'description'];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'price_per_night' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Định dạng giá tiền mỗi đêm theo VND.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->price_per_night, 0, ',', '.') . 'đ';
    }

    /**
     * Hiển thị nhãn sức chứa người lớn.
     */
    public function getCapacityLabelAttribute(): string
    {
        return $this->capacity . ' người lớn';
    }
}
