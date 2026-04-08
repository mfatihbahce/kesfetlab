<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Notification;
use App\Models\AppSetting;
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
        // Uygulama genelinde marka ayarlarini paylas.
        View::composer('*', function ($view) {
            try {
                $brandName = AppSetting::getValue('brand_name', 'Kesfet LAB');
                $brandLogoPath = AppSetting::getValue('brand_logo_path');
            } catch (\Throwable $e) {
                // Migration oncesi/table yoksa fallback kullan.
                $brandName = 'Kesfet LAB';
                $brandLogoPath = null;
            }

            $view->with('brandName', $brandName);
            $view->with('brandLogoPath', $brandLogoPath);
        });

        // Observer'ı manuel olarak kayıt et
        try {
            Notification::observe(NotificationObserver::class);
        } catch (\Exception $e) {
            // Hata durumunda log'a yaz
            \Log::error('Observer registration failed: ' . $e->getMessage());
        }
    }
}