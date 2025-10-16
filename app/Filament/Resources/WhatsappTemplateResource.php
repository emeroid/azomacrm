<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsappTemplateResource\Pages;
use App\Models\WhatsappTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Set;
use Filament\Tables\Filters\TernaryFilter;

class WhatsappTemplateResource extends Resource
{
    protected static ?string $model = WhatsappTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            // Use live() to update other fields in real-time
                            ->live(onBlur: true)
                            // After the name is updated, automatically generate a slug-like key
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('key', Str::slug($state, '_'))),

                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->maxLength(255)
                            ->label('Unique Key')
                            ->helperText('A unique key for programmatic access. Auto-generated from the name.'),

                        Forms\Components\TextInput::make('category')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Group similar templates together, e.g., "order_updates".'),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(65535),

                        Forms\Components\Textarea::make('message')
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(65535)
                            ->helperText('Use placeholders like {Name} or {product_name}. They will be replaced dynamically.'),
                        
                        // This toggle will only be visible to users who have permission to make a template a default.
                        // You can control this with Filament's policy integration.
                        // Forms\Components\Toggle::make('is_default')
                        //     ->required()
                        //     ->label('Default Template')
                        //     ->helperText('Default templates are available to all users.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('key')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->searchable()
                    ->sortable(),
                // Use a ToggleColumn for quick inline editing of the 'is_default' status
                Tables\Columns\ToggleColumn::make('is_default')
                    ->label('Is Default'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Add a filter to easily show default, non-default, or all templates.
                TernaryFilter::make('is_default')
                    ->label('Is a default template')
                    ->boolean()
                    ->trueLabel('Only Default')
                    ->falseLabel('Not Default')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // No relations needed here for now
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhatsappTemplates::route('/'),
            'create' => Pages\CreateWhatsappTemplate::route('/create'),
            'edit' => Pages\EditWhatsappTemplate::route('/{record}/edit'),
        ];
    }
}

