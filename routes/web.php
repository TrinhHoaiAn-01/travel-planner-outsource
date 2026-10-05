<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Tuyến trang chủ công khai (Public Home)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Tuyến đăng nhập đơn giản cho hệ thống
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        if (Auth::user()->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('home'));
    }

    return back()->withErrors([
        'email' => 'Thông tin đăng nhập không chính xác.',
    ])->onlyInput('email');
});

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

// Tuyến đăng xuất
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

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