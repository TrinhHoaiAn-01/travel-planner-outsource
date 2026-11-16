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
     * Tự động thêm các trường tính toán khi serialize sang mảng/JSON.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'images',
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
     * Lấy URL ảnh đại diện đầu tiên của thành phố (tương thích ngược nếu lưu chuỗi hoặc JSON mảng ảnh).
     */
    public function getImageAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $img = $value;
        if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
            $decoded = json_decode($value, true);
            if (is_array($decoded) && ! empty($decoded)) {
                $img = $decoded[0];
            }
        }

        if (! empty($img) && ! str_starts_with($img, 'http') && ! str_starts_with($img, '/')) {
            $img = '/' . $img;
        }

        return $img;
    }

    /**
     * Lấy danh sách toàn bộ hình ảnh của thành phố dưới dạng mảng.
     *
     * @return array<int, string>
     */
    public function getImagesAttribute(): array
    {
        $raw = $this->attributes['image'] ?? null;
        if (empty($raw)) {
            return [];
        }

        $images = [$raw];
        if (str_starts_with($raw, '[') && str_ends_with($raw, ']')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $images = $decoded;
            }
        }

        return array_map(function ($img) {
            if (! empty($img) && ! str_starts_with($img, 'http') && ! str_starts_with($img, '/')) {
                return '/' . $img;
            }
            return $img;
        }, $images);
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
