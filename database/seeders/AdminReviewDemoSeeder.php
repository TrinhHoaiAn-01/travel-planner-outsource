<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AdminReviewDemoSeeder extends Seeder
{
    /**
     * Khởi tạo 4 đánh giá mẫu chính xác 100% theo ảnh giao diện Kiểm Duyệt Đánh Giá Khách Hàng.
     */
    public function run(): void
    {
        // 1. Dữ liệu Người dùng đánh giá
        $usersData = [
            [
                'name' => 'Nguyễn Bảo Ngọc',
                'email' => 'ngoc.travel@travelplanner.com',
            ],
            [
                'name' => 'Hoàng Đức Anh',
                'email' => 'ducanh@gmail.com',
            ],
            [
                'name' => 'Trần Hoàng Long',
                'email' => 'long.tran@travelplanner.com',
            ],
            [
                'name' => 'Spam Account',
                'email' => 'spam@bot.net',
            ],
        ];

        $users = [];
        foreach ($usersData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => bcrypt('password'),
                    'role' => 'user',
                    'is_active' => true,
                ]
            );
            $users[$data['email']] = $user;
        }

        // 2. Tra cứu các địa điểm tương ứng
        $banaHills = Destination::where('slug', 'sun-world-ba-na-hills')
            ->orWhere('name', 'like', '%Bà Nà Hills%')
            ->first();

        $vinpearlPhuQuoc = Destination::where('slug', 'vinpearl-resort-spa-phu-quoc')
            ->orWhere('slug', 'vinpearl-resort-phu-quoc')
            ->orWhere('name', 'like', '%Vinpearl%Phú Quốc%')
            ->first();

        $haLongCruise = Destination::where('slug', 'du-thuyen-vinh-ha-long')
            ->orWhere('name', 'like', '%Hạ Long%')
            ->first();

        $hoiAn = Destination::where('slug', 'hoi-an-ancient-town')
            ->orWhere('slug', 'pho-co-hoi-an')
            ->orWhere('name', 'like', '%Hội An%')
            ->first();

        // 3. Danh sách 4 đánh giá khớp chính xác với ảnh
        $reviewsData = [
            // Row 1: Nguyễn Bảo Ngọc - Sun World Bà Nà Hills - 5.0 - 28/09/2026 - Pending
            [
                'user' => $users['ngoc.travel@travelplanner.com'],
                'destination' => $banaHills,
                'rating' => 5,
                'comment' => 'Cầu Vàng sáng sớm siêu đẹp và ít đông đúc, không khí mát mẻ dễ chịu vô cùng.',
                'status' => Review::STATUS_PENDING,
                'created_at' => Carbon::create(2026, 9, 28, 9, 15, 0),
            ],
            // Row 2: Hoàng Đức Anh - Vinpearl Resort Phú Quốc - 4.0 - 27/09/2026 - Pending
            [
                'user' => $users['ducanh@gmail.com'],
                'destination' => $vinpearlPhuQuoc,
                'rating' => 4,
                'comment' => 'Phòng ốc rộng rãi, view biển rất thoáng mát, dịch vụ buffet sáng ngon miệng.',
                'status' => Review::STATUS_PENDING,
                'created_at' => Carbon::create(2026, 9, 27, 14, 30, 0),
            ],
            // Row 3: Trần Hoàng Long - Du Thuyền Vịnh Hạ Long - 5.0 - 15/08/2026 - Approved
            [
                'user' => $users['long.tran@travelplanner.com'],
                'destination' => $haLongCruise,
                'rating' => 5,
                'comment' => 'Trải nghiệm ngắm hoàng hôn vịnh Hạ Long trên du thuyền rất tuyệt vời, phục vụ chu đáo.',
                'status' => Review::STATUS_APPROVED,
                'created_at' => Carbon::create(2026, 8, 15, 17, 45, 0),
            ],
            // Row 4: Spam Account - Phố Cổ Hội An - 1.0 - 10/08/2026 - Hidden
            [
                'user' => $users['spam@bot.net'],
                'destination' => $hoiAn,
                'rating' => 1,
                'comment' => 'Nội dung quảng cáo link ngoài không hợp lệ vi phạm quy chuẩn cộng đồng.',
                'status' => Review::STATUS_HIDDEN,
                'created_at' => Carbon::create(2026, 8, 10, 8, 20, 0),
            ],
        ];

        // Bổ sung review mẫu với đủ 3 trạng thái cho tài khoản Demo User (user@travelplanner.test)
        $demoUser = User::where('email', 'user@travelplanner.test')->first();
        if ($demoUser) {
            if ($banaHills) {
                $reviewsData[] = [
                    'user' => $demoUser,
                    'destination' => $banaHills,
                    'rating' => 5,
                    'comment' => 'Không gian Bà Nà Hills tuyệt đẹp, làng Pháp kiến trúc cổ kính và thời tiết 4 mùa trong một ngày rất thú vị!',
                    'status' => Review::STATUS_APPROVED,
                    'created_at' => Carbon::create(2026, 10, 15, 10, 0, 0),
                ];
            }
            if ($vinpearlPhuQuoc) {
                $reviewsData[] = [
                    'user' => $demoUser,
                    'destination' => $vinpearlPhuQuoc,
                    'rating' => 4,
                    'comment' => 'Khu nghỉ dưỡng sang trọng, hồ bơi lớn sát biển và buffet sáng rất phong phú, các bạn nhân viên nhiệt tình.',
                    'status' => Review::STATUS_PENDING,
                    'created_at' => Carbon::create(2026, 10, 25, 14, 20, 0),
                ];
            }
            if ($hoiAn) {
                $reviewsData[] = [
                    'user' => $demoUser,
                    'destination' => $hoiAn,
                    'rating' => 2,
                    'comment' => 'Phố cổ Hội An đông đúc và chèo kéo khách quá mức vào buổi tối, cần có biện pháp quản lý tốt hơn.',
                    'status' => Review::STATUS_HIDDEN,
                    'created_at' => Carbon::create(2026, 10, 5, 18, 45, 0),
                ];
            }
        }

        foreach ($reviewsData as $item) {
            if ($item['user'] && $item['destination']) {
                $review = Review::updateOrCreate(
                    [
                        'user_id' => $item['user']->id,
                        'destination_id' => $item['destination']->id,
                    ],
                    [
                        'rating' => $item['rating'],
                        'comment' => $item['comment'],
                        'status' => $item['status'],
                    ]
                );

                $review->created_at = $item['created_at'];
                $review->updated_at = $item['created_at'];
                $review->save();
            }
        }
    }
}
