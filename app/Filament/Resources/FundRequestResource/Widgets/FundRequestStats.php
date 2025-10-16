<?php

namespace App\Filament\Resources\FundRequestResource\Widgets;

use App\Models\FundRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;


class FundRequestStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Funds Approved', '$'.number_format(FundRequest::where('status', 'approved')->sum('amount'), 2))
                ->description('Total amount disbursed')
                ->color('success'),
            Stat::make('Pending Requests', FundRequest::where('status', 'pending')->count())
                ->description('Awaiting manager approval')
                ->color('warning'),
            Stat::make('Total Requests', FundRequest::count())
                ->description('All requests submitted')
                ->color('info'),
        ];
    }
}
