<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'phone',
        'city',
        'bio',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Bỏ qua remember_token do bảng users trong ERD đồ án đã loại bỏ cột này.
     */
    public function getRememberTokenName(): string
    {
        return '';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteDestinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class, 'favorites')->withPivot('created_at');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Mã định danh hiển thị chuẩn ảnh (#USR-xxx).
     */
    public function getUserCodeAttribute(): string
    {
        if ($this->role === 'admin') {
            return '#USR-000';
        }

        return sprintf('#USR-%03d', $this->id);
    }

    /**
     * Nhãn vai trò người dùng (Administrator / Member).
     */
    public function getRoleLabelAttribute(): string
    {
        return $this->role === 'admin' ? 'Administrator' : 'Member';
    }

    /**
     * Nhãn trạng thái tài khoản.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Hoạt động (Active)' : 'Đã khóa (Disabled)';
    }
}
