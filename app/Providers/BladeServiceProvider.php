<?php

namespace App\Providers;

use App\Models\Notification;
use App\Repositories\LanguageRepository;
use App\Repositories\NotificationInstanceRepository;
use App\Repositories\SettingRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $setting = null;
            try {
                $setting = \App\Models\Setting::first();
            } catch (\Exception $e) {}

            $app_setting = [
                'name' => config('app.name'),
                'favicon' => asset('assets/images/favicon.ico'),
                'logo' => asset('assets/images/logo-new.png'),
                'footer_text' => config('app.name'),
                'currency_position' => 'Left',
                'currency_symbol' => config('app.currency_symbol', '$'),
            ];

            if ($setting) {
                $app_setting['name'] = config('app.name');
                $app_setting['favicon'] = $setting->faviconPath ?? $app_setting['favicon'];
                $app_setting['logo'] = $setting->logoPath ?? $app_setting['logo'];
                $app_setting['footer_text'] = $setting->footer_text ?: config('app.name');
                $app_setting['currency_position'] = $setting->currency_position ?? 'Left';
                $app_setting['currency_symbol'] = config('app.currency_symbol', '$');
            }

            $storageLink = !file_exists(public_path('storage'));
            $notificationMessages = collect();
            
            if (auth()->check()) {
                if (class_exists(\App\Models\NotificationInstance::class)) {
                    $notificationMessages = \App\Models\NotificationInstance::where('recipient_id', auth()->id())->latest()->take(10)->get();
                }
            }

            $languages = collect();
            if (class_exists(\App\Models\Language::class)) {
                $languages = \App\Models\Language::all();
            }

            $view->with('app_setting', $app_setting);
            $view->with('storageLink', $storageLink);
            $view->with('notificationMessages', $notificationMessages);
            $view->with('languages', $languages);
        });
    }
}
