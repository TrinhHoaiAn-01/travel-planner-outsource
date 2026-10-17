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

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Kiểm tra người dùng có vai trò thông thường (user) hay không.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Kiểm tra người dùng có một trong các vai trò được chỉ định hay không.
     *
     * @param  string|array<int, string>  $roles
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            $roles = array_map('trim', explode(',', $roles));
        }

        return in_array($this->role, $roles, true);
    }

    /**
     * Kiểm tra người dùng có đang ở trạng thái hoạt động hay không.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
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
