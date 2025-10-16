<?php

// namespace App\Filament\Widgets;

// use App\Enums\Role;
// use App\Models\Order;
// use App\Models\EmbeddableForm;
// use App\Models\FormTemplate;
// use Filament\Widgets\StatsOverviewWidget as BaseWidget;
// use Filament\Widgets\StatsOverviewWidget\Stat;

// class PremiumStatsOverview extends BaseWidget
// {
//     protected static ?string $pollingInterval = '30s';
//     protected static bool $isLazy = true;

//     protected function getStats(): array 
//     {
//         $user = auth()->user();
//         $isAdmin = $user->is_admin;
//         $isMarketer = $user->role === Role::MARKETER->value;
//         $isDeliveryAgent = $user->role === Role::DELIVERY_AGENT->value;
//         $isCallAgent = $user->role === Role::CALL_AGENT->value;

//         $stats = [];

//         // Total Orders - Animated Counter
//         $orderQuery = Order::query();
//         if ($isMarketer) {
//             $orderQuery->where('marketer_id', $user->id);
//         } elseif($isCallAgent) {
//             $orderQuery->where('call_agent_id', $user->id);
//         } elseif($isDeliveryAgent) {
//             $orderQuery->where('delivery_agent_id', $user->id);
//         }

//         $totalOrders = $orderQuery->count();

//         $stats[] = Stat::make('Total Orders', number_format($totalOrders))
//             ->description('All time orders')
//             ->icon('heroicon-o-shopping-bag')
//             ->color('primary')
//             ->chart($this->getOrderTrendData())
//             ->descriptionIcon('heroicon-m-arrow-trending-up')
//             ->extraAttributes(['class' => 'hover:shadow-lg transition-shadow']);

//         // Monthly Performance - With Trend Indicator
//         $monthlyOrders = clone $orderQuery;
//         $monthlyOrders->whereBetween('created_at', [now()->startOfMonth(), now()]);
//         $lastMonthOrders = clone $orderQuery;
//         $lastMonthOrders->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
        
//         $currentMonthCount = $monthlyOrders->count();
//         $lastMonthCount = $lastMonthOrders->count();
//         $change = $lastMonthCount > 0 ? (($currentMonthCount - $lastMonthCount) / $lastMonthCount) * 100 : 0;

//         $stats[] = Stat::make('Monthly Orders', number_format($currentMonthCount))
//             ->description($change >= 0 ? "↑ {$change}% from last month" : "↓ {$change}% from last month")
//             ->descriptionIcon($change >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
//             ->descriptionColor($change >= 0 ? 'success' : 'danger')
//             ->icon('heroicon-o-calendar')
//             ->color('info')
//             ->chart($this->getMonthlyOrderData())
//             ->extraAttributes(['class' => 'hover:shadow-lg transition-shadow']);

//         // Conversion Rate - With Sparkline
//         if ($isAdmin || $isMarketer) {
//             $formCount = FormTemplate::when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))->count();
//             $conversionRate = $formCount > 0 ? ($currentMonthCount / $formCount) * 100 : 0;
            
//             $stats[] = Stat::make('Conversion Rate', round($conversionRate, 1) . '%')
//                 ->description('Form to order ratio')
//                 ->icon('heroicon-o-presentation-chart-line')
//                 ->color('success')
//                 ->chart($this->getConversionTrendData())
//                 ->extraAttributes(['class' => 'hover:shadow-lg transition-shadow']);
//         }

//         // Delivery Performance - With Goal Progress
//         if ($isAdmin || $isDeliveryAgent) {
//             $deliveredQuery = Order::where('status', Order::STATUS_DELIVERED);
//             if ($isDeliveryAgent) {
//                 $deliveredQuery->where('delivery_agent_id', $user->id);
//             }
//             $deliveredCount = $deliveredQuery->count();
//             $totalDeliverable = $orderQuery->whereIn('status', [Order::STATUS_IN_TRANSIT, Order::STATUS_DELIVERED])->count();
//             $completionRate = $totalDeliverable > 0 ? ($deliveredCount / $totalDeliverable) * 100 : 0;

//             $stats[] = Stat::make('Delivery Success', round($completionRate, 1) . '%')
//                 ->description("{$deliveredCount} of {$totalDeliverable} completed")
//                 ->icon('heroicon-o-truck')
//                 ->color('warning')
//                 ->chart($this->getDeliveryTrendData())
//                 ->extraAttributes(['class' => 'hover:shadow-lg transition-shadow']);
//         }

//         return $stats;
//     }

//     private function getOrderTrendData(): array
//     {
//         return [12, 15, 18, 22, 27, 33, 41, 45, 48, 52, 55, 60];
//     }

//     private function getMonthlyOrderData(): array
//     {
//         return [30, 40, 35, 45, 55, 60, 70, 75, 72, 80, 85, 90];
//     }

//     private function getConversionTrendData(): array
//     {
//         return [5, 10, 8, 12, 15, 18, 20, 22, 25, 28, 30, 32];
//     }

//     private function getDeliveryTrendData(): array
//     {
//         return [60, 65, 70, 75, 78, 82, 85, 88, 90, 92, 94, 96];
//     }
// }