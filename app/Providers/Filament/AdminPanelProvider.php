<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Enums\ThemeMode;
use App\Filament\Widgets\AttentionList;
use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\InventoryByBand;
use App\Filament\Widgets\MarginByProduct;
use App\Filament\Widgets\PacksSoldChart;
use App\Filament\Widgets\MarginRealisedWidget;
use App\Filament\Widgets\InventoryAgingWidget;
use App\Filament\Widgets\StorePerformanceWidget;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->defaultThemeMode(ThemeMode::Dark)
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Arcane')
            ->brandLogo(asset('images/logo.png'))
            ->darkMode(true)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => '#512b74',
                'gray'    => Color::Slate,
            ])
            ->font('Inter')
            ->favicon(asset('images/logo.png'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                DashboardStats::class,
                MarginRealisedWidget::class,
                PacksSoldChart::class,
                InventoryByBand::class,
                InventoryAgingWidget::class,
                MarginByProduct::class,
                StorePerformanceWidget::class,
                AttentionList::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
