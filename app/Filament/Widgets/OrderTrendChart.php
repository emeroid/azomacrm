<?php

// namespace App\Filament\Widgets;

// use App\Enums\Role;
// use App\Models\Order;
// use Flowframe\Trend\Trend;
// use Flowframe\Trend\TrendValue;
// use Filament\Widgets\ChartWidget;

// class OrderTrendChart extends ChartWidget
// {
//     protected static ?string $heading = 'Order Trend Analysis';
//     protected static ?int $sort = 3;
//     protected static ?string $pollingInterval = '300s';
//     protected static ?string $maxHeight = '300px';

//     protected function getData(): array
//     {
//         $user = auth()->user();
//         $query = Order::query();
        
//         if ($user->role === Role::MARKETER->value) {
//             $query->where('marketer_id', $user->id);
//         } elseif ($user->role === Role::DELIVERY_AGENT->value) {
//             $query->where('delivery_agent_id', $user->id);
//         } elseif ($user->role === Role::CALL_AGENT->value) {
//             $query->where('call_agent_id', $user->id);
//         }

//         $data = Trend::query($query)
//             ->between(
//                 start: now()->subMonths(3),
//                 end: now(),
//             )
//             ->perMonth()
//             ->count();

//         return [
//             'datasets' => [
//                 [
//                     'label' => 'Orders',
//                     'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
//                     'fill' => true,
//                     'borderColor' => 'rgb(59, 130, 246)',
//                     'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
//                     'tension' => 0.3,
//                 ],
//             ],
//             'labels' => $data->map(fn (TrendValue $value) => $value->date),
//         ];
//     }

//     protected function getType(): string
//     {
//         return 'line';
//     }

//     protected function getOptions(): array
//     {
//         return [
//             'plugins' => [
//                 'legend' => [
//                     'display' => false,
//                 ],
//                 'tooltip' => [
//                     'enabled' => true,
//                     'mode' => 'index',
//                     'intersect' => false,
//                 ],
//             ],
//             'scales' => [
//                 'y' => [
//                     'beginAtZero' => true,
//                     'grid' => [
//                         'drawOnChartArea' => true,
//                         'color' => 'rgba(229, 231, 235, 0.2)',
//                     ],
//                     'ticks' => [
//                         'color' => '#9ca3af',
//                     ],
//                 ],
//                 'x' => [
//                     'grid' => [
//                         'drawOnChartArea' => false,
//                     ],
//                     'ticks' => [
//                         'color' => '#9ca3af',
//                     ],
//                 ],
//             ],
//             'responsive' => true,
//             'maintainAspectRatio' => false,
//             'interaction' => [
//                 'mode' => 'nearest',
//                 'axis' => 'x',
//                 'intersect' => false,
//             ],
//         ];
//     }
// }