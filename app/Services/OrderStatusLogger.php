<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderCommunication;
use Illuminate\Support\Facades\Auth;

class OrderStatusLogger
{
    /**
     * Log an order status change
     *
     * @param Order $order
     * @param string $newStatus
     * @param string|null $notes
     * @return OrderCommunication
     */
    public static function logStatusChange(Order $order, string $newStatus, ?string $notes = null)
    {
        if(Auth::check()) {
            return OrderCommunication::create([
                'order_id' => $order->id,
                'content' => $notes ?? "Order status updated to: {$newStatus}",
                'type' => 'note',
                'outcome' => 'status_updated',
                'sender_id' => Auth::id(),
                'agent_id' => $order->call_agent_id,
                'status_before' => $order->status,
                'status_after' => $newStatus,
                'metadata' => [
                    'changed_by' => Auth::user()->name,
                    'changed_at' => now()->toDateTimeString(),
                ]
            ]);
        }
    }

    /**
     * Get all status changes for an order
     *
     * @param Order $order
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getStatusHistory(Order $order)
    {
        return OrderCommunication::where('order_id', $order->id)
            ->where('type', 'status_update')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}