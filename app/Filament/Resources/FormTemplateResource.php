<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormTemplateResource\Pages;
use App\Filament\Resources\FormTemplateResource\RelationManagers;
use App\Models\FormTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FormTemplateResource extends Resource
{
    protected static ?string $model = FormTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
                // Forms\Components\TextInput::make('name')
                //     ->required()
                //     ->maxLength(255)
                //     ->live(onBlur: true)
                //     ->afterStateUpdated(function ($state, Forms\Set $set) {
                //         $set('slug', Str::slug($state));
                //     }),
                
                // Forms\Components\TextInput::make('slug')
                //     ->required()
                //     ->maxLength(255)
                //     ->unique(ignoreRecord: true),
                
                // Forms\Components\Textarea::make('description')
                //     ->columnSpanFull(),
                
                // Forms\Components\TextInput::make('redirect_url')
                //     ->url()
                //     ->columnSpanFull(),
            
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_template')
                    ->label('Template')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->sortable(),
                
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
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable(),
                
                Tables\Filters\TernaryFilter::make('is_template')
                    ->label('Template Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('builder')
                    ->label('Open Builder')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (FormTemplate $record) => url("/builder/{$record->id}"))
                    ->openUrlInNewTab(),
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->hidden(fn (FormTemplate $record) => $record->is_template),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormTemplates::route('/'),
            'edit' => Pages\EditFormTemplate::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->when(!auth()->user()->is_admin, function ($query) {
                return $query->where(function ($query) {
                    $query->where('is_template', false)
                        ->where('user_id', auth()->id());
                });
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('name');
    }
}
