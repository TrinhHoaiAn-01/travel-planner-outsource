<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleTripDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'user@travelplanner.test'],
            [
                'name' => 'Ngọc Nguyễn',
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
            ]
        );

        Trip::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Hành trình Khám phá Đà Nẵng - Hội An'],
            [
                'description' => 'Bà Nà Hills, Bán đảo Sơn Trà, Ngũ Hành Sơn và phố đèn lồng Hội An thơ mộng.',
                'start_date' => '2026-10-10',
                'end_date' => '2026-10-15',
                'budget' => 15000000,
                'status' => Trip::STATUS_PLANNED,
            ]
        );

        Trip::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Du Thuyền Nghỉ Dưỡng Vịnh Hạ Long'],
            [
                'description' => 'Trải nghiệm chèo thuyền kayak hang Luồn, tắm biển đảo Ti Tốp và tiệc trà hoàng hôn.',
                'start_date' => '2026-09-26',
                'end_date' => '2026-09-30',
                'budget' => 10000000,
                'status' => 'ongoing',
            ]
        );

        $trip3 = Trip::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Săn Mây Đỉnh Fansipan Sa Pa'],
            [
                'description' => 'Chinh phục đỉnh núi cao nhất Việt Nam, trekking bản Cát Cát và thưởng thức lẩu cá tầm.',
                'start_date' => '2026-08-15',
                'end_date' => '2026-08-18',
                'budget' => 10000000,
                'status' => Trip::STATUS_COMPLETED,
            ]
        );

        if ($trip3->expenses()->count() === 0) {
            Expense::create([
                'trip_id' => $trip3->id,
                'category' => 'other',
                'description' => 'Chi phí tour và ăn uống Fansipan',
                'amount' => 7850000,
                'expense_date' => '2026-08-16',
            ]);
        }
    }
}
