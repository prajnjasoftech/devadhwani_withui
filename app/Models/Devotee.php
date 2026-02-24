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
 * @property string $devotee_name
 * @property string|null $devotee_phone
 * @property string|null $nakshatra
 * @property string|null $address
 * @property \Carbon\Carbon|null $device_created_at
 * @property-read Temple $temple
 * @property-read Collection<int, TemplePoojaBookingTracking> $trackings
 */
class Devotee extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'devotees';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'temple_id',
        'devotee_name',
        'devotee_phone',
        'nakshatra',
        'address',
        'device_created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'device_created_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Temple, Devotee>
     */
    public function temple(): BelongsTo
    {
        return $this->belongsTo(Temple::class, 'temple_id', 'id');
    }

    /**
     * @return HasMany<TemplePoojaBookingTracking>
     */
    public function trackings(): HasMany
    {
        return $this->hasMany(
            TemplePoojaBookingTracking::class,
            'devotee_id',
            'id'
        );
    }
}
