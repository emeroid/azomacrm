<?php

namespace App\Filament\Widgets;

use App\Models\FundRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $approvedAmount = FundRequest::where('status', 'approved')->sum('amount');
        $totalApprovals = FundRequest::where('status', 'approved')->count();
        $totalRejections = FundRequest::where('status', 'rejected')->count();

        return [
            Stat::make('Total Amount Disbursed', '₦' . number_format($approvedAmount, 2))
                ->description('Sum of all approved requests')
                ->color('success'),
            Stat::make('Total Approvals', $totalApprovals)
                ->description('Count of approved requests')
                ->color('info'),
            Stat::make('Total Rejections', $totalRejections)
                ->description('Count of rejected requests')
                ->color('danger'),
        ];
    }

    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }
}
