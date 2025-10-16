<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoResponder extends Model
{
    public $fillable = [
        'user_id', 
        'keyword', 
        'response_message', 
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
