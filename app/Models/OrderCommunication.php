<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class OrderCommunication extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'agent_id',
        'sender_id', // can be a delivery agent update on this order, or the marketer id
        'type', // call, email, note
        'content',
        'labels', // JSON array of labels
        'outcome', // primary outcome of this communication
    ];

    protected $casts = [
        'labels' => 'array',
    ];

    // Predefined communication outcomes
    const OUTCOMES = [
        'order_placed',
        'order_cancelled',
        'not_ready',
        'interested',
        'not_interested',
        'follow_up',
        'payment_issue',
        'delivery_issue',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Helper to add labels
    public function addLabel($label)
    {
        $labels = $this->labels ?? [];
        if (!in_array($label, $labels)) {
            $labels[] = $label;
            $this->labels = $labels;
        }
        return $this;
    }

    // Helper to check if has label
    public function hasLabel($label)
    {
        return in_array($label, $this->labels ?? []);
    }

    protected static function booted()
    {
        static::creating(function ($order) {
            $order->sender_id = request()->user()->id ?? $order->marketer_id;
        });
    }
}
