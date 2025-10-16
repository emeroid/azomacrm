<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\Page;

class OrderInvoice extends Page
{
    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.order-resource.pages.order-invoice';

    public Order $record;

    public function mount($record): void
    {
        $this->record = Order::findOrFail($record->id);
    }
}
