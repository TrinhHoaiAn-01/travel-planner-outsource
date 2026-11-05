<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommercePromotion extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'created_by', 'name', 'listing_ids', 'discount_pct', 'starts', 'ends', 'nights'];
    protected function casts(): array
    {
        return ['listing_ids' => 'array', 'discount_pct' => 'decimal:2', 'starts' => 'date', 'ends' => 'date', 'nights' => 'array'];
    }
}
