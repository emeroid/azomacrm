<?php

namespace App\Filament\Resources\FundRequestResource\Pages;

use App\Filament\Resources\FundRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFundRequests extends ListRecords
{
    protected static string $resource = FundRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        // We will only show widgets to managers/admins who can see all records
        if (auth()->user()->can('view_all_fund_requests')) {
             return [
                // FundRequestStats::class,
             ];
        }
        return [];
    }
}
