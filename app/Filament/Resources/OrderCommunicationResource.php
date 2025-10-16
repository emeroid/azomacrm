<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderCommunicationResource\Pages;
use App\Filament\Resources\OrderCommunicationResource\RelationManagers;
use App\Models\OrderCommunication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderCommunicationResource extends Resource
{
    protected static ?string $model = OrderCommunication::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $modelLabel = 'Communication';

    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->relationship('order', 'order_number')
                    ->disabled(),

                Forms\Components\Select::make('agent_id')
                    ->relationship('agent', 'email')
                    ->disabled(),

                Forms\Components\Select::make('type')
                    ->options([
                        'call' => 'Call',
                        'email' => 'Email',
                        'note' => 'Note',
                    ])
                    ->disabled(),

                Forms\Components\Textarea::make('content')
                    ->columnSpanFull()
                    ->disabled(),

                Forms\Components\TagsInput::make('labels')
                    ->disabled(),

                // Forms\Components\Select::make('outcome')
                //     ->options(array_combine(
                //         OrderCommunication::OUTCOMES,
                //         array_map('ucfirst', OrderCommunication::OUTCOMES)
                //     ))
                //     ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('agent.full_name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'call' => 'info',
                        'email' => 'success',
                        'note' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('outcome')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'call' => 'Call',
                        'email' => 'Email',
                        'note' => 'Note',
                    ]),

                Tables\Filters\SelectFilter::make('outcome')
                    ->options(array_combine(
                        OrderCommunication::OUTCOMES,
                        array_map('ucfirst', OrderCommunication::OUTCOMES)
                    )),

                Tables\Filters\SelectFilter::make('agent_id')
                    ->relationship('agent', 'full_name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // Only allow viewing
            ])
            ->bulkActions([]); // Disable bulk actions
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderCommunications::route('/'),
            // 'view' => Pages\ViewOrderCommunication::route('/{record}'),
        ];
    }
}
