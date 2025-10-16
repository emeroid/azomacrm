<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UserActivityLogWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent User Activity';

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return ActivityLog::with('user')->latest()->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('user.name')->label('User'),
            Tables\Columns\TextColumn::make('action')->label('Action'),
            Tables\Columns\TextColumn::make('subject_type')->label('Entity'),
            Tables\Columns\TextColumn::make('created_at')->label('Timestamp')->dateTime(),
        ];
    }
}
