<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiStudentAuthentication;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Thêm dòng này để sử dụng Bootstrap 5 pagination
        Paginator::useBootstrapFive();

        // Đăng ký middleware
        Route::aliasMiddleware('auth.student', ApiStudentAuthentication::class);
    }
}
