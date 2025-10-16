<?php

// namespace App\Filament\Widgets;

// use App\Enums\Role;
// use App\Models\Order;
// use Filament\Widgets\ChartWidget;
// use Illuminate\Support\Facades\DB;

// class OrderStatusRadialChart extends ChartWidget
// {
//     protected static ?string $heading = 'Order Status Distribution';
//     protected static ?string $pollingInterval = '60s';
//     protected static ?string $maxHeight = '300px';
//     protected static ?int $sort = 2;

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

//         $data = $query->select('status', DB::raw('count(*) as total'))
//             ->groupBy('status')
//             ->pluck('total', 'status')
//             ->toArray();

//         return [
//             'datasets' => [
//                 [
//                     'label' => 'Order Status',
//                     'data' => array_values($data),
//                     'backgroundColor' => [
//                         'rgba(59, 130, 246, 0.8)',  // Processing
//                         'rgba(245, 158, 11, 0.8)',   // In Transit
//                         'rgba(16, 185, 129, 0.8)',   // Delivered
//                         'rgba(239, 68, 68, 0.8)',    // Cancelled
//                     ],
//                     'borderColor' => [
//                         'rgb(59, 130, 246)',
//                         'rgb(245, 158, 11)',
//                         'rgb(16, 185, 129)',
//                         'rgb(239, 68, 68)',
//                     ],
//                     'borderWidth' => 1,
//                     'cutout' => '70%',
//                 ],
//             ],
//             'labels' => array_map(function($status) {
//                 return ucwords(str_replace('_', ' ', $status));
//             }, array_keys($data)),
//         ];
//     }

//     protected function getType(): string
//     {
//         return 'doughnut';
//     }

//     protected function getOptions(): array
//     {
//         return [
//             'plugins' => [
//                 'legend' => [
//                     'position' => 'right',
//                     'labels' => [
//                         'font' => [
//                             'family' => 'Inter',
//                             'size' => 14,
//                             'weight' => '600',
//                         ],
//                         'color' => '#6b7280',
//                         'padding' => 20,
//                         'usePointStyle' => true,
//                     ],
//                 ],
//                 'tooltip' => [
//                     'enabled' => true,
//                     'backgroundColor' => '#1f2937',
//                     'titleFont' => [
//                         'family' => 'Inter',
//                         'size' => 14,
//                     ],
//                     'bodyFont' => [
//                         'family' => 'Inter',
//                         'size' => 12,
//                     ],
//                     'cornerRadius' => 8,
//                     'displayColors' => true,
//                 ],
//             ],
//             'cutoutPercentage' => 70,
//             'animation' => [
//                 'animateScale' => true,
//                 'animateRotate' => true,
//             ],
//             'responsive' => true,
//             'maintainAspectRatio' => false,
//         ];
//     }
// }