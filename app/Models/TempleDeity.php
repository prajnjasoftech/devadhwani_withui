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
 * @property string $name
 * @property string|null $description
 * @property string|null $image
 * @property bool $is_active
 * @property-read Temple $temple
 * @property-read Collection<int, TemplePooja> $poojas
 * @property-read Collection<int, TemplePoojaBooking> $bookings
 */
class TempleDeity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'temple_deities';

    protected $fillable = [
        'temple_id',
        'name',
        'description',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo<Temple, TempleDeity>
     */
    public function temple(): BelongsTo
    {
        return $this->belongsTo(Temple::class, 'temple_id');
    }

    /**
     * @return HasMany<TemplePooja>
     */
    public function poojas(): HasMany
    {
        return $this->hasMany(TemplePooja::class, 'deity_id');
    }

    /**
     * @return HasMany<TemplePoojaBooking>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(TemplePoojaBooking::class, 'deity_id');
    }
}
