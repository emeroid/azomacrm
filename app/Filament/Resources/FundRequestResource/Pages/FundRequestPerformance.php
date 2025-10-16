<?php

namespace App\Filament\Resources\FundRequestResource\Pages;

use App\Filament\Resources\FundRequestResource;
use App\Filament\Resources\FundRequestResource\Widgets\FundRequestFiltersWidget;
use App\Filament\Resources\FundRequestResource\Widgets\MonthlyPerformanceOverview;
use App\Filament\Resources\FundRequestResource\Widgets\SpilloverOrdersWidget;
use App\Models\FundRequest;
use App\Models\Order;
use App\Models\User;
use App\Enums\Role;
use Filament\Resources\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FundRequestPerformance extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = FundRequestResource::class;
    protected static ?string $title = 'Fund Request Performance Analytics';
    protected static string $view = 'filament.resources.fund-request-resource.pages.fund-request-performance';

    public ?array $filterData = [
        'user_id' => null,
        'start_date' => null,
        'end_date' => null,
    ];

    // ✅ Listen for updates from the filter widget
    protected $listeners = [
        'filterUpdated' => 'applyFilters',
    ];

    public function mount(): void
    {
        $this->filterData['start_date'] = now()->startOfMonth()->format('Y-m-d');
        $this->filterData['end_date'] = now()->endOfDay()->format('Y-m-d');

        if ($userId = request()->query('user')) {
            $this->filterData['user_id'] = $userId;
        }
    }

    // ✅ Render filter widget at top
    protected function getHeaderWidgets(): array
    {
        return [
            FundRequestFiltersWidget::class,
        ];
    }

    // ✅ Apply filters when updated from widget
    public function applyFilters(array $filters): void
    {
        $this->filterData = $filters;

        if (method_exists($this, 'resetTable')) {
            $this->resetTable();
        } else {
            $this->cachedTableColumns = [];
            $this->cacheTableColumns();
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $filters = $this->filterData;
                $requesterRoles = [Role::MARKETER->value, Role::CALL_AGENT->value];

                $query = User::query()
                    ->whereIn('role', $requesterRoles)
                    ->whereHas('fundRequests', function (Builder $q) use ($filters) {
                        $q->where('status', 'approved'); // ✅ Only approved
                    
                        if ($filters['start_date']) {
                            $q->whereDate('created_at', '>=', $filters['start_date']);
                        }
                        if ($filters['end_date']) {
                            $q->whereDate('created_at', '<=', $filters['end_date']);
                        }
                    })
                    ->when($filters['user_id'], fn (Builder $query) => $query->where('id', $filters['user_id']));

                $approvedRequestsSubquery = FundRequest::query()
                    ->select(DB::raw('sum(amount)'))
                    ->where('status', 'approved')
                    ->whereColumn('user_id', 'users.id')
                    ->when($filters['start_date'], fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['start_date']))
                    ->when($filters['end_date'], fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['end_date']));

                $deliveredOrdersCountSubquery = Order::query()
                    ->select(DB::raw('count(*)'))
                    ->where('status', Order::STATUS_DELIVERED)
                    ->where(fn (Builder $q) => $q->whereColumn('marketer_id', 'users.id')->orWhereColumn('call_agent_id', 'users.id'))
                    ->when($filters['start_date'], fn (Builder $query) => $query->whereDate('updated_at', '>=', $filters['start_date']))
                    ->when($filters['end_date'], fn (Builder $query) => $query->whereDate('updated_at', '<=', $filters['end_date']));

                $deliveredOrdersValueSubquery = Order::query()
                    ->selectRaw('SUM(order_items.unit_price * order_items.quantity)')
                    ->join('order_items', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.status', Order::STATUS_DELIVERED)
                    ->where(function (Builder $q) {
                        $q->whereColumn('orders.marketer_id', 'users.id')
                          ->orWhereColumn('orders.call_agent_id', 'users.id');
                    })
                    ->when($filters['start_date'], fn (Builder $query) =>
                        $query->whereDate('orders.updated_at', '>=', $filters['start_date'])
                    )
                    ->when($filters['end_date'], fn (Builder $query) =>
                        $query->whereDate('orders.updated_at', '<=', $filters['end_date'])
                    );

                $query->select('users.*')
                    ->selectSub($approvedRequestsSubquery, 'requested_amount')
                    ->selectSub($deliveredOrdersCountSubquery, 'delivered_count')
                    ->selectSub($deliveredOrdersValueSubquery, 'delivered_value');

                return $query;
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Requester')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('requested_amount')
                    ->label('Approved Funds')
                    ->money('NGN')
                    ->sortable()
                    ->default(0),
                TextColumn::make('delivered_count')
                    ->label('Delivered Count')
                    ->numeric()
                    ->alignCenter()
                    ->sortable()
                    ->default(0),
                TextColumn::make('delivered_value')
                    ->label('Delivered Value')
                    ->money('NGN')
                    ->sortable()
                    ->default(0),
                TextColumn::make('ratio')
                    ->label('Ratio')
                    ->state(function (User $record): float {
                        if ($record->requested_amount > 0) {
                            return ($record->delivered_value ?? 0) / $record->requested_amount;
                        }
                        return 0;
                    })
                    ->formatStateUsing(fn ($state): string => number_format($state, 2) . 'x')
                    ->color(fn ($state): string => $state >= 1 ? 'success' : 'danger')
                    ->weight('bold')
                    ->sortable(false),
            ]);
    }

    protected function getFooterWidgets(): array
    {
        return [
            MonthlyPerformanceOverview::make(['filters' => $this->filterData]),
            SpilloverOrdersWidget::make(['filters' => $this->filterData]),
        ];
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-presentation-chart-line';
    }

    public static function getNavigationLabel(): string
    {
        return 'Performance';
    }
}
