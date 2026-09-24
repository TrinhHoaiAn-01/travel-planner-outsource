<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'image'];

    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
