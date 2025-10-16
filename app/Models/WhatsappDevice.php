<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappDevice extends Model
{
    use HasFactory;

    protected $fillable = ['session_id', 'user_id', 'status', 'qr_code_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
