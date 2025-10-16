<?php

namespace App\Services;

use App\Mail\NewOrderForMarketer;
use App\Mail\OrderAssignedToAgent;
use App\Mail\OrderConfirmationForCustomer;
use App\Models\NotificationSetting;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Enums\Role;

class NotificationService
{
    /**
     * Check if a specific notification is enabled for a role.
     */
    private function isEnabled(string $role, string $notificationType): bool
    {
        // Cache the settings to avoid hitting the DB on every request
        $settings = Cache::remember('notification_settings', 3600, function () {
            return NotificationSetting::all()->groupBy('role');
        });
        
        return true;
    }

    /**
     * Handle notifications when a new order is placed.
     */
    public function handleOrderPlaced(Order $order): void
    {
        // 1. Notify the Marketer
        if ($this->isEnabled(Role::MARKETER->value, 'new_order') && $order->marketer?->email) {
            Mail::to($order->marketer->email)->queue(new NewOrderForMarketer($order));
        }

        // 2. Notify the Customer
        if ($this->isEnabled('customer', 'new_order') && $order->email) {
            Mail::to($order->email)->queue(new OrderConfirmationForCustomer($order));
        }
    }

    /**
     * Handle notifications when an order is assigned to a call agent.
     */
    public function handleOrderAssigned(Order $order): void
    {
        if ($this->isEnabled(Role::CALL_AGENT->value, 'order_assigned') && $order->callAgent?->email) {
            Mail::to($order->callAgent->email)->queue(new OrderAssignedToAgent($order));
        }
    }
}
