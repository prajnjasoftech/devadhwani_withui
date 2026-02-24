<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $temple_id
 * @property string $event_name
 * @property string|null $description
 * @property \Carbon\Carbon $event_date
 * @property string|null $event_time
 * @property string|null $location
 * @property string $status
 * @property-read Temple $temple
 */
class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'temple_id',
        'event_name',
        'description',
        'event_date',
        'event_time',
        'location',
        'status',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    /**
     * @return BelongsTo<Temple, Event>
     */
    public function temple(): BelongsTo
    {
        return $this->belongsTo(Temple::class);
    }
}
