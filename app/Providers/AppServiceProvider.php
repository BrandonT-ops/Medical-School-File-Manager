<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\StorageService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register StorageService as singleton
        $this->app->singleton(StorageService::class, function ($app) {
            return new StorageService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Share school branding with all views
        view()->composer('*', function ($view) {
            $view->with('schoolName', config('app.school_name', env('SCHOOL_NAME', 'Medical School')));
            $view->with('schoolTagline', config('app.school_tagline', env('SCHOOL_TAGLINE', 'File Management System')));
            $view->with('schoolLogo', config('app.school_logo', env('SCHOOL_LOGO', '')));
            $view->with('schoolFooter', config('app.school_footer', env('SCHOOL_FOOTER', '© 2024 Medical School')));
        });
    }
}
