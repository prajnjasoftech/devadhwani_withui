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
 * @property int|null $member_id
 * @property string $pooja_name
 * @property string|null $period
 * @property float $amount
 * @property string|null $details
 * @property int|null $devotees_required
 * @property \Carbon\Carbon|null $next_pooja_perform_date
 * @property-read Temple $temple
 * @property-read Member|null $member
 * @property-read Collection<int, TemplePoojaBooking> $bookings
 */
class TemplePooja extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'temple_poojas';

    protected $fillable = [
        'temple_id',
        'member_id',
        'pooja_name',
        'period',
        'amount',
        'details',
        'devotees_required',
        'next_pooja_perform_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'next_pooja_perform_date' => 'date',
    ];

    /**
     * @return BelongsTo<Temple, TemplePooja>
     */
    public function temple(): BelongsTo
    {
        return $this->belongsTo(Temple::class, 'temple_id');
    }

    /**
     * @return BelongsTo<Member, TemplePooja>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    /**
     * @return HasMany<TemplePoojaBooking>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(TemplePoojaBooking::class, 'pooja_id');
    }
}
