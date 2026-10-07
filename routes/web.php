<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

//Ngocai
// Tuyến trang chủ công khai (Public Home)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Các tuyến đăng ký & đăng nhập (Dành cho khách chưa đăng nhập)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/captcha/refresh', [AuthController::class, 'refreshCaptcha'])->name('captcha.refresh');
});

use App\Http\Controllers\TripController;

// Các tuyến xác thực thông tin đăng ký / email (Dành cho tài khoản đã đăng nhập)
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])->name('verification.send');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Quản lý chuyến đi của người dùng (Trip Management - Nguyễn Trần Thành)
    Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
    Route::get('/trips/create', [TripController::class, 'create'])->name('trips.create');
    Route::post('/trips', [TripController::class, 'store'])->name('trips.store');
    Route::post('/trips/{trip}/clone', [TripController::class, 'clone'])->name('trips.clone');
    Route::post('/trips/{trip}/reopen', [TripController::class, 'reopen'])->name('trips.reopen');

    //Ngocai
    // Quản lý danh sách địa điểm yêu thích (Favorites) & Thêm vào Trip
    Route::get('/favorites', [\App\Http\Controllers\FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/toggle/{destinationId}', [\App\Http\Controllers\FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::post('/favorites/add-to-trip', [\App\Http\Controllers\FavoriteController::class, 'addToTrip'])->name('favorites.add-to-trip');
});

// Tuyến đăng nhập nhanh người dùng thường (Demo / Test)
Route::get('/dev/login-as-user', function () {
    $user = User::where('role', 'user')->first();
    if (! $user) {
        $user = User::firstOrCreate(
            ['email' => 'user@travelplanner.test'],
            [
                'name' => 'Ngọc Nguyễn',
                'password' => bcrypt('password'),
                'role' => 'user',
                'is_active' => true,
            ]
        );
    }
    Auth::login($user);
    return redirect()->route('trips.index');
})->name('dev.login.user');

// Tuyến đăng nhập nhanh cho môi trường phát triển / demo
Route::get('/dev/login-as-admin', function () {
    $admin = User::where('role', 'admin')->first();
    if (!$admin) {
        $admin = User::firstOrCreate(
            ['email' => 'admin@travelplanner.test'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
    Auth::login($admin);
    return redirect()->route('admin.dashboard');
})->name('dev.login.admin');

// Nhóm các tuyến quản trị viên Admin (Bảo vệ bởi auth và admin middleware)
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        // Bảng điều khiển chính (Dashboard)
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Các tuyến chức năng quản trị hệ thống
        Route::get('/users', function () {
            return view('admin.placeholder', ['pageTitle' => 'Quản lý Người dùng (Users)']);
        })->name('users.index');

        Route::get('/cities', function () {
            return view('admin.placeholder', ['pageTitle' => 'Quản lý Thành phố (Cities)']);
        })->name('cities.index');

        Route::get('/categories', function () {
            return view('admin.placeholder', ['pageTitle' => 'Quản lý Danh mục (Categories)']);
        })->name('categories.index');

        Route::get('/destinations', function () {
            return view('admin.placeholder', ['pageTitle' => 'Quản lý Địa điểm (Destinations)']);
        })->name('destinations.index');

        Route::get('/destination-images', function () {
            return view('admin.placeholder', ['pageTitle' => 'Thư viện ảnh (Destination Images)']);
        })->name('destination-images.index');

        Route::get('/reviews', function () {
            return view('admin.placeholder', ['pageTitle' => 'Kiểm duyệt Đánh giá (Reviews)']);
        })->name('reviews.index');

        Route::get('/bookings', function () {
            return view('admin.placeholder', ['pageTitle' => 'Quản lý Đơn đặt phòng (Bookings)']);
        })->name('bookings.index');

        Route::get('/bookings/{booking}', function ($booking) {
            return view('admin.placeholder', ['pageTitle' => "Chi tiết Đơn đặt phòng #{$booking}"]);
        })->name('bookings.show');
    });