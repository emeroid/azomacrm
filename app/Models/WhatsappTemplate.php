<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'key', 'message', 'description', 'category', 'user_id', 'is_default'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getDefaultTemplates()
    {
        return [
            [
                'name' => 'Order Confirmation',
                'key' => 'order_confirmed',
                'message' => 'Hello {Name}, your order for {product name} has been confirmed. Our delivery agent will contact you soon.',
                'description' => 'Confirm order placement to customer',
                'category' => 'order_confirmed',
            ],
            [
                'name' => 'Order Cancellation',
                'key' => 'order_cancelled',
                'message' => 'Hello {Name}, we regret to inform you that your order for {product name} has been cancelled. Please contact us for more details.',
                'description' => 'Notify customer about order cancellation',
                'category' => 'order_cancelled'
            ],
            [
                'name' => 'Delivery Update',
                'key' => 'delivery_update',
                'message' => 'Hello {Name}, I just finished speaking with you about {product name}. The delivery agent will be coming soon. If you don\'t get a call in 24 hours, please reach out to me.',
                'description' => 'Update customer about delivery status',
                'category' => 'delivery_update',
            ],
            [
                'name' => 'Number Not Connecting',
                'key' => 'number_not_connecting',
                'message' => 'Hello {Name}, I tried calling you regarding your order but couldn\'t connect. Please message me on WhatsApp when you\'re available.',
                'description' => 'When customer number isn\'t reachable',
                'category' => 'number_not_connecting',
            ],
            [
                'name' => 'Follow Up',
                'key' => 'follow_up',
                'message' => 'Hello {Name}, I will get back to you shortly regarding your order for {product name}. Thank you for your patience.',
                'description' => 'General follow up message',
                'category' => 'follow_up',
            ],
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->key)) {
                $model->key = strtolower(str_replace(' ', '_', $model->name));
            }
        });
    }
}
