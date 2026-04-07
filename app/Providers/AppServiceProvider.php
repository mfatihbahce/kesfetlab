<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Notification;
use App\Observers\NotificationObserver;

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
        // Observer'ı manuel olarak kayıt et
        try {
            Notification::observe(NotificationObserver::class);
        } catch (\Exception $e) {
            // Hata durumunda log'a yaz
            \Log::error('Observer registration failed: ' . $e->getMessage());
        }
    }
}