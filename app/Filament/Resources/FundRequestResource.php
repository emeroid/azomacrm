<?php

namespace App\Filament\Resources;

use App\Enums\Role;
use App\Exports\FundRequestsDateRangeExport;
use App\Filament\Resources\FundRequestResource\Pages;
use App\Filament\Resources\FundRequestResource\Widgets\MonthlyPerformanceOverview;
use App\Models\FundRequest;
use App\Models\Product;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Excel;

class FundRequestResource extends Resource
{
    protected static ?string $model = FundRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Financials';
    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('request_type')
                            ->label('Request Type')
                            ->options([
                                'ads' => 'Ads',
                                'airtime' => 'Airtime',
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('₦')
                            ->rules(['min:100'])
                            ->live(onBlur: true)
                            ->columnSpan(1),

                        Forms\Components\Select::make('product_id')
                            ->label('Product')
                            ->options(Product::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Payment Details')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('account_name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('account_number')
                            ->required()
                            ->numeric()
                            ->length(10)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('bank_name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                    ]),

                Forms\Components\Hidden::make('team')
                    ->dehydrateStateUsing(fn (?string $state): string => $state ?? match (Auth::user()?->role) {
                        Role::CALL_AGENT->value => 'call_agent',
                        Role::MARKETER->value => 'digital_marketer',
                        default => 'unknown',
                    })
                    ->disabled(),

                Forms\Components\Hidden::make('user_id')->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        $canApprove = fn (): bool => in_array(auth()->user()?->role, ['admin', 'manager']);

        // requester options with full name (uses accessor in PHP)
        $requesterRoles = [Role::MARKETER->value, Role::CALL_AGENT->value];
        $requesterOptions = User::whereIn('role', $requesterRoles)
            ->get()
            ->mapWithKeys(fn ($u) => [$u->id => $u->full_name]);

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Requester')
                    ->searchable()
                    ->sortable(),

                // Role/Team
                Tables\Columns\TextColumn::make('team')
                    ->label('Role/Team')
                    ->formatStateUsing(fn (?string $state): string => $state ? ucwords(str_replace('_', ' ', $state)) : '-')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'call_agent' => 'success',
                        'digital_marketer' => 'primary',
                        default => 'secondary',
                    })
                    ->sortable(),

                // Product
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                // Type
                Tables\Columns\TextColumn::make('request_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'ads' => 'info',
                        'airtime' => 'primary',
                        default => 'secondary',
                    }),

                // Amount
                Tables\Columns\TextColumn::make('amount')
                    ->money('NGN', locale: 'en')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()->money('NGN'),
                    ]),

                // Requested Month (derived)
                Tables\Columns\TextColumn::make('requested_month')
                    ->label('Requested Month')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => optional($record->created_at)->format('M Y') ?? '-')
                    ->toggleable(),

                // Date formatted as requested
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Status
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->icon(fn (string $state): string => match ($state) {
                        'approved' => 'heroicon-o-check-circle',
                        'rejected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    })
                    ->sortable(),

                // Account details - visible only to approvers
                Tables\Columns\TextColumn::make('account_name')
                    ->label('Account Name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible($canApprove()),

                Tables\Columns\TextColumn::make('account_number')
                    ->label('Account Number')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible($canApprove()),

                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible($canApprove()),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Processed By')
                    ->default('-')
                    ->toggleable(),
            ])
            ->filters([
                // SelectFilter::make('user_id')
                //     ->label('Requester')
                //     ->options($requesterOptions)
                //     ->searchable()
                //     ->indicator(fn ($values) => $values ? (is_array($values) ? count($values) : 1) : null),

                // SelectFilter::make('status')
                //     ->label('Status')
                //     ->options([
                //         'pending' => 'Pending',
                //         'approved' => 'Approved',
                //         'rejected' => 'Rejected',
                //     ])
                //     ->native(false),

                // SelectFilter::make('request_type')
                //     ->label('Request Type')
                //     ->options([
                //         'ads' => 'Ads',
                //         'airtime' => 'Airtime',
                //     ])
                //     ->native(false),

                // Date range filter (created_at)
                Filter::make('created_at')
                    ->label('Created (Range)')
                    ->form([
                        DatePicker::make('created_from')->label('From'),
                        DatePicker::make('created_until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),

                // Month filter: easy quick select for month/year values found in DB (last 12 months)
                // SelectFilter::make('month_year')
                //     ->label('Requested Month')
                //     ->options(function () {
                //         $months = [];
                //         $start = now()->subMonths(11)->startOfMonth();
                //         for ($i = 0; $i < 12; $i++) {
                //             $m = $start->copy()->addMonths($i);
                //             $months[$m->format('M Y')] = $m->format('M Y'); // label => value
                //         }
                //         return array_reverse($months); // newest first
                //     })
                //     ->query(fn (Builder $q, $value) => $q->whereMonth('created_at', Carbon::createFromFormat('M Y', $value)->month)
                //                                                ->whereYear('created_at', Carbon::createFromFormat('M Y', $value)->year)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (FundRequest $record): bool => $record->status === 'pending' && $record->user_id === Auth::id()),

                ActionGroup::make([
                    Action::make('view')
                        ->label('View')
                        ->icon('heroicon-o-eye')
                        ->url(fn (FundRequest $record) => self::getUrl('edit', ['record' => $record->id])),
                        
                    Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (FundRequest $record) {
                            $record->update([
                                'status' => 'approved',
                                'approved_by' => Auth::id(),
                                'processed_at' => now(),
                            ]);
                            Notification::make()->title('Request Approved')->success()->send();
                        })
                        ->visible(fn (FundRequest $record): bool => $record->status === 'pending' && $canApprove()),

                    Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('Reason for Rejection')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->action(function (FundRequest $record, array $data) {
                            $record->update([
                                'status' => 'rejected',
                                'reason' => $data['reason'],
                                'approved_by' => Auth::id(),
                                'processed_at' => now(),
                            ]);
                            Notification::make()->title('Request Rejected')->danger()->send();
                        })
                        ->visible(fn (FundRequest $record): bool => $record->status === 'pending' && $canApprove()),

                    Action::make('account')
                        ->label('Account Info')
                        ->icon('heroicon-o-wallet')
                        ->modalHeading('Requester Bank Details')
                        ->modalDescription(fn (FundRequest $record) => $record->user->name . ' — ' . ucfirst(str_replace('_', ' ', $record->team)))
                        ->modalSubmitActionLabel('Close')
                        ->modalCancelAction(false)
                        ->form([
                            Forms\Components\TextInput::make('account_name')
                                ->default(fn (FundRequest $record) => $record->account_name)
                                ->label('Account Name')
                                ->disabled(),

                            Forms\Components\TextInput::make('account_number')
                                ->default(fn (FundRequest $record) => $record->account_number)
                                ->label('Account Number')
                                ->disabled(),

                            Forms\Components\TextInput::make('bank_name')
                                ->default(fn (FundRequest $record) => $record->bank_name)
                                ->label('Bank Name')
                                ->disabled(),
                        ])
                        ->visible($canApprove()),
                ])->label('More'),
            ])
            ->bulkActions([
                // You can add bulk approve/reject later if needed
            ])
            ->headerActions([
                Action::make('performance')
                    ->label('Performance Analytics')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->color('primary')
                    ->url(self::getUrl('performance')), // keep header button
                Action::make('export_filtered')
                    ->label('Export (Date Range)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->default(now()->subDays(30))
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $filteredQuery = FundRequest::query()
                            ->whereBetween('created_at', [
                                Carbon::parse($data['start_date'])->startOfDay(),
                                Carbon::parse($data['end_date'])->endOfDay(),
                            ]);

                        $user = Auth::user();
                        $managerRoles = ["admin", "manager"];
                        if (is_object($user) && method_exists($user, 'role') && !in_array($user->role, $managerRoles)) {
                            $filteredQuery->where('user_id', $user->id);
                        }

                        $fileName = "FundRequests-{$data['start_date']}-to-{$data['end_date']}.xlsx";
                        $excel = app(Excel::class);

                        return $excel->download(
                            new FundRequestsDateRangeExport($filteredQuery),
                            $fileName
                        );
                    })
                    ->visible(fn (): bool => in_array(Auth::user()?->role, ['admin', 'manager'])),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        $managerRoles = ["admin", "manager"];
        if (is_object($user) && method_exists($user, 'role') && !in_array($user->role, $managerRoles)) {
            $query->where('user_id', $user->id);
        }
        return $query;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        // Add the monthly performance overview (top of resource)
        return [
            MonthlyPerformanceOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFundRequests::route('/'),
            'create' => Pages\CreateFundRequest::route('/create'),
            'edit' => Pages\EditFundRequest::route('/{record}/edit'),
            'performance' => Pages\FundRequestPerformance::route('/performance'),
        ];
    }
}
