<?php

namespace App\Providers\Filament;

use App\Enums\Role;
use App\Filament\Pages\AnalyticsDashboard;
use App\Filament\Pages\Profile;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot() {
        FilamentColor::register([
            'indigo' => Color::Indigo,
            'yellow' => Color::Orange,
            'zinc' => Color::Zinc,
            'rose' => Color::Rose,
            'cyan' => Color::Cyan,
            'blue' => Color::Blue,
            'amber' => Color::Amber,
        ]);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->spa()
            ->passwordReset()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->profile(Profile::class)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                AnalyticsDashboard::class,
            ])
            ->navigationItems([
                NavigationItem::make('Create Template')
                    ->url('/template')
                    ->icon('heroicon-o-link') // optional
                    ->openUrlInNewTab()
                    ->visible(function() {
                        return (request()->user()->role === Role::MARKETER->value) || (request()->user()->is_admin);
                    }),// optional
                NavigationItem::make('Call Center')
                    ->url('/orders/follow-up')
                    ->icon('heroicon-o-phone-arrow-down-left') // optional
                    // ->openUrlInNewTab()// optional
                    ->visible(function() {
                        return (request()->user()->role === Role::CALL_AGENT->value) || (request()->user()->is_admin);
                    }),// optional

                NavigationItem::make('WA Campaign')
                    ->url('/devices')
                    ->icon('heroicon-o-link') // optional
                    ->openUrlInNewTab()
                    ->visible(function() {
                        return (request()->user()->role === Role::MARKETER->value) || (request()->user()->is_admin);
                    }),// optional
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
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
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\RestrictFilamentAccess::class,
            ]);
    }
}
