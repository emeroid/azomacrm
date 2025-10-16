<?php

namespace App\Filament\Pages;

use App\Enums\Role;
use App\Filament\Widgets\AnalyticsFiltersWidget;
use App\Filament\Widgets\AnalyticsStatsWidget;
use App\Filament\Widgets\AnalyticsTableWidget;
use App\Filament\Widgets\DeliveryStatusChartWidget;
use App\Filament\Widgets\FulfilmentSlaChartWidget;
use App\Filament\Widgets\OrdersChartWidget;
use App\Filament\Widgets\RevenueOrdersChartWidget;
use App\Filament\Widgets\RtoChartWidget;
use App\Filament\Widgets\StateOrdersChartWidget;
use Filament\Pages\Page;

class AnalyticsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.analytics-dashboard';
    protected static ?string $navigationGroup = 'Analytics';
    protected static ?int $navigationSort = 1;

    protected function getHeaderWidgets(): array
    {
        $widgets =  [
            AnalyticsFiltersWidget::class,
            AnalyticsStatsWidget::class,
            OrdersChartWidget::class,
            DeliveryStatusChartWidget::class,
            AnalyticsTableWidget::class,
            // New Widgets
            // RevenueOrdersChartWidget::class,
            StateOrdersChartWidget::class,
            FulfilmentSlaChartWidget::class,
            RtoChartWidget::class,
        ];

        if (auth()->user()->role === Role::ADMIN->value) {
           $widgets[] = RevenueOrdersChartWidget::class;
        }

        return $widgets;
    }
}

