<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outcome extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'key', 'user_id', 'is_default'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public static function getDefaultOutcomes()
    {
        return [
            ['name' => 'Order Placed', 'key' => 'order_placed'],
            ['name' => 'Order Cancelled', 'key' => 'order_cancelled'],
            ['name' => 'Not Ready', 'key' => 'not_ready'],
            ['name' => 'Interested', 'key' => 'interested'],
            ['name' => 'Not Interested', 'key' => 'not_interested'],
            ['name' => 'Follow Up', 'key' => 'follow_up'],
            ['name' => 'Payment Issue', 'key' => 'payment_issue'],
            ['name' => 'Delivery Issue', 'key' => 'delivery_issue'],
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
