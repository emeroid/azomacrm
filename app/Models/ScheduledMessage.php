<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledMessage extends Model
{
    protected $fillable = [
        'user_id',
        'whatsapp_device_id',
        'action_type',
        'action_id',
        'target_criteria',
        'whatsapp_field_name',
        'message',
        'sent_count',
        'failed_count',
        'send_at',
        'sent_at',
    ];


    protected $casts = [
        'target_criteria' => 'array'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function device() {
        return $this->belongsTo(WhatsappDevice::class);
    }
}
