<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommerceCampaign extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'created_by', 'name', 'status', 'budget', 'details'];
    protected function casts(): array
    {
        return ['details' => 'array', 'budget' => 'decimal:2'];
    }
}
