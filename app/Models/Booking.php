<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = ['user_id', 'trip_id', 'room_type_id', 'room_count', 'check_in_date', 'check_out_date', 'total_price', 'status', 'booking_code'];

    protected function casts(): array
    {
        return [
            'room_count' => 'integer',
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'total_price' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function itineraryItems(): HasMany
    {
        return $this->hasMany(ItineraryItem::class);
    }
}
