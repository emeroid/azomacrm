<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\DispatchWhatsappBroadcast;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderConfirmationWhatsapp
{
    /**
     * Create the event listener.
    */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        $message = "Hello {$event->order->customer->name}, thank you for your order! Your order ID is #{$event->order->id}. We will notify you once it's shipped.";
    
        // Dispatch a job to send the message
        DispatchWhatsappBroadcast::dispatch(
            request()->getSession(), // The customer's assigned session/device
            $event->order->customer->phone,
            $message
        );
    }
}
