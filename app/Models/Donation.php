<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $temple_id
 * @property string $donor_name
 * @property string|null $phone
 * @property float $amount
 * @property string|null $mode
 * @property \Carbon\Carbon $donation_date
 * @property string|null $purpose
 * @property string|null $remarks
 * @property-read Temple $temple
 */
class Donation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'temple_id',
        'donor_name',
        'phone',
        'amount',
        'mode',
        'donation_date',
        'purpose',
        'remarks',
    ];

    protected $casts = [
        'donation_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<Temple, Donation>
     */
    public function temple(): BelongsTo
    {
        return $this->belongsTo(Temple::class);
    }
}
