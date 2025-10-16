<?php

// namespace App\Filament\Widgets;

// use App\Enums\Role;
// use App\Models\Order;
// use Filament\Widgets\TableWidget;
// use Illuminate\Database\Eloquent\Builder;
// use Filament\Tables\Columns\TextColumn;
// use Filament\Tables\Columns\BadgeColumn;
// use Filament\Tables\Columns\IconColumn;
// use Filament\Tables\Actions\Action;

// class RecentOrdersTable extends TableWidget
// {
//     protected static ?string $heading = 'Recent Orders';
//     protected static ?int $sort = 4;
//     protected int|string|array $columnSpan = 'full';
//     protected static ?string $pollingInterval = '60s';

//     protected function getTableQuery(): Builder
//     {
//         $user = auth()->user();
//         $query = Order::query()
//             ->with(['marketer', 'deliveryAgent', 'callAgent'])
//             ->latest()
//             ->limit(8);
        
//         if ($user->role === Role::MARKETER->value) {
//             $query->where('marketer_id', $user->id);
//         } elseif ($user->role === Role::DELIVERY_AGENT->value) {
//             $query->where('delivery_agent_id', $user->id);
//         } elseif ($user->role === Role::CALL_AGENT->value) {
//             $query->where('call_agent_id', $user->id);
//         }

//         return $query;
//     }

//     protected function getTableColumns(): array
//     {
//         $user = auth()->user();
//         $isAdmin = $user->is_admin;
        
//         return [
//             TextColumn::make('order_number')
//                 ->label('ORDER #')
//                 ->searchable()
//                 ->color('primary')
//                 ->weight('bold')
//                 ->description(fn (Order $record) => $record->created_at->diffForHumans()),
                
//             TextColumn::make('full_name')
//                 ->label('CUSTOMER')
//                 ->searchable()
//                 ->description(fn (Order $record) => $record->mobile),
                
//             BadgeColumn::make('status')
//                 ->label('STATUS')
//                 ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
//                 ->colors([
//                     'bg-blue-100 text-blue-800' => 'processing',
//                     'bg-amber-100 text-amber-800' => 'in transit',
//                     'bg-emerald-100 text-emerald-800' => 'delivered',
//                     'bg-red-100 text-red-800' => 'cancelled',
//                 ])
//                 ->extraAttributes(['class' => 'uppercase font-bold text-xs']),
                
//             TextColumn::make('marketer.full_name')
//                 ->label('MARKETER')
//                 ->visible($isAdmin)
//                 ->placeholder('N/A'),
                
//             IconColumn::make('priority')
//                 ->label('')
//                 ->options([
//                     'heroicon-o-exclamation-circle' => fn ($state): bool => $state === 'high',
//                 ])
//                 ->colors([
//                     'danger' => 'high',
//                 ])
//                 ->visible($isAdmin),
//         ];
//     }

//     protected function getTableActions(): array
//     {
//         return [
//             Action::make('view')
//                 // ->url(fn (Order $record): string => route('filament.admin.resources.orders'))
//                 ->icon('heroicon-o-eye')
//                 ->color('gray'),
//         ];
//     }

//     protected function getTableEmptyStateHeading(): ?string
//     {
//         return 'No orders found';
//     }

//     protected function getTableEmptyStateDescription(): ?string
//     {
//         return 'Create your first order to get started';
//     }

//     protected function getTableEmptyStateIcon(): ?string
//     {
//         return 'heroicon-o-shopping-bag';
//     }

//     protected function getTableRecordsPerPageSelectOptions(): array
//     {
//         return [5, 10, 25];
//     }
// }