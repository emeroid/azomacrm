<?php

namespace App\Filament\Widgets;

use App\Enums\Role;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AnalyticsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    public ?string $startDate = null;
    public ?string $endDate = null;

    protected $listeners = ['updateAnalyticsFilters'];

    public function mount(): void
    {
        $this->startDate = Carbon::now()->startOfWeek()->toDateString();
        $this->endDate = Carbon::now()->endOfWeek()->toDateString();
    }

    public function updateAnalyticsFilters(array $filters): void
    {
        $this->startDate = $filters['start_date'];
        $this->endDate = $filters['end_date'];
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $query = Order::query()
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        // Apply role-based filtering
        if ($user->role === Role::MARKETER->value) {
            $query->where('marketer_id', $user->id);
        } elseif ($user->role === Role::DELIVERY_AGENT->value) {
            $query->where('delivery_agent_id', $user->id);
        } elseif ($user->role === Role::CALL_AGENT->value) {
            $query->where('call_agent_id', $user->id);
        }
        
        // Clone the query to use it for different calculations
        $totalOrders = (clone $query)->count();
        $totalDelivered = (clone $query)->where('status', Order::STATUS_DELIVERED)->count();
        $totalPending = (clone $query)->whereIn('status', [Order::STATUS_PROCESSING, Order::STATUS_CONFIRMED, Order::STATUS_SCHEDULED, Order::STATUS_IN_TRANSIT])->count();
        $totalReturned = (clone $query)->where('status', Order::STATUS_RETURNED)->count();

        return [
            Stat::make('Total Orders', $totalOrders)
                ->description('All orders in the selected period')
                ->color('primary'),
            Stat::make('Orders Delivered', $totalDelivered)
                ->description('Successfully delivered orders')
                ->color('success'),
            Stat::make('Orders Pending', $totalPending)
                ->description('Orders not yet delivered')
                ->color('warning'),
            Stat::make('Orders Returned (RTO)', $totalReturned)
                ->description('Failed deliveries returned to origin')
                ->color('danger'),
        ];
    }
}

