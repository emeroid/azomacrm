<?php

namespace App\Filament\Resources;

use App\Enums\Role;
use App\Filament\Resources\FormSubmissionResource\Pages;
use App\Models\FormSubmission;
use App\Models\FormTemplate;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class FormSubmissionResource extends Resource
{
    protected static ?string $model = FormSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationLabel = "Carts Abadonment";
    protected static ?string $modelLabel = "Carts Abandonment";
    protected static ?string $slug = "carts-abandonment";


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('form_template_id')
                    ->relationship('template', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Forms\Components\KeyValue::make('data')
                    ->keyLabel('Field')
                    ->valueLabel('Value')
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('ip_address'),
                
                Forms\Components\TextInput::make('user_agent'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('template.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fullname_with_mobile')
                    ->label('Customer')
                    ->wrap(),

                Tables\Columns\TextColumn::make('address_with_state')
                    ->label('Address')
                    ->wrap(),

                Tables\Columns\TextColumn::make('parsed_products')
                    ->label('Products')
                    ->wrap(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        FormSubmission::STATUS_DRAFT => 'warning',
                        FormSubmission::STATUS_ABANDONED => 'danger',
                        FormSubmission::STATUS_SUBMITTED => 'success',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('ip_address')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('form_template_id')
                    ->label('Form Template')
                    ->options(FormTemplate::pluck('name', 'id'))
                    ->searchable(),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'abandoned' => 'Abandoned',
                        'submitted' => 'Submitted',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    
                    // Send Email Action (only for abandoned forms with email)
                    Tables\Actions\Action::make('sendEmail')
                        ->label('Send Email Reminder')
                        ->icon('heroicon-o-envelope')
                        ->color('success')
                        ->visible(fn (FormSubmission $record): bool => 
                            $record->status === FormSubmission::STATUS_ABANDONED && 
                            !empty($record->customer_email))
                        ->form([
                            Forms\Components\TextInput::make('subject')
                                ->label('Subject')
                                ->default('Complete Your Order - Reminder')
                                ->required(),
                                
                            Forms\Components\Textarea::make('message')
                                ->label('Message')
                                ->default(fn (FormSubmission $record) => 
                                    "Hi {$record->customer_name},\n\nWe noticed you started an order but didn't complete it. Your selected product: {$record->product}\n\nClick here to complete your order: [ORDER_LINK]\n\nBest regards,\nThe Team")
                                ->required()
                                ->rows(5),
                        ])
                        ->action(function (FormSubmission $record, array $data): void {
                            // Here you would integrate with your email service
                            // For now, we'll just show a notification
                            
                            Notification::make()
                                ->title('Email reminder sent')
                                ->body("Email sent to {$record->customer_email}")
                                ->success()
                                ->send();
                                
                            // You can add your email sending logic here
                            // Mail::to($record->customer_email)->send(new AbandonedCartReminder($data));
                        }),
                    
                    // Call Customer Action (only for abandoned forms with phone)
                    Tables\Actions\Action::make('callCustomer')
                        ->label('Call Customer')
                        ->icon('heroicon-o-phone')
                        ->color('primary')
                        ->visible(fn (FormSubmission $record): bool => 
                            $record->status === FormSubmission::STATUS_ABANDONED && 
                            !empty($record->customer_phone))
                        ->action(function (FormSubmission $record): void {
                            // This would typically open a phone dialer or log the call
                            Notification::make()
                                ->title('Call customer')
                                ->body("Calling {$record->customer_phone} - {$record->customer_name}")
                                ->success()
                                ->send();
                                
                            // You can integrate with your phone system here
                        }),
                    
                    // Create Order Action (only for abandoned forms)
                    Tables\Actions\Action::make('createOrder')
                        ->label('Create Order from Lead')
                        ->icon('heroicon-o-shopping-cart')
                        ->color('warning')
                        ->visible(fn (FormSubmission $record): bool => 
                            $record->status === FormSubmission::STATUS_ABANDONED)
                        ->form(fn (FormSubmission $record) => [
                            Forms\Components\Hidden::make('form_submission_id')
                                ->default($record->id),
                                
                            Section::make('Customer Information')
                                ->schema([
                                    Forms\Components\TextInput::make('full_name')
                                        ->label('Full Name')
                                        ->default($record->data['fullname'] ?? '')
                                        ->required()
                                        ->maxLength(255),
                                        
                                    Forms\Components\TextInput::make('email')
                                        ->label('Email')
                                        ->default($record->data['email'] ?? '')
                                        ->email()
                                        ->maxLength(255),
                                        
                                    Forms\Components\TextInput::make('mobile')
                                        ->label('Mobile')
                                        ->default($record->data['mobile'] ?? '')
                                        ->required()
                                        ->tel()
                                        ->maxLength(20),
                                        
                                    Forms\Components\TextInput::make('phone')
                                        ->label('Phone')
                                        ->default($record->data['phone'] ?? '')
                                        ->tel()
                                        ->maxLength(20),
                                        
                                    Forms\Components\Textarea::make('address')
                                        ->label('Address')
                                        ->default($record->data['address'] ?? '')
                                        ->required()
                                        ->columnSpanFull(),
                                        
                                    Forms\Components\TextInput::make('state')
                                        ->label('State')
                                        ->default($record->data['state'] ?? '')
                                        ->required()
                                        ->maxLength(100),
                                ])
                                ->columns(2),

                            Section::make('Order Information')
                                ->schema([
                                    Forms\Components\Hidden::make('marketer_id')
                                        ->default(Auth::id()),
                                    
                                    Forms\Components\Textarea::make('notes')
                                        ->label('Notes')
                                        ->default("Created from abandoned lead #{$record->id}")
                                        ->columnSpanFull(),
                                        
                                    Forms\Components\Select::make('product_id')
                                        ->label('Product')
                                        ->options(Product::query()->pluck('name', 'id'))
                                        ->searchable()
                                        ->required()
                                        ->default(function () use ($record) {
                                            if ($record->data) {
                                                $productParts = explode('::', $record->data['products'] ?? '');
                                                return $productParts[0] ?? null;
                                            }
                                            return null;
                                        }),
                                        
                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Quantity')
                                        ->numeric()
                                        ->default(1)
                                        ->required()
                                        ->minValue(1),

                                    Forms\Components\TextInput::make('price')
                                        ->label('Price')
                                        ->default(function () use ($record) {
                                            if ($record->data) {
                                                $productParts = explode('::', $record->data['products'] ?? '');
                                                return $productParts[1] ?? null;
                                            }
                                            return null;
                                        })
                                        ->required(),
                                ])
                                ->columns(2),
                        ])
                        ->action(function (FormSubmission $record, array $data): void {
                            // Create the order
                            $order = Order::create([
                                'full_name' => $data['full_name'],
                                'mobile' => $data['mobile'],
                                'phone' => $data['phone'] ?? null,
                                'email' => $data['email'] ?? null,
                                'address' => $data['address'],
                                'state' => $data['state'],
                                'marketer_id' => $data['marketer_id'],
                                'source_type' => Order::SOURCE_TYPE_CUSTOMER,
                                'source_id' => $record->id,
                                'notes' => $data['notes'] ?? null,
                            ]);
                            
                            $productParts = explode('::', $data['products'] ?? '');
                            if(count($productParts) >= 2) {
                                $selectedProductId = $productParts[0];
                                $selectedProductPrice = (float) $productParts[1];
                                $order->items()->create([
                                    'unit_price' => $selectedProductPrice,
                                    'product_id' => $selectedProductId,
                                ]);
                            } else {

                                // Create order item
                                $product = Product::find($data['product_id']);
                                if ($product) {
                                    $order->items()->create([
                                        'product_id' => $data['product_id'],
                                        'quantity' => $data['quantity'],
                                        'unit_price' =>$data['price'], // or get from form data if available
                                        'total_price' => $data['quantity'] * $product->price,
                                    ]);
                                }
                            }

                            // Mark the lead as recovered
                            $record->markAsSubmitted();
                            
                            Notification::make()
                                ->title('Order created successfully')
                                ->body("Order #{$order->id} created from lead")
                                ->success()
                                ->send();
                                
                            // Redirect to the order edit page
                            redirect()->route('filament.admin.resources.orders.edit', $order);
                        }),
                    
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                
                Tables\Actions\BulkAction::make('markAsContacted')
                    ->label('Mark as Contacted')
                    ->icon('heroicon-o-check')
                    ->action(function (Collection $records): void {
                        $records->each->markAsSubmitted();
                        
                        Notification::make()
                            ->title('Leads marked as contacted')
                            ->body("{$records->count()} leads updated")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormSubmissions::route('/'),
            'view' => Pages\ViewFormSubmission::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {

        $query = parent::getEloquentQuery();
        // If user is not an admin, they can only see their own requests.
        if (!in_array(Auth::user()->role, [Role::ADMIN->value])) {
            $query->whereHas('template', function($q) {
                $q->where('user_id', Auth::id())
                  ->whereIn("status", [FormSubmission::STATUS_ABANDONED, FormSubmission::STATUS_DRAFT]);
            });
        }
        return $query;
    }
}
