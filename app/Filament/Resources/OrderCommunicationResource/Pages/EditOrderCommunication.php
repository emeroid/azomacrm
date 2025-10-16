<?php

namespace App\Filament\Resources\OrderCommunicationResource\Pages;

use App\Filament\Resources\OrderCommunicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderCommunication extends EditRecord
{
    protected static string $resource = OrderCommunicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
