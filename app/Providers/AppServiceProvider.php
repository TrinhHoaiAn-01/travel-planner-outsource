<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Services\Interfaces\ChatServiceInterface::class, \App\Services\ChatService::class);
        $this->app->bind(\App\Services\Interfaces\SearchServiceInterface::class, \App\Services\SolrSearchService::class);
        // Đăng ký agent và các dịch vụ hiện có mà luồng website sử dụng.
        $this->app->bind(\App\Services\Interfaces\AiTravelAssistantInterface::class, \App\Services\AiTravelAssistantService::class);
        $this->app->bind(\App\Services\Interfaces\ActivityLogServiceInterface::class, \App\Services\ActivityLogService::class);
        $this->app->bind(\App\Services\Interfaces\BookingServiceInterface::class, \App\Services\BookingService::class);
        $this->app->bind(\App\Services\Interfaces\BudgetServiceInterface::class, \App\Services\BudgetService::class);
        $this->app->bind(\App\Services\Interfaces\ExpenseServiceInterface::class, \App\Services\ExpenseService::class);
        $this->app->bind(\App\Services\Interfaces\ItineraryServiceInterface::class, \App\Services\ItineraryService::class);
        $this->app->bind(\App\Services\Interfaces\MoMoGatewayServiceInterface::class, \App\Services\MoMoGatewayService::class);
        $this->app->bind(\App\Services\Interfaces\PaymentServiceInterface::class, \App\Services\PaymentService::class);
        $this->app->bind(\App\Services\Interfaces\ReviewModerationServiceInterface::class, \App\Services\ReviewModerationService::class);
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

        // Ràng buộc TripServiceInterface với triển khai cụ thể TripService
        $this->app->bind(
            \App\Services\Interfaces\TripServiceInterface::class,
            \App\Services\TripService::class
        );

        //Ngocai
        // Ràng buộc DestinationServiceInterface với triển khai cụ thể DestinationService
        $this->app->bind(
            \App\Services\Interfaces\DestinationServiceInterface::class,
            \App\Services\DestinationService::class
        );

        // Ràng buộc FavoriteServiceInterface với triển khai cụ thể FavoriteService
        $this->app->bind(
            \App\Services\Interfaces\FavoriteServiceInterface::class,
            \App\Services\FavoriteService::class
        );

        // Ràng buộc ProfileServiceInterface với triển khai cụ thể ProfileService (Trịnh Hoài An)
        $this->app->bind(
            \App\Services\Interfaces\ProfileServiceInterface::class,
            \App\Services\ProfileService::class
        );

        // Ràng buộc CityManagementServiceInterface với triển khai cụ thể CityManagementService (Trần Văn Trọng)
        $this->app->bind(
            \App\Services\Interfaces\CityManagementServiceInterface::class,
            \App\Services\CityManagementService::class
        );

        // Ràng buộc CategoryManagementServiceInterface & CategoryServiceInterface với CategoryManagementService (Trần Văn Trọng)
        $this->app->bind(
            \App\Services\Interfaces\CategoryManagementServiceInterface::class,
            \App\Services\CategoryManagementService::class
        );
        $this->app->bind(
            \App\Services\Interfaces\CategoryServiceInterface::class,
            \App\Services\CategoryManagementService::class
        );

        // Ràng buộc UserManagementServiceInterface với triển khai cụ thể UserManagementService (Trần Văn Trọng)
        $this->app->bind(
            \App\Services\Interfaces\UserManagementServiceInterface::class,
            \App\Services\UserManagementService::class
        );

        // Ràng buộc DestinationManagementServiceInterface với DestinationManagementService (Trần Văn Trọng)
        $this->app->bind(
            \App\Services\Interfaces\DestinationManagementServiceInterface::class,
            \App\Services\DestinationManagementService::class
        );

        // Ràng buộc DestinationImageServiceInterface với DestinationImageService (Trần Văn Trọng)
        $this->app->bind(
            \App\Services\Interfaces\DestinationImageServiceInterface::class,
            \App\Services\DestinationImageService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        @umask(0000);
        foreach ([\App\Models\Destination::class, \App\Models\City::class, \App\Models\Category::class,
            \App\Models\Trip::class, \App\Models\User::class, \App\Models\Booking::class,
            \App\Models\Payment::class, \App\Models\ActivityLog::class, \App\Models\Review::class,
            \App\Models\RoomType::class] as $searchModel) {
            $searchModel::observe(\App\Observers\SearchIndexObserver::class);
        }
        \App\Models\Destination::observe(\App\Observers\CommerceCatalogObserver::class);
        \App\Models\City::observe(\App\Observers\CommerceCatalogObserver::class);
        \App\Models\Category::observe(\App\Observers\CommerceCatalogObserver::class);
        Schema::defaultStringLength(191);

        // Thiết lập view phân trang mặc định dùng CSS tự xây dựng, tránh lỗi Tailwind mặc định
        Paginator::defaultView('vendor.pagination.default');
    }
}
