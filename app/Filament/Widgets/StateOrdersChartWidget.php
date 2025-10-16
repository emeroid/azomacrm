<?php

namespace App\Filament\Widgets;

use App\Enums\Role;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StateOrdersChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Top States by Orders';
    protected static ?int $sort = 6;
    
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
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotNull('state');

        // Apply role-based filtering
        if ($user->role === Role::MARKETER->value) {
            $query->where('marketer_id', $user->id);
        } elseif ($user->role === Role::DELIVERY_AGENT->value) {
            $query->where('delivery_agent_id', $user->id);
        } elseif ($user->role === Role::CALL_AGENT->value) {
            $query->where('call_agent_id', $user->id);
        }

        $data = $query
            ->select('state', DB::raw('count(*) as count'))
            ->groupBy('state')
            ->orderBy('count', 'desc')
            ->limit(10) // Show top 10 states
            ->pluck('count', 'state');

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data->values()->all(),
                ],
            ],
            'labels' => $data->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
