<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    /**
     * Danh sách các trường được phép gán dữ liệu hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'code',
        'region',
        'description',
        'image',
        'is_active',
    ];

    /**
     * Định dạng kiểu dữ liệu thuộc tính.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Mối quan hệ: Một thành phố có nhiều địa điểm du lịch.
     */
    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class);
    }

    /**
     * Khóa định tuyến mặc định theo slug.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Hỗ trợ tìm nạp bản ghi định tuyến theo cả ID (trong admin) và Slug (ngoài public).
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return parent::resolveRouteBinding($value, $field);
        }

        if (is_numeric($value)) {
            $cityById = $this->where('id', $value)->first();
            if ($cityById) {
                return $cityById;
            }
        }

        return $this->where('slug', $value)->first();
    }
}
