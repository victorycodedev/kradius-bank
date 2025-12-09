<?php

namespace App\Providers\Filament;

use App\Models\Settings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->resourceCreatePageRedirect('index') // or
            ->authMiddleware([
                Authenticate::class,
            ])
            ->sidebarWidth('17.5rem')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->globalSearch(false)
            ->topbar(true)
            ->sidebarWidth('17.5rem')
            ->profile(isSimple: false)
            ->revealablePasswords()
            ->passwordReset()
            ->brandLogo(
                function () {
                    $settings = Settings::get();
                    $url = $settings->getFirstMediaUrl('logo', 'optimized');
                    return $url;
                }
            )
            ->favicon(
                function () {
                    $settings = Settings::get();
                    $url = $settings->getFirstMediaUrl('favicon', 'ico');
                    return $url;
                }
            )
            ->colors(function () {
                $settings = Settings::get();
                return [
                    'primary' => $settings->primary_color,
                    // 'gray' => $settings->secondary_color,
                ];
            })
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Account Management')
                    ->icon(Heroicon::UserGroup),
                NavigationGroup::make()
                    ->label('Loan Management')
                    ->icon(Heroicon::Banknotes),
                NavigationGroup::make()
                    ->label('Stock Trading')
                    ->icon(Heroicon::ChartBarSquare),
                NavigationGroup::make()
                    ->label('Settings')
                    ->icon(Heroicon::Cog8Tooth),
            ]);
    }
}
