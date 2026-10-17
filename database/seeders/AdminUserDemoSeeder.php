<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AdminUserDemoSeeder extends Seeder
{
    /**
     * Khởi tạo 4 tài khoản người dùng mẫu chính xác 100% theo ảnh thiết kế giao diện Admin Users.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Nguyễn Bảo Ngọc',
                'email' => 'ngoc.travel@travelplanner.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'created_at' => Carbon::create(2026, 1, 15, 9, 30, 0),
            ],
            [
                'name' => 'Trần Hoàng Long',
                'email' => 'long.tran@travelplanner.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'created_at' => Carbon::create(2026, 2, 2, 14, 20, 0),
            ],
            [
                'name' => 'Hệ Thống Admin',
                'email' => 'admin@travelplanner.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
                'created_at' => Carbon::create(2026, 1, 1, 8, 0, 0),
            ],
            [
                'name' => 'Lê Thu Hà (Tạm khóa)',
                'email' => 'ha.le@travelplanner.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => false,
                'created_at' => Carbon::create(2026, 3, 18, 16, 45, 0),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
