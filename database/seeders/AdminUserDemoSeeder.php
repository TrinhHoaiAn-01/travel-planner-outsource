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
            [
                'name' => 'Vũ Minh Tuấn',
                'email' => 'tuan.vu@travelplanner.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'created_at' => Carbon::create(2026, 4, 1, 10, 0, 0),
            ],
            [
                'name' => 'Phạm Thu Trang',
                'email' => 'trang.pham@travelplanner.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'created_at' => Carbon::create(2026, 4, 5, 11, 15, 0),
            ],
            [
                'name' => 'Đỗ Thành Đạt',
                'email' => 'dat.do@travelplanner.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'created_at' => Carbon::create(2026, 4, 10, 14, 0, 0),
            ],
            [
                'name' => 'Hoàng Bích Phương',
                'email' => 'phuong.hoang@travelplanner.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'created_at' => Carbon::create(2026, 4, 12, 9, 0, 0),
            ],
            [
                'name' => 'Quản Trị Viên Phụ',
                'email' => 'admin.support@travelplanner.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
                'created_at' => Carbon::create(2026, 4, 15, 8, 30, 0),
            ],
            [
                'name' => 'Đinh Quang Khải',
                'email' => 'khai.dinh@travelplanner.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'created_at' => Carbon::create(2026, 4, 18, 15, 45, 0),
            ],
            [
                'name' => 'Ngô Mai Phương',
                'email' => 'mai.ngo@travelplanner.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'created_at' => Carbon::create(2026, 4, 20, 16, 20, 0),
            ],
            [
                'name' => 'Bùi Đức Anh',
                'email' => 'ducanh.bui@travelplanner.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'created_at' => Carbon::create(2026, 4, 22, 10, 10, 0),
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
