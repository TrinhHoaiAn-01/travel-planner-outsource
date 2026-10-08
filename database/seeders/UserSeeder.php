<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Travel Planner Admin', 'email' => 'admin@travelplanner.test', 'role' => 'admin'],
            ['name' => 'Nguyen Minh Anh', 'email' => 'user@travelplanner.test', 'role' => 'user'],
            ['name' => 'Tran Gia Han', 'email' => 'han@travelplanner.test', 'role' => 'user'],
            ['name' => 'Le Quoc Bao', 'email' => 'bao@travelplanner.test', 'role' => 'user'],
            ['name' => 'Pham Ngoc Linh', 'email' => 'linh@travelplanner.test', 'role' => 'user'],
            ['name' => 'Vo Tuan Kiet', 'email' => 'kiet@travelplanner.test', 'role' => 'user'],
            [
                'name' => 'Nguyễn Bảo Ngọc',
                'email' => 'ngoc.travel@travelplanner.com',
                'role' => 'user',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop',
                'phone' => '0912 345 678',
                'city' => 'Đà Nẵng',
                'bio' => 'Đam mê du lịch tự túc, nhiếp ảnh phong cảnh và khám phá ẩm thực đường phố các vùng miền.',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                    'avatar' => $user['avatar'] ?? null,
                    'phone' => $user['phone'] ?? null,
                    'city' => $user['city'] ?? null,
                    'bio' => $user['bio'] ?? null,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
