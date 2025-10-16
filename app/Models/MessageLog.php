<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
    */
    protected $fillable = [
        'message_id',
        'session_id',
        'recipient_number',
        'message',
        'status',
        'sent_at',
        'delivered_at',
        'read_at',

        // --- NEW FOREIGN KEYS for Analytics ---
        'user_id',                   // Needed for general analytics access
        'scheduled_message_id',      // Links back to ScheduledMessage (null if from Campaign/AutoResponder)
        'campaign_id',               // Links back to Campaign (null if from Schedule/AutoResponder)
        'auto_responder_log_id',     // Links back to AutoResponderLog (null if from Schedule/Campaign)
    ];

    public function scheduledMessage()
    {
        return $this->belongsTo(ScheduledMessage::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
    
    // Placeholder function used in the job (you may need to implement this based on your structure)
    public static function getUserFromSessionId(string $sessionId)
    {
        // Example: Find the user ID based on the session ID in the WhatsappDevice table
        return WhatsappDevice::where('session_id', $sessionId)->value('user_id');
    }
}
