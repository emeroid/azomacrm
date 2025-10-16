<?php

namespace App\Filament\Resources\FundRequestResource\Pages;

use App\Filament\Resources\FundRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFundRequest extends EditRecord
{
    protected static string $resource = FundRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
