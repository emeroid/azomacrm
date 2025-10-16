<?php

/* namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use App\Enums\Role;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.custom-dashboard';

    public function getWidgets(): array
    {
        $user = Auth::user();
        $isAdmin = $user->is_admin;
        $isMarketer = $user->role === Role::MARKETER->value;
        // $isDeliveryAgent = $user->role === Role::DELIVERY_AGENT->value;

        $widgets = [
            // \App\Filament\Widgets\PremiumStatsOverview::class,
            // \App\Filament\Widgets\OrderStatusRadialChart::class,
            // \App\Filament\Widgets\RecentOrdersTable::class,
        ];

        if ($isAdmin || $isMarketer) {
            // array_push($widgets, 
            //     \App\Filament\Widgets\OrderTrendChart::class,
            //     \App\Filament\Widgets\PerformanceScorecards::class
            // );
        }

        return $widgets;
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'xl' => 3,
            '2xl' => 4,
        ];
    }

    public function getTitle(): string
    {
        return 'Performance Dashboard';
    }
} */