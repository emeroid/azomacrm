<?php

namespace App\Listeners;

use App\Events\OrderAssigned;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderAssignedNotification
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
    public function handle(OrderAssigned $event): void
    {
        $order = $event->order;
        $notification = new NotificationService();
        $notification->handleOrderAssigned($order);
    }
}
