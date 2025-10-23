<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id', 
        'user_id', 
        'status', 
        'qr_code_url',
        'phone_number',
        'name',
        'min_delay',
        'max_delay',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
