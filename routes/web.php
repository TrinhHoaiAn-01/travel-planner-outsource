<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\DestinationImageController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DestinationController as PublicDestinationController;
use App\Http\Controllers\HomeController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

//Ngocai
// Tuyến trang chủ công khai (Public Home)
Route::get('/', [HomeController::class, 'index'])->name('home');
// Tuyến API tìm kiếm nhanh gợi ý địa điểm & thành phố từ Trang chủ (Trần Văn Trọng)
Route::get('/quick-search', [HomeController::class, 'quickSearch'])->name('destinations.quick-search');

// Tuyến hiển thị nơi lưu trú, danh sách & chi tiết địa điểm, Room Type và Gallery ảnh (Trần Văn Trọng)
Route::get('/destinations', [PublicDestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{slug}', [PublicDestinationController::class, 'show'])->name('destinations.show');
Route::get('/destinations/{slug}/gallery', [PublicDestinationController::class, 'gallery'])->name('destinations.gallery');
Route::get('/room-types/{roomType}', [PublicDestinationController::class, 'getRoomType'])->name('room-types.show');
Route::get('/cities/{city}/destinations', [PublicDestinationController::class, 'byCity'])->name('cities.destinations');
Route::get('/categories/{category}/destinations', [PublicDestinationController::class, 'byCategory'])->name('categories.destinations');


use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TripController;

// Các tuyến đăng ký & đăng nhập & khôi phục mật khẩu (Dành cho khách chưa đăng nhập)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/captcha/refresh', [AuthController::class, 'refreshCaptcha'])->name('captcha.refresh');

    // Quên mật khẩu & Khôi phục mật khẩu (Trịnh Hoài An)
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Các tuyến xác thực thông tin đăng ký / email (Dành cho tài khoản đã đăng nhập)
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])->name('verification.send');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Quản lý hồ sơ cá nhân & Đổi mật khẩu (Profile & Avatar - Trịnh Hoài An)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::get('/profile/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('profile.password');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.password.update');

    // Quản lý chuyến đi của người dùng (Trip Management - Nguyễn Trần Thành)
    Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
    Route::get('/trips/create', [TripController::class, 'create'])->name('trips.create');
    Route::post('/trips', [TripController::class, 'store'])->name('trips.store');
    Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show');
    Route::get('/trips/{trip}/edit', [TripController::class, 'edit'])->name('trips.edit');
    Route::put('/trips/{trip}', [TripController::class, 'update'])->name('trips.update');
    Route::patch('/trips/{trip}/status', [TripController::class, 'updateStatus'])->name('trips.update-status');
    Route::patch('/trips/{trip}/notes', [TripController::class, 'updateNotes'])->name('trips.update-notes');
    Route::delete('/trips/{trip}', [TripController::class, 'destroy'])->name('trips.destroy');
    Route::post('/trips/{trip}/clone', [TripController::class, 'clone'])->name('trips.clone');
    Route::post('/trips/{trip}/reopen', [TripController::class, 'reopen'])->name('trips.reopen');

    //Ngocai
    // Quản lý danh sách địa điểm yêu thích (Favorites) & Thêm vào Trip
    Route::get('/favorites', [\App\Http\Controllers\FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/toggle/{destinationId}', [\App\Http\Controllers\FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::post('/favorites/add-to-trip', [\App\Http\Controllers\FavoriteController::class, 'addToTrip'])->name('favorites.add-to-trip');

    // Quản lý Ngân sách & Chi tiêu (Budget & Expenses - Nguyễn Văn Thắng)
    Route::get('/trips/{trip}/budget', [\App\Http\Controllers\BudgetController::class, 'show'])->name('trips.budget.show');
    Route::patch('/trips/{trip}/budget', [\App\Http\Controllers\BudgetController::class, 'update'])->name('trips.budget.update');
    Route::post('/trips/{trip}/expenses', [\App\Http\Controllers\ExpenseController::class, 'store'])->name('trips.expenses.store');
    Route::patch('/expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'destroy'])->name('expenses.destroy');
    // Quản lý Đặt phòng (Bookings - Nguyễn Văn Thắng - STT 5, 6, 7, 8)
    Route::get('/bookings', [\App\Http\Controllers\BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings', [\App\Http\Controllers\BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/cancel', [\App\Http\Controllers\BookingController::class, 'cancel'])->name('bookings.cancel');
});

// Tuyến đăng nhập nhanh người dùng thường (Demo / Test)
Route::get('/dev/login-as-user', function () {
    $user = User::where('email', 'user@travelplanner.test')->first() ?? User::where('role', 'user')->first();
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

// Nhóm các tuyến quản trị viên Admin (Bảo vệ bởi auth, active và admin middleware)
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'active', 'admin'])
    ->group(function () {
        // Bảng điều khiển chính (Dashboard)
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/export-report', [DashboardController::class, 'exportReport'])->name('dashboard.export');

        // Quản lý Người dùng hệ thống (Admin Users - Trịnh Hoài An)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.update-status');
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');

        // Quản lý Tỉnh / Thành phố (Admin Cities - Trần Văn Trọng)
        Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
        Route::post('/cities', [CityController::class, 'store'])->name('cities.store');
        Route::put('/cities/{city}', [CityController::class, 'update'])->name('cities.update');
        Route::delete('/cities/{city}', [CityController::class, 'destroy'])->name('cities.destroy');

        // Quản lý Danh mục Du lịch (Admin Categories - Trần Văn Trọng)
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Quản lý Địa điểm Du lịch (Admin Destinations - Trần Văn Trọng)
        Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
        Route::get('/destinations/create', [DestinationController::class, 'create'])->name('destinations.create');
        Route::post('/destinations', [DestinationController::class, 'store'])->name('destinations.store');
        Route::get('/destinations/{destination}', [DestinationController::class, 'show'])->name('destinations.show');
        Route::get('/destinations/{destination}/edit', [DestinationController::class, 'edit'])->name('destinations.edit');
        Route::put('/destinations/{destination}', [DestinationController::class, 'update'])->name('destinations.update');
        Route::delete('/destinations/{destination}', [DestinationController::class, 'destroy'])->name('destinations.destroy');

        // Quản lý Thư viện Hình ảnh (Admin Destination Images - Trần Văn Trọng)
        Route::get('/destination-images', [DestinationImageController::class, 'index'])->name('destination-images.index');
        Route::post('/destination-images', [DestinationImageController::class, 'store'])->name('destination-images.store');
        Route::post('/destination-images/{image}/set-primary', [DestinationImageController::class, 'setPrimary'])->name('destination-images.set-primary');
        Route::delete('/destination-images/{image}', [DestinationImageController::class, 'destroy'])->name('destination-images.destroy');

        // Kiểm duyệt Đánh giá Khách hàng (Admin Reviews - Trần Văn Trọng)
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/{review}', [ReviewController::class, 'show'])->name('reviews.show');
        Route::post('/reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('/reviews/{review}/hide', [ReviewController::class, 'hide'])->name('reviews.hide');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');



        Route::get('/bookings', function () {
            return view('admin.placeholder', ['pageTitle' => 'Quản lý Đơn đặt phòng (Bookings)']);
        })->name('bookings.index');

        Route::get('/bookings/{booking}', function ($booking) {
            return view('admin.placeholder', ['pageTitle' => "Chi tiết Đơn đặt phòng #{$booking}"]);
        })->name('bookings.show');

        // Quản lý Nhật ký hoạt động hệ thống (Activity Logs - Trịnh Hoài An)
        Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/{activityLog}', [\App\Http\Controllers\Admin\ActivityLogController::class, 'show'])->name('activity-logs.show');
    });