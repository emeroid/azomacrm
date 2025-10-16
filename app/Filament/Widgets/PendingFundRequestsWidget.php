<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\FundRequestResource;
use App\Models\FundRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingFundRequestsWidget extends BaseWidget
{
    protected static ?string $heading = 'Pending Fund Requests for Review';

    protected function getTableQuery(): Builder
    {
        return FundRequest::where('status', 'pending')->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('user.name')->label('Requester'),
            Tables\Columns\TextColumn::make('team')->badge(),
            Tables\Columns\TextColumn::make('amount')->money("NGN"),
            Tables\Columns\TextColumn::make('description')->limit(40),
            Tables\Columns\TextColumn::make('created_at')->since(),
        ];
    }

    protected function getTableActions(): array
    {
        // We reuse the actions from the main resource for consistency
        return [
            FundRequestResource::table(new Table($this))->getAction('approve'),
            FundRequestResource::table(new Table($this))->getAction('reject'),
        ];
    }

    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }
}
