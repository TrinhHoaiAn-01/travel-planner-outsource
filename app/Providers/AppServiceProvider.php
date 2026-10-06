<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ràng buộc DashboardServiceInterface với triển khai cụ thể DashboardService
        $this->app->bind(
            \App\Services\Interfaces\DashboardServiceInterface::class,
            \App\Services\DashboardService::class
        );

        // Ràng buộc AuthServiceInterface với triển khai cụ thể AuthService
        $this->app->bind(
            \App\Services\Interfaces\AuthServiceInterface::class,
            \App\Services\AuthService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Schema::defaultStringLength(191);
    }
}
