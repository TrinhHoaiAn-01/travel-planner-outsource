<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

//Ngocai
// Tuyến trang chủ công khai (Public Home)
Route::get('/', [HomeController::class, 'index'])->name('home');

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
Route::middleware('auth')->group(function () {
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

// Nhóm các tuyến quản trị viên Admin (Bảo vệ bởi auth và admin middleware)
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        // Bảng điều khiển chính (Dashboard)
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Quản lý Người dùng hệ thống (Admin Users - Trần Văn Trọng)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

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