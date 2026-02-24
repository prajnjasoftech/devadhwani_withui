<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    protected $fillable = [
        'temple_id',
        'source_id',
        'member_id',
        'booking_id',
        'payment_date',
        'payment',
        'source',
        'type',
        'payment_mode',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'payment' => 'decimal:2',
    ];

    public function temple()
    {
        return $this->belongsTo(Temple::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function booking()
    {
        return $this->belongsTo(TemplePoojaBooking::class, 'booking_id');
    }
}
