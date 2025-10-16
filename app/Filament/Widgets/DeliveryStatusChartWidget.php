<?php

namespace App\Filament\Widgets;

use App\Enums\Role;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeliveryStatusChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Delivery Status';
    protected static ?int $sort = 3;
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
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);
        
        // Apply role-based filtering
        if ($user->role === Role::MARKETER->value) {
            $query->where('marketer_id', $user->id);
        } elseif ($user->role === Role::DELIVERY_AGENT->value) {
            $query->where('delivery_agent_id', $user->id);
        } elseif ($user->role === Role::CALL_AGENT->value) {
            $query->where('call_agent_id', $user->id);
        }

        $data = $query
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $statuses = $data->keys()->map(fn($status) => ucfirst(str_replace('_', ' ', $status)))->all();
        $counts = $data->values()->all();

        return [
            'datasets' => [
                [
                    'label' => 'Orders by Status',
                    'data' => $counts,
                ],
            ],
            'labels' => $statuses,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getChartHeight(): string|int
    {
        return 150; // px height (default is ~400)
    }
}

