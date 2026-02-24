<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpLog extends Model
{
    protected $fillable = [
        'phone',
        'otp',
        'is_verified',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
