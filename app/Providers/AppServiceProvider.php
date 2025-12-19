<?php

namespace App\Providers;

use App\Models\Settings;
use EragLaravelPwa\Facades\PWA;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        $appInProduction = App::environment('production');

        Model::automaticallyEagerLoadRelationships();

        $settings = Settings::get();

        $logo = $settings->getFirstMediaUrl('logo', 'optimized');
        $favicon = $settings->getFirstMediaUrl('favicon', 'ico');

        View::share('configuration', $settings);
        View::share('site_logo', $logo);
        View::share('site_favicon', $favicon);

        PWA::update([
            'name' => $settings->app_name,
            'short_name' => $settings->app_short_name,
            'background_color' => $settings->primary_color,
            'display' => 'fullscreen',
            'description' => $settings->app_slogan,
            'theme_color' => $settings->primary_color,
            'start_url' => '/onboarding', // Add this line
            'icons' => [
                [
                    'src' => $settings->getFirstMediaUrl('mobile_app_icon', 'optimized'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
        ]);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
