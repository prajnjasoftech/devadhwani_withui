<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $temple_id
 * @property int $pooja_id
 * @property int|null $member_id
 * @property int|null $devotee_id
 * @property string $booking_number
 * @property \Carbon\Carbon $booking_date
 * @property string|null $booking_time_slot
 * @property string|null $period
 * @property float $pooja_amount
 * @property float|null $service_charge
 * @property string|null $payment_status
 * @property string|null $payment_mode
 * @property string|null $transaction_id
 * @property string|null $booking_status
 * @property string|null $remarks
 * @property \Carbon\Carbon|null $booking_end_date
 * @property float|null $pooja_amount_receipt
 * @property float|null $pooja_amount_remaining
 * @property float|null $pooja_amount_total_received
 * @property-read Temple $temple
 * @property-read TemplePooja $pooja
 * @property-read Member|null $member
 * @property-read Devotee|null $devotee
 * @property-read Collection<int, TemplePoojaBookingTracking> $trackings
 * @property-read Collection<int, Devotee> $devotees
 */
class TemplePoojaBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'temple_id',
        'pooja_id',
        'member_id',
        'devotee_id',
        'booking_number',
        'receipt_number',
        'booking_date',
        'booking_time_slot',
        'period',
        'pooja_amount',
        'service_charge',
        'payment_status',
        'payment_mode',
        'transaction_id',
        'booking_status',
        'remarks',
        'booking_end_date',
        'pooja_amount_receipt',
        'pooja_amount_remaining',
        'pooja_amount_total_received',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_end_date' => 'date',
        'pooja_amount' => 'decimal:2',
        'pooja_amount_receipt' => 'decimal:2',
        'pooja_amount_remaining' => 'decimal:2',
        'pooja_amount_total_received' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<Temple, TemplePoojaBooking>
     */
    public function temple(): BelongsTo
    {
        return $this->belongsTo(Temple::class);
    }

    /**
     * @return BelongsTo<TemplePooja, TemplePoojaBooking>
     */
    public function pooja(): BelongsTo
    {
        return $this->belongsTo(TemplePooja::class);
    }

    /**
     * @return BelongsTo<Member, TemplePoojaBooking>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * @return BelongsTo<Devotee, TemplePoojaBooking>
     */
    public function devotee(): BelongsTo
    {
        return $this->belongsTo(Devotee::class);
    }

    /**
     * @return HasMany<TemplePoojaBookingTracking>
     */
    public function trackings(): HasMany
    {
        return $this->hasMany(
            TemplePoojaBookingTracking::class,
            'booking_id',
            'id'
        );
    }

    /**
     * @return HasMany<Devotee>
     */
    public function devotees(): HasMany
    {
        return $this->hasMany(Devotee::class, 'id', 'devotee_id');
    }
}
