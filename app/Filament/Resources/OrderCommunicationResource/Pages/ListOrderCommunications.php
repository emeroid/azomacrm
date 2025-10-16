<?php

namespace App\Filament\Resources\OrderCommunicationResource\Pages;

use App\Filament\Resources\OrderCommunicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderCommunications extends ListRecords
{
    protected static string $resource = OrderCommunicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
