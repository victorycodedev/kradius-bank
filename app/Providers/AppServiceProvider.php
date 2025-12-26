<?php

namespace App\Providers;

use App\Listeners\SendLoginSuccessNotification;
use App\Listeners\SendRegisterSuccessNotification;
use App\Models\Settings;
use EragLaravelPwa\Facades\PWA;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {

        Event::listen(
            Login::class,
            SendLoginSuccessNotification::class,
        );

        Event::listen(
            Registered::class,
            SendRegisterSuccessNotification::class,
        );

        $appInProduction = App::environment('production');

        Model::automaticallyEagerLoadRelationships();

        $settings = Settings::get();

        $logo = $settings->getFirstMediaUrl('logo', 'optimized');
        $favicon = $settings->getFirstMediaUrl('favicon', 'ico');

        View::share('configuration', $settings);
        View::share('site_logo', $logo);
        View::share('site_favicon', $favicon);

        // Build PWA icons array
        $icons = [];

        // Add all icon sizes
        $iconSizes = ['72x72', '96x96', '128x128', '144x144', '152x152', '192x192', '384x384', '512x512'];

        foreach ($iconSizes as $size) {
            $iconUrl = $settings->getFirstMediaUrl('mobile_app_icon', "icon-{$size}");
            if ($iconUrl) {
                $icons[] = [
                    'src' => $iconUrl,
                    'sizes' => $size,
                    'type' => 'image/png',
                    'purpose' => 'any'
                ];
            }
        }

        // Add maskable icons for adaptive icons on Android
        $icons[] = [
            'src' => $settings->getFirstMediaUrl('mobile_app_icon', 'icon-192x192'),
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'maskable'
        ];

        $icons[] = [
            'src' => $settings->getFirstMediaUrl('mobile_app_icon', 'icon-512x512'),
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'maskable'
        ];

        // Fallback if no icons are uploaded
        if (empty($icons)) {
            $icons = [
                [
                    'src' => asset('images/icons/icon-192x192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => asset('images/icons/icon-512x512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ]
            ];
        }

        PWA::update([
            'name' => $settings->app_name ?? config('app.name'),
            'short_name' => $settings->app_short_name ?? 'BankApp',
            'background_color' => $settings->primary_color ?? '#ffffff',
            'display' => 'standalone',
            'description' => $settings->app_slogan ?? 'Secure mobile banking',
            'theme_color' => $settings->primary_color ?? '#f46b10',
            'start_url' => '/onboarding',
            'scope' => '/onboarding',
            'orientation' => 'portrait-primary',
            'icons' => $icons,
            'categories' => ['finance', 'banking'],
            'screenshots' => [], // Add screenshots if needed
        ]);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
