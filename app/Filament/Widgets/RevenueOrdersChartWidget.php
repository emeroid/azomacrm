<?php

namespace App\Filament\Widgets;

use App\Enums\Role;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RevenueOrdersChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Revenue vs Orders';
    protected static ?int $sort = 5;

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

    protected function getData(): array
    {
        $user = Auth::user();
        $query = Order::query()
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate]);

        // Apply role-based filtering
        if ($user->role === Role::MARKETER->value) {
            $query->where('orders.marketer_id', $user->id);
        } elseif ($user->role === Role::DELIVERY_AGENT->value) {
            $query->where('orders.delivery_agent_id', $user->id);
        } elseif ($user->role === Role::CALL_AGENT->value) {
            $query->where('orders.call_agent_id', $user->id);
        }

        $data = $query
            ->select(
                DB::raw('DATE(orders.created_at) as date'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data->pluck('order_count')->all(),
                    'yAxisID' => 'orders',
                ],
                [
                    'label' => 'Revenue (NGN)',
                    'data' => $data->pluck('revenue')->all(),
                    'yAxisID' => 'revenue',
                    'borderColor' => '#36A2EB',
                    'backgroundColor' => '#9BD0F5',
                ],
            ],
            'labels' => $data->pluck('date')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'orders' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                ],
                'revenue' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
