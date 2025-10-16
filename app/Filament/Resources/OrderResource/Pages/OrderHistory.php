<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\Page;
use App\Models\Order;
use App\Models\OrderCommunication;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class OrderHistory extends Page
{
    use InteractsWithForms;

    protected static string $resource = OrderResource::class;
    protected static string $view = 'filament.resources.order-resource.pages.order-history';

    public Order $record;
    public array $labels = [];
    public ?string $outcome = null;
    public ?string $content = null;
    public ?string $type = 'note';
    // ✅ This is what was missing
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('type')
                    ->options([
                        'note' => 'Note',
                    ])
                    ->required(),
                
                Textarea::make('content')
                    ->label('Message')
                    ->required()
                    ->rows(3),
                
                // Select::make('outcome')
                //     ->options(OrderCommunication::OUTCOMES)
                //     ->label('Outcome'),
                
                // Select::make('labels')
                //     ->multiple()
                //     ->options([
                //         'urgent' => 'Urgent',
                //         'follow_up' => 'Follow Up',
                //         'customer_request' => 'Customer Request',
                //     ]),
            ])
            ->statePath('data');
    }

    public function sendMessage(): void
    {
        $data = $this->form->getState();
        
        $communication = new OrderCommunication([
            'order_id' => $this->record->id,
            'agent_id' => $this->record->call_agent_id,
            'sender_id' => Auth::id(),
            'type' => $data['type'],
            'content' => $data['content'],
            // 'labels' => $data['labels'] ?? [],
        ]);
        
        $communication->save();
        
        $this->form->fill();
        
        Notification::make()
            ->title('Message sent')
            ->success()
            ->send();
    }

    public function getCommunicationsProperty()
    {
        return OrderCommunication::where('order_id', $this->record->id)
            ->with(['agent', 'sender'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }
}
