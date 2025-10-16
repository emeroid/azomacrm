<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use App\Filament\Widgets\FinancialOverviewWidget;
use App\Filament\Widgets\PendingFundRequestsWidget;
use App\Filament\Widgets\UserActivityLogWidget;

class OperationManagerDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Manager Dashboard';

    protected static ?string $navigationGroup = 'Dashboards';

    protected static string $view = 'filament.pages.operation-manager-dashboard';

    public static function canAccess(): bool
    {
        return Gate::allows('view_operation_manager_dashboard');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FinancialOverviewWidget::class,
        ];
    }

     protected function getFooterWidgets(): array
     {
         return [
            PendingFundRequestsWidget::class,
            UserActivityLogWidget::class,
         ];
     }
}
