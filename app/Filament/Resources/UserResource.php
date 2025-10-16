<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use App\Enums\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('mobile')
                            ->numeric()
                            ->required()
                            ->maxLength(20),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Security')
                    ->schema([
                        // Conditional role field
                        Forms\Components\Select::make('role')
                            ->options(function () {
                                $user = auth()->user();
                                
                                return match($user->role) {
                                    'admin' => [
                                        'admin' => 'Admin',
                                        'marketer' => 'Marketer',
                                        'call_agent' => 'Call Agent',
                                        'delivery_agent' => 'Delivery Agent',
                                        Role::MANAGER->value => 'Manager',
                                    ],
                                    default => []
                                };
                            })
                            ->required()
                            ->visible(fn () => auth()->user()->isAdmin || auth()->user()->role === 'marketer'),
                            
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)),
                            
                        Forms\Components\Toggle::make('is_blacklisted')
                            ->label('Blacklisted')
                            ->hidden(fn (): bool => !auth()->user()->is_admin),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(['first_name', 'last_name']),
                    
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'call_agent' => 'info',
                        'delivery_agent' => 'warning',
                        'marketer' => 'success',
                        'manager' => 'gray',
                    }),
                    
                Tables\Columns\IconColumn::make('is_blacklisted')
                    ->boolean()
                    ->label('Blacklisted'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'call_agent' => 'Call Agent',
                        'delivery_agent' => 'Delivery Agent',
                        'marketer' => 'Marketer',
                        'manager' => 'Manager',
                    ]),
                    
                Tables\Filters\Filter::make('blacklisted')
                    ->query(fn ($query) => $query->where('is_blacklisted', true))
                    ->label('Blacklisted Users'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
