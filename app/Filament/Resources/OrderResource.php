<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Enums\Role;
use App\Events\OrderAssigned;
use App\Exports\OrdersExport;
use App\Services\OrderStatusLogger;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Filament\Notifications\Collection;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Maatwebsite\Excel\Facades\Excel;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $modelLabel = 'Order';
    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isAdmin = $user->is_admin;
        $isMarketer = $user->role === Role::MARKETER->value;
        $isDeliveryAgent = $user->role === Role::DELIVERY_AGENT->value;

        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Order Details')
                        ->schema([
                            Section::make('Customer Information')
                                ->schema([
                                    Forms\Components\TextInput::make('full_name')
                                        ->required()
                                        ->maxLength(255),
                                        
                                    Forms\Components\TextInput::make('email')
                                        ->email()
                                        ->maxLength(255),
                                        
                                    Forms\Components\TextInput::make('mobile')
                                        ->required()
                                        ->tel()
                                        ->maxLength(20),
                                        
                                    Forms\Components\TextInput::make('phone')
                                        ->tel()
                                        ->maxLength(20),
                                        
                                    Forms\Components\Textarea::make('address')
                                        ->required()
                                        ->columnSpanFull(),
                                        
                                    Forms\Components\TextInput::make('state')
                                        ->required()
                                        ->maxLength(100),
                                ])
                                ->columns(2),

                            Section::make('Order Information')
                                ->schema([
                                    Forms\Components\Hidden::make('marketer_id')
                                        ->default($user->id),
                                    
                                    Forms\Components\Textarea::make('notes')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Wizard\Step::make('Order Items')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->relationship()
                                ->schema([
                                    Forms\Components\Select::make('product_id')
                                        ->label('Product')
                                        ->options(Product::query()->pluck('name', 'id'))
                                        ->searchable()
                                        ->required(),
                                        
                                    Forms\Components\TextInput::make('quantity')
                                        ->numeric()
                                        ->default(1)
                                        ->required()
                                        ->minValue(1)
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Forms\Set $set, $get) {
                                            self::calculateTotalPrice($set, $get);
                                        }),

                                    Forms\Components\TextInput::make('unit_price')
                                        ->numeric()
                                        ->required()
                                        ->step(0.01),
                                        //->reactive()
                                        //->afterStateUpdated(function ($state, Forms\Set $set, $get) {
                                        //    self::calculateTotalPrice($set, $get);
                                        //}),
                                        
                                    Forms\Components\TextInput::make('total_price')
                                        ->numeric()
                                        ->disabled()
                                ])
                                ->columns(4)
                                ->columnSpanFull()
                                ->defaultItems(1)
                                ->addActionLabel('Add Product')
                                ->mutateRelationshipDataBeforeCreateUsing(
                                    fn (array $data): array => array_merge($data, [
                                        'total_price' => ($data['quantity'] ?? 1) * ($data['unit_price'] ?? 0)
                                    ])
                                )
                                ->disabled(fn ($context, $operation) => 
                                    $operation === 'edit' && 
                                    !self::canEditOrder(Auth::user())),
                        ]),

                    Wizard\Step::make('Agent Assignment')
                        ->schema([
                
                            Forms\Components\Select::make('call_agent_id')
                                ->label('Call Agent')
                                ->relationship(
                                    name: 'callAgent',
                                    titleAttribute: 'email',
                                    modifyQueryUsing: fn (Builder $query) => $query->where('role', Role::CALL_AGENT->value)
                                )
                                ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->full_name} <{$record->email}>")
                                ->searchable(['first_name', 'last_name', 'email'])
                                ->preload()
                                ->visible(fn (): bool => $isAdmin || $isMarketer)
                                ->disabled(fn ($get) => 
                                    !in_array($get('status'), [null, Order::STATUS_PROCESSING])),

                            // Forms\Components\Textarea::make('delivery_notes')
                            //     ->columnSpanFull()
                            //     ->visible(fn (): bool => $isAdmin || $isDeliveryAgent),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    protected static function calculateTotalPrice(Forms\Set $set, $get): void
    {
        $quantity = (float) $get('quantity');
        $unitPrice = (float) $get('unit_price');
        $set('total_price', number_format($quantity * $unitPrice, 2, '.', ''));
    }

    protected static function canEditOrder(User $user): bool
    {
        return $user->is_admin || $user->role === Role::MARKETER->value;
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $isAdmin = $user->is_admin;
        $isMarketer = $user->role === Role::MARKETER->value;
        $isDeliveryAgent = $user->role === Role::DELIVERY_AGENT->value;
        $isCallAgent = $user->role === Role::CALL_AGENT->value;

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($user, $isMarketer, $isDeliveryAgent, $isCallAgent) {
                if ($isMarketer) {
                    return $query->where('marketer_id', $user->id)->orderBy("updated_at", "desc");
                }

                if ($isCallAgent) {
                    return $query->where('call_agent_id', $user->id)->orderBy("updated_at", "desc");
                }

                if ($isDeliveryAgent) {
                    return $query->where('delivery_agent_id', $user->id)->orderBy("updated_at", "desc")->whereIn('status', [
                        Order::STATUS_IN_TRANSIT, 
                        Order::STATUS_CONFIRMED,
                        Order::STATUS_DELIVERED,
                        Order::STATUS_RETURNED,
                    ]);
                }
                return $query->orderBy("updated_at", "desc");
            })
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->sortable()
                    ->label('Order #'),
                    
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('mobile')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_PROCESSING => self::getStatusStyles()[$state]['color'],
                        Order::STATUS_IN_TRANSIT => self::getStatusStyles()[$state]['color'],
                        Order::STATUS_DELIVERED => self::getStatusStyles()[$state]['color'],
                        // Order::STATUS_CANCELLED => self::getStatusStyles()[$state]['color'],
                        Order::STATUS_CONFIRMED => self::getStatusStyles()[$state]['color'],
                        Order::STATUS_NOT_AVAILABLE => self::getStatusStyles()[$state]['color'],
                        Order::STATUS_NOT_INTERESTED => self::getStatusStyles()[$state]['color'],
                        Order::STATUS_NOT_READY => self::getStatusStyles()[$state]['color'],
                        Order::STATUS_RETURNED => self::getStatusStyles()[$state]['color'],
                        Order::STATUS_NOT_REACHABLE  => self::getStatusStyles()[$state]['color'],
                        Order::STATUS_PHONE_SWITCHED_OFF => self::getStatusStyles()[$state]['color'],
                        Order::STATUS_TRAVELLED => self::getStatusStyles()[$state]['color'],
                        Order::STATUS_SCHEDULED => self::getStatusStyles()[$state]['color'],
                        default => 'gray',
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('marketer.full_name')
                    ->label('Marketer')
                    ->visible(fn (): bool => $isAdmin),

                Tables\Columns\TextColumn::make('created_at')
                    ->label("Date")
                    ->date(),
                    
                Tables\Columns\TextColumn::make('deliveryAgent.email')
                    ->label('Delivery Agent')
                    ->formatStateUsing(function ($state, Order $record) {
                        if (!$record->deliveryAgent) return '';
                
                        return <<<EOT
                            <strong>{$record->deliveryAgent->full_name}</strong><br>
                            📱 {$record->deliveryAgent->mobile}<br>
                            ✉️ {$state}
                            EOT;
                    })
                    ->html(),
                
            Tables\Columns\TextColumn::make('callAgent.email')
                    ->label('Call Agent')
                    ->formatStateUsing(function ($state, Order $record) {
                        if (!$record->callAgent) return '';
                
                        return <<<EOT
                            <strong>{$record->callAgent->full_name}</strong><br>
                            📱 {$record->callAgent->mobile}<br>
                            ✉️ {$state}
                            EOT;
                                })
                                ->html(),            
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Order::STATUS_PROCESSING => 'Processing',
                        Order::STATUS_IN_TRANSIT => 'In Transit',
                        Order::STATUS_DELIVERED => 'Delivered',
                        // Order::STATUS_CANCELLED => 'Cancelled',
                        Order::STATUS_RETURNED => 'Returned',
                        Order::STATUS_CONFIRMED => 'Confirmed',
                        Order::STATUS_NOT_READY => 'Not Ready',
                        Order::STATUS_NOT_INTERESTED => 'Not Interested',
                        Order::STATUS_NOT_REACHABLE => 'Not Reachable',
                        Order::STATUS_PHONE_SWITCHED_OFF => 'Phone Switched Off',
                        Order::STATUS_TRAVELLED => 'Travelled',
                        Order::STATUS_NOT_AVAILABLE => 'Not Available'
                    ]),
                Tables\Filters\SelectFilter::make('marketer_id')
                    ->label('Marketer')
                    ->relationship('marketer', 'email')
                    ->visible(fn (): bool => $isAdmin),
            ])
            ->headerActions([

                Tables\Actions\Action::make('exportOrders')
                    ->label('Export Orders')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form(function () {
                        $user = auth()->user();
            
                        // Managers/Admins → see full export form
                        if (in_array($user->role, [Role::MANAGER->value, Role::ADMIN->value])) {
                            return [
                                Forms\Components\DatePicker::make('start_date')->required(),
                                Forms\Components\DatePicker::make('end_date')->required(),
                                Forms\Components\Select::make('status')
                                    ->label('Order Status')
                                    ->options(Order::getStatuses())
                                    ->required(),
                                Forms\Components\Select::make('user_type')
                                    ->label('Filter by')
                                    ->options([
                                        'marketer_id' => 'Marketer',
                                        'call_agent_id' => 'Call Agent',
                                        'delivery_agent_id' => 'Delivery Agent',
                                    ]),
                                    Forms\Components\Select::make('user_id')->label('Select User') 
                                        ->options(fn (callable $get) => User::query() 
                                        ->when($get('user_type') === 'marketer_id', fn ($q) => $q->where('role', 'marketer')) 
                                        ->when($get('user_type') === 'call_agent_id', fn ($q) => $q->where('role', 'call_agent')) ->when($get('user_type') === 'delivery_agent_id', fn ($q) => $q->where('role', 'delivery_agent'))
                                        ->get()
                                        ->mapWithKeys(fn ($user) => [$user->id => $user->full_name])) 
                                        ->searchable() 
                                        ->required(),
                            ];
                        }
            
                        // Agents → only pick date range & status
                        return [
                            Forms\Components\DatePicker::make('start_date')->required(),
                            Forms\Components\DatePicker::make('end_date')->required(),
                            Forms\Components\Select::make('status')
                                ->label('Order Status')
                                ->options(Order::getStatuses())
                                ->required(),
                        ];
                    })
                    ->action(function (array $data) {
                        $user = auth()->user();
            
                        // Managers/Admins can filter by selected user
                        $userType = null;
                        $userId = null;
            
                        if (in_array($user->role, [Role::MANAGER->value, Role::ADMIN->value])) {
                            $userType = $data['user_type'] ?? null;
                            $userId = $data['user_id'] ?? null;
                        }
            
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\OrdersExport(
                                $data['start_date'],
                                $data['end_date'],
                                $userType,
                                $userId,
                                $data['status']
                            ),
                            'orders_' . $data['status'] . '.xlsx'
                        );
                    })
                    // Make visible to all user roles
                    ->visible(fn(): bool => in_array(auth()->user()?->role, [
                        Role::ADMIN->value,
                        Role::MANAGER->value,
                        Role::MARKETER->value,
                        Role::DELIVERY_AGENT->value,
                        Role::CALL_AGENT->value,
                    ])),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Tables\Actions\ViewAction::make(),
                    
                    Tables\Actions\EditAction::make()
                        ->visible(function (Order $record) use ($user) {
                            if ($user->is_admin) return true;
                            if ($user->role === Role::MARKETER->value) {
                                return $record->marketer_id === $user->id && 
                                       $record->status === Order::STATUS_PROCESSING;
                            }
                            return false;
                        }),

                    Tables\Actions\Action::make('invoice')
                        ->label('View Invoice')
                        ->icon('heroicon-o-document-text')
                        ->url(fn (Order $record): string => OrderResource::getUrl('invoice', ['record' => $record]))
                        ->openUrlInNewTab()
                        ->visible(fn (): bool => true), // All users can access
                    
                    Tables\Actions\Action::make('copy')
                        ->label('Copy Order Info')
                        ->icon('heroicon-o-clipboard')
                        ->modalHeading('Order Information')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->modalContent(function (Order $record) {
                            $orderInfo = self::generateOrderInfoText($record);
                            
                            return view('filament.resources.order-resource.pages.copy-order-info', [
                                'orderInfo' => $orderInfo
                            ]);
                        })
                        ->action(function () {
                            // This is intentionally empty as we handle the copy in the view
                        }),

                    ...self::getDeliveryAgentOutcomeAction(),

                    Tables\Actions\Action::make('cancel')
                        ->label('Cancel Order')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Order $record) {
                            $record->update(['status' => Order::STATUS_CANCELLED]);
                            OrderStatusLogger::logStatusChange($record, Order::STATUS_CANCELLED);
                            Notification::make()
                                ->title('Order cancelled')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Order $record): bool => 
                            auth()->user()->role === Role::CALL_AGENT->value &&
                            in_array($record->status, [Order::STATUS_PROCESSING]))
                        ->requiresConfirmation(),

                    Tables\Actions\Action::make('schedule')
                            ->label('Schedule Order')
                            ->icon('heroicon-o-calendar')
                            ->form([
                                Forms\Components\DateTimePicker::make('scheduled_at')
                                    ->label('Scheduled Date & Time')
                                    ->required()
                                    ->minDate(now())
                                    ->native(false)
                                    ->displayFormat('M j, Y g:i A')
                                    ->closeOnDateSelection(),
                                    
                                Forms\Components\Textarea::make('notes')
                                    ->label('Scheduling Notes')
                                    ->columnSpanFull(),
                            ])
                            ->action(function (Order $record, array $data): void {
                                $record->update([
                                    'scheduled_at' => $data['scheduled_at'],
                                    'status' => Order::STATUS_SCHEDULED,
                                ]);

                                OrderStatusLogger::logStatusChange($record, Order::STATUS_SCHEDULED);
                                
                                Notification::make()
                                    ->title('Order scheduled successfully')
                                    ->body("Scheduled for: " . $data['scheduled_at']->format('M j, Y g:i A'))
                                    ->success()
                                    ->send();
                            })
                            ->visible(fn (Order $record): bool => 
                                (auth()->user()->role === Role::CALL_AGENT || auth()->user()->role === Role::MARKETER->value) &&
                                $record->status === Order::STATUS_PROCESSING),

                    Tables\Actions\Action::make('assign_call_agent')
                        ->label('Assign Call Agent')
                        ->icon('heroicon-o-phone')
                        ->form([
                            Forms\Components\Select::make('call_agent_id')
                                ->label('Call Agent')
                                ->options(
                                    User::where('role', Role::CALL_AGENT->value)
                                        ->get()
                                        ->mapWithKeys(fn (User $user) => [
                                            $user->id => "{$user->full_name} <{$user->mobile}>"
                                        ])
                                )
                                ->searchable(['first_name', 'last_name', 'email', 'mobile'])
                                ->required()
                        ])
                        ->action(function (Order $record, array $data): void {
                            
                            $record->update([
                                'call_agent_id' => $data['call_agent_id'],
                                'status' => Order::STATUS_PROCESSING
                            ]);

                            OrderStatusLogger::logStatusChange($record, Order::STATUS_PROCESSING, 'Order assigned to call agent');
                            OrderAssigned::dispatch($record);
                            Notification::make()
                                ->title('Call agent assigned successfully')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Order $record): bool => 
                            (auth()->user()->is_admin || auth()->user()->role === Role::MARKETER->value) &&
                            $record->status === Order::STATUS_PROCESSING),
                    
                    // Tables\Actions\Action::make('history')
                    //     ->url(fn (Order $record): string => route('filament.admin.resources.orders.communications', $record))
                    //     ->icon('heroicon-o-eye')
                    //     ->color('gray'),
                    //     Tables\Actions\DeleteAction::make()
                    //     ->visible(fn (): bool => $record->marketer_id === auth()->user()->id && $record->status === Order::STATUS_PROCESSING),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('assign_call_agent')
                        ->label('Assign Call Agent')
                        ->icon('heroicon-o-phone')
                        ->color('primary')
                        ->form([
                            Forms\Components\Select::make('call_agent_id')
                                ->label('Call Agent')
                                ->options(
                                    User::where('role', Role::CALL_AGENT->value)
                                        ->get()
                                        ->mapWithKeys(fn (User $user) => [
                                            $user->id => "{$user->full_name} <{$user->mobile}>"
                                        ])
                                )
                                ->searchable(['full_name', 'email', 'mobile'])
                                ->required()
                        ])
                        ->action(function ($records, array $data): void {
                            $callAgentId = $data['call_agent_id'];
                            $records->each(function ($record) use ($callAgentId) {
                                $record->update([
                                    'call_agent_id' => $callAgentId,
                                    'status' => Order::STATUS_PROCESSING,
                                ]);
                                OrderStatusLogger::logStatusChange($record, Order::STATUS_PROCESSING, 'Order assigned to call agent via bulk action');
                                OrderAssigned::dispatch($record);
                            });
                            
                            Notification::make()
                                ->title('Orders assigned successfully')
                                ->body(count($records) . ' orders have been assigned to the call agent.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (): bool => 
                            auth()->user()->is_admin || auth()->user()->role === Role::MARKETER->value),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'communications' => Pages\OrderHistory::route('/{record}/communications'),
            'invoice' => Pages\OrderInvoice::route('/{record}/invoice'), // Add this line
        ];
    }

    protected static function getDeliveryAgentOutcomeAction() {
        $STATUS_OUTCOME = [
            // Order::STATUS_NOT_READY => 'Not Ready',
            // Order::STATUS_NOT_INTERESTED => 'Not Interested',
            // Order::STATUS_NOT_REACHABLE => 'Not Reachable',
            // Order::STATUS_PHONE_SWITCHED_OFF => 'Phone Switched Off',
            // Order::STATUS_TRAVELLED => 'Travelled',
            // Order::STATUS_NOT_AVAILABLE => 'Not Available',
            Order::STATUS_RETURNED => 'Returned',
            Order::STATUS_DELIVERED => 'Delivered',
        ];

        $tableStatus = [];

        foreach($STATUS_OUTCOME as $key => $outcome) {
            $tableStatus[] = Tables\Actions\Action::make($key)
                        ->label("Mark as " . $outcome)
                        ->icon(fn() => self::getStatusStyles()[$key]['icon'])
                        ->action(function (Order $record) use ($key, $outcome) {
                            $record->update(['status' => $key]);
                            OrderStatusLogger::logStatusChange($record, $key);
                            Notification::make()
                                ->title("Order marked as " . $outcome)
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Order $record): bool => 
                            auth()->user()->role === Role::DELIVERY_AGENT->value &&
                            $record->status === Order::STATUS_CONFIRMED)
                        ->requiresConfirmation();
        }

        return $tableStatus;
    }

    public static function getStatusStyles(): array
    {
        return [
            Order::STATUS_PROCESSING => [
                'color' => 'info',
                'icon'  => 'heroicon-o-badge-check',
                'label' => 'Confirmed',
            ],
            Order::STATUS_IN_TRANSIT => [
                'color' => 'warning',
                'icon'  => 'heroicon-o-truck',
                'label' => 'In Transit',
            ],
            Order::STATUS_CONFIRMED => [
                'color' => 'indigo',
                'icon'  => 'heroicon-o-badge-check',
                'label' => 'Confirmed',
            ],
            Order::STATUS_DELIVERED => [
                'color' => 'success',
                'icon'  => 'heroicon-o-check-circle',
                'label' => 'Delivered',
            ],
            Order::STATUS_NOT_READY => [
                'color' => 'gray',
                'icon'  => 'heroicon-o-clock',
                'label' => 'Not Ready',
            ],
            Order::STATUS_NOT_INTERESTED => [
                'color' => 'amber',
                'icon'  => 'heroicon-o-hand-raised',
                'label' => 'Not Interested',
            ],
            Order::STATUS_NOT_REACHABLE => [
                'color' => 'yellow',
                'icon'  => 'heroicon-o-phone-arrow-up-right',
                'label' => 'Not Reachable',
            ],
            Order::STATUS_PHONE_SWITCHED_OFF => [
                'color' => 'zinc',
                'icon'  => 'heroicon-o-phone-x-mark',
                'label' => 'Phone Off',
            ],
            Order::STATUS_TRAVELLED => [
                'color' => 'blue',
                'icon'  => 'heroicon-o-globe-alt',
                'label' => 'Travelled',
            ],
            Order::STATUS_NOT_AVAILABLE => [
                'color' => 'rose',
                'icon'  => 'heroicon-o-x-circle',
                'label' => 'Not Available',
            ],
            Order::STATUS_RETURNED => [
                'color' => 'danger',
                'icon'  => 'heroicon-o-arrow-uturn-left',
                'label' => 'Returned',
            ],
            Order::STATUS_SCHEDULED => [
                'color' => 'cyan',
                'icon'  => 'heroicon-o-calendar-days',
                'label' => 'Scheduled',
            ],
        ];
    }

    protected static function generateOrderInfoText(Order $record): string
    {
        $info = [];
        $info[] = "Order #: {$record->order_number}";
        $info[] = "Name: {$record->full_name}";
        $info[] = "Mobile: {$record->mobile}";
        $info[] = "Address: {$record->address}";
        $info[] = "State: {$record->state}";
        
        if ($record->phone) {
            $info[] = "Phone: {$record->phone}";
        }
        
        if ($record->email) {
            $info[] = "Email: {$record->email}";
        }
        
        $info[] = "\nProducts:";
        foreach ($record->items as $item) {
            $info[] = "- {$item->product->name}";
            $info[] = "  Qty: {$item->quantity}";
            $info[] = "  Price: " . number_format($item->unit_price, 2);
            $info[] = "  Total: " . number_format($item->total_price, 2);
        }
        
        return implode("\n", $info);
    }

}