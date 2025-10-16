<?php

namespace App\Filament\Widgets;

use App\Enums\Role;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class AnalyticsTableWidget extends BaseWidget
{
    protected static ?int $sort = 4;

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
        
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();

        // Start with the base query for the selected date range
        $query = Order::query()
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        // Apply role-based filtering
        // Admins and Managers see everything, so we only add filters for other roles.
        if ($user->role === Role::MARKETER->value) {
            $query->where('marketer_id', $user->id);
        } elseif ($user->role === Role::DELIVERY_AGENT->value) {
            $query->where('delivery_agent_id', $user->id);
        } elseif ($user->role === Role::CALL_AGENT->value) {
            $query->where('call_agent_id', $user->id);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'processing' => 'gray',
                        'confirmed' => 'primary',
                        'scheduled' => 'info',
                        'in_transit' => 'warning',
                        'delivered' => 'success',
                        'cancelled', 'returned' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
            ]);
    }

    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }
}

