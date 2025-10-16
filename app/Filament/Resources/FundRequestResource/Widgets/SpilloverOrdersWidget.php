<?php

namespace App\Filament\Resources\FundRequestResource\Widgets;

use App\Models\FundRequest;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SpilloverOrdersWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $currentMonthStart = Carbon::now()->startOfMonth();
        $lastMonth = now()->subMonth();

        // 1. Get IDs of users who requested funds in the *previous* month
        $lastMonthRequesters = FundRequest::query()
            ->where('status', 'approved')
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->pluck('user_id')
            ->unique();
            
        // 2. Find orders placed by these users *before* the current month
        //    AND were delivered *within* the current month.
        $spilloverOrders = Order::query()
            ->where('status', Order::STATUS_DELIVERED)
            ->whereDate('created_at', '<', $currentMonthStart) // Placed before this month
            ->whereDate('updated_at', '>=', $currentMonthStart) // Delivered this month
            // Check if the requester ID is either the marketer or the call agent
            ->where(function ($query) use ($lastMonthRequesters) {
                $query->whereIn('marketer_id', $lastMonthRequesters)
                      ->orWhereIn('call_agent_id', $lastMonthRequesters);
            })
            ->get();

        $spilloverCount = $spilloverOrders->count();
        $spilloverValue = $spilloverOrders->sum(fn ($order) => $order->getTotalPriceAttribute());
        
        return [
            Stat::make('Spillover Orders Delivered', $spilloverCount)
                ->description('Orders placed last month, delivered this month by requesters.')
                ->color('secondary')
                ->chart([1, 5, 2, 6, 4, 10, 8]),

            Stat::make('Spillover Value', '₦' . number_format($spilloverValue, 2))
                ->description('Value of delivered spillover orders')
                ->color('secondary'),
        ];
    }
}