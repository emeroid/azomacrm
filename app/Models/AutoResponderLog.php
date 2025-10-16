<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoResponderLog extends Model
{
    public $fillable = [
        'auto_responder_id', 
        'recipient', 
        'sent_at',
        'status'
    ];

    public function autoResponder() {
        return $this->belongsTo(AutoResponder::class);
    }
}
