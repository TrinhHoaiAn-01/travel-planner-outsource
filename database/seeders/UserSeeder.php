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
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
