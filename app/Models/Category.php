<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
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
        'description',
        'icon',
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
     * Mối quan hệ: Một danh mục có nhiều địa điểm du lịch.
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
            $categoryById = $this->where('id', $value)->first();
            if ($categoryById) {
                return $categoryById;
            }
        }

        return $this->where('slug', $value)->first();
    }

    /**
     * Lấy cấu hình màu sắc và biểu tượng SVG tương ứng theo danh mục.
     *
     * @return array
     */
    public function getIconMetaAttribute(): array
    {
        $iconKey = strtolower(trim($this->icon ?? ''));
        $slugKey = strtolower(trim($this->slug ?? ''));

        if ($slugKey === 'resort-spa' || in_array($iconKey, ['hotel', 'resort', 'building', 'spa'])) {
            return [
                'type' => 'hotel',
                'bg_color' => '#EFF6FF',
                'icon_color' => '#3B82F6',
                'name' => 'Nghỉ dưỡng & Resort',
            ];
        }

        if ($slugKey === 'bien-dao' || in_array($iconKey, ['beach', 'waves', 'island', 'sea', 'water'])) {
            return [
                'type' => 'waves',
                'bg_color' => '#ECFEFF',
                'icon_color' => '#06B6D4',
                'name' => 'Biển & Đảo',
            ];
        }

        if ($slugKey === 'nui-rung' || in_array($iconKey, ['mountain', 'tree', 'trekking', 'forest'])) {
            return [
                'type' => 'tree',
                'bg_color' => '#ECFDF5',
                'icon_color' => '#10B981',
                'name' => 'Núi Rừng & Trekking',
            ];
        }

        if ($slugKey === 'di-san-van-hoa' || in_array($iconKey, ['culture', 'landmark', 'temple', 'heritage'])) {
            return [
                'type' => 'landmark',
                'bg_color' => '#FEF2F2',
                'icon_color' => '#EF4444',
                'name' => 'Di Sản Văn Hóa',
            ];
        }

        return [
            'type' => 'default',
            'bg_color' => '#F1F5F9',
            'icon_color' => '#64748B',
            'name' => 'Mặc định',
        ];
    }
}
