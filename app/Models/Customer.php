<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'user_id', // 
        'email', // nullable
        'mobile', // require
        'phone', //nullable
        'address', // required
        'full_name' // required
    ];
}
