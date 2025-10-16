<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'user_id',
        'whatsapp_device_id',
        'message',
        'delay',
        'total_recipients', // Total recipients in the broadcast
        'sent_count',       // For analytics
        'failed_count',     // For analytics
        'queued_at',
    ];

    public function messageLogs()
    {
        // One Campaign has many MessageLogs
        return $this->hasMany(MessageLog::class, 'campaign_id');
    }
}
