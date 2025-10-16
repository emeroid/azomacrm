<?php

namespace App\Filament\Resources\FundRequestResource\Widgets;

use App\Models\FundRequest;
use App\Models\Order;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection; // Import Eloquent Collection for type hint
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Pagination\CursorPaginator;

class OrderDeliveryComparison extends BaseWidget
{
    protected static ?string $heading = 'Requester Fund vs. Delivered Order Value (Current Month)';
    protected int | string | array $columnSpan = 'full';

        /**
     * Define the data to be displayed in the table.
     * Must be public to match the parent Filament\Widgets\TableWidget
     * * @return EloquentCollection|Paginator|CursorPaginator
     */
    public function getTableRecords(): EloquentCollection|Paginator|CursorPaginator // Change visibility to public
    {
        $currentMonth = now();
        
        // 1. Get all approved fund requests this month
        $approvedRequests = FundRequest::query()
            ->where('status', 'approved')
            ->whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->get();
            
        $requesterIds = $approvedRequests->pluck('user_id')->unique();
        
        // 2. Fetch all delivered orders this month linked to these requesters
        $deliveredOrders = Order::query()
            ->where('status', Order::STATUS_DELIVERED)
            ->whereMonth('updated_at', $currentMonth->month)
            ->whereYear('updated_at', $currentMonth->year)
            ->where(function ($query) use ($requesterIds) {
                $query->whereIn('marketer_id', $requesterIds)
                      ->orWhereIn('call_agent_id', $requesterIds);
            })
            ->get();
        
        $users = User::find($requesterIds)->keyBy('id');

        // 3. Aggregate data by user
        $performanceData = $requesterIds->map(function ($userId) use ($approvedRequests, $deliveredOrders, $users) {
            $userRequests = $approvedRequests->where('user_id', $userId);
            
            $userDeliveredOrders = $deliveredOrders->filter(function ($order) use ($userId) {
                return $order->marketer_id === $userId || $order->call_agent_id === $userId;
            });

            $requestedAmount = $userRequests->sum('amount');
            $deliveredOrderCount = $userDeliveredOrders->count();
            $deliveredOrderValue = $userDeliveredOrders->sum(fn ($order) => $order->getTotalPriceAttribute());
            
            return [
                'user_id' => $userId,
                'user_name' => $users[$userId]->name ?? 'N/A',
                'requested_amount' => $requestedAmount,
                'delivered_count' => $deliveredOrderCount,
                'delivered_value' => $deliveredOrderValue,
                'ratio' => $requestedAmount > 0 ? ($deliveredOrderValue / $requestedAmount) : 0,
            ];
        })->sortByDesc('delivered_value');

        // Cast the Support\Collection to an Eloquent\Collection to satisfy the type-hint
        return new EloquentCollection($performanceData->values());
    }

    public function table(Table $table): Table
    {
        // ... (Table definition remains the same) ...
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_name')
                    ->label('Requester'),

                Tables\Columns\TextColumn::make('requested_amount')
                    ->label('Approved Funds (₦)')
                    ->money('NGN', locale: 'en'),

                Tables\Columns\TextColumn::make('delivered_count')
                    ->label('Delivered Orders Count')
                    ->badge(),

                Tables\Columns\TextColumn::make('delivered_value')
                    ->label('Delivered Orders Value (₦)')
                    ->money('NGN', locale: 'en'),

                Tables\Columns\TextColumn::make('ratio')
                    ->label('Value / Fund Ratio')
                    ->formatStateUsing(fn (float $state) => number_format($state, 2) . 'x')
                    ->color(fn (float $state) => $state >= 1 ? 'success' : 'danger')
                    ->description('Delivered Value per ₦1 of Approved Fund'),
            ]);
    }
}