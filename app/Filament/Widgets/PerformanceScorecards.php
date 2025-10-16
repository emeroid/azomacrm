<?php

// namespace App\Filament\Widgets;

// use App\Enums\Role;
// use App\Models\Order;
// use Filament\Widgets\StatsOverviewWidget\Stat;
// use Filament\Widgets\StatsOverviewWidget as BaseWidget;

// class PerformanceScorecards extends BaseWidget
// {
//     protected static ?int $sort = 5;
//     protected static ?string $pollingInterval = '120s';

//     protected function getStats(): array
//     {
//         $user = auth()->user();
//         $isAdmin = $user->is_admin;
//         $isMarketer = $user->role === Role::MARKETER->value;

//         $stats = [];

//         if ($isAdmin || $isMarketer) {
//             // Marketer Performance Score
//             $targetOrders = 50; // Monthly target
//             $achievedOrders = Order::when(!$isAdmin, fn($q) => $q->where('marketer_id', $user->id))
//                 ->whereBetween('created_at', [now()->startOfMonth(), now()])
//                 ->count();
//             $performanceScore = min(100, ($achievedOrders / $targetOrders) * 100);

//             $stats[] = Stat::make('Performance Score', round($performanceScore) . '%')
//                 ->description($achievedOrders . ' of ' . $targetOrders . ' target')
//                 ->color($performanceScore >= 80 ? 'success' : ($performanceScore >= 50 ? 'warning' : 'danger'))
//                 ->icon('heroicon-o-star')
//                 ->chart([30, 45, 60, 75, 90, 100])
//                 ->extraAttributes(['class' => 'bg-gradient-to-br from-blue-50 to-white']);
//         }

//         if ($isAdmin) {
//             // Team Performance
//             $teamTarget = 200;
//             $teamAchieved = Order::whereBetween('created_at', [now()->startOfMonth(), now()])->count();
//             $teamPerformance = min(100, ($teamAchieved / $teamTarget) * 100);

//             $stats[] = Stat::make('Team Performance', round($teamPerformance) . '%')
//                 ->description($teamAchieved . ' of ' . $teamTarget . ' target')
//                 ->color($teamPerformance >= 80 ? 'success' : ($teamPerformance >= 50 ? 'warning' : 'danger'))
//                 ->icon('heroicon-o-user-group')
//                 ->chart([40, 60, 75, 85, 95, 100])
//                 ->extraAttributes(['class' => 'bg-gradient-to-br from-purple-50 to-white']);
//         }

//         // Delivery Performance (for admins/delivery agents)
//         if ($isAdmin || $user->role === Role::DELIVERY_AGENT->value) {
//             $delivered = Order::where('status', Order::STATUS_DELIVERED)
//                 ->when($user->role === Role::DELIVERY_AGENT->value, fn($q) => $q->where('delivery_agent_id', $user->id))
//                 ->whereBetween('created_at', [now()->startOfMonth(), now()])
//                 ->count();
//             $deliveryTarget = $isAdmin ? 150 : 30;
//             $deliveryPerformance = min(100, ($delivered / $deliveryTarget) * 100);

//             $stats[] = Stat::make('Delivery Performance', round($deliveryPerformance) . '%')
//                 ->description($delivered . ' of ' . $deliveryTarget . ' target')
//                 ->color($deliveryPerformance >= 80 ? 'success' : ($deliveryPerformance >= 50 ? 'warning' : 'danger'))
//                 ->icon('heroicon-o-check-badge')
//                 ->chart([35, 50, 65, 80, 90, 100])
//                 ->extraAttributes(['class' => 'bg-gradient-to-br from-green-50 to-white']);
//         }

//         return $stats;
//     }
// }