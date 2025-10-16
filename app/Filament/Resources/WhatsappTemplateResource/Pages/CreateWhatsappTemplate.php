<?php

namespace App\Filament\Resources\WhatsappTemplateResource\Pages;

use App\Filament\Resources\WhatsappTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWhatsappTemplate extends CreateRecord
{
    protected static string $resource = WhatsappTemplateResource::class;

    // Add this method
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_default'] = true;
        return $data;
    }
}
