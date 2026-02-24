<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $booking_id
 * @property \Carbon\Carbon|null $pooja_date
 * @property string|null $payment_status
 * @property string|null $booking_status
 * @property int|null $devotee_id
 * @property string|null $remarks
 * @property float|null $paid_amount
 * @property float|null $due_amount
 * @property-read TemplePoojaBooking $booking
 * @property-read Devotee|null $devotee
 */
class TemplePoojaBookingTracking extends Model
{
    protected $table = 'temple_pooja_bookings_tracking';

    protected $fillable = [
        'booking_id',
        'pooja_date',
        'payment_status',
        'booking_status',
        'devotee_id',
        'remarks',
        'paid_amount',
        'due_amount',
    ];

    protected $casts = [
        'pooja_date' => 'datetime',
    ];

    /**
     * @return BelongsTo<TemplePoojaBooking, TemplePoojaBookingTracking>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(TemplePoojaBooking::class, 'booking_id');
    }

    /**
     * @return BelongsTo<Devotee, TemplePoojaBookingTracking>
     */
    public function devotee(): BelongsTo
    {
        return $this->belongsTo(
            Devotee::class,
            'devotee_id',
            'id'
        );
    }
}
