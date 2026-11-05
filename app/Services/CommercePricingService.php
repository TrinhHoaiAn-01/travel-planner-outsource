<?php

namespace App\Services;

use App\Models\CommercePromotion;
use App\Models\RoomType;
use Carbon\Carbon;

class CommercePricingService
{
    public function nightlyPrice(RoomType $room, string $date): float
    {
        $day = Carbon::parse($date);
        $promotions = CommercePromotion::whereDate('starts', '<=', $day)->whereDate('ends', '>=', $day)->latest()->get();
        foreach ($promotions as $promotion) {
            if (! in_array('room:'.$room->id, $promotion->listing_ids, true)) {
                continue;
            }
            if ($promotion->nights && ! in_array(strtolower($day->format('D')), $promotion->nights, true)) {
                continue;
            }
            // Chỉ áp dụng khung giá mới nhất, không cộng dồn giảm giá.
            return round((float) $room->price_per_night * (1 - (float) $promotion->discount_pct / 100), 2);
        }
        return (float) $room->price_per_night;
    }

    public function calculateRoomPrice(RoomType $room, string $checkIn, string $checkOut): float
    {
        $start = Carbon::parse($checkIn);
        $end = Carbon::parse($checkOut);
        $total = 0;
        for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
            $total += $this->nightlyPrice($room, $date->format('Y-m-d'));
        }
        return $total;
    }
}
