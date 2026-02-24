<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $temple_id
 * @property int $item_id
 * @property int|null $used_by
 * @property float $quantity
 * @property string|null $used_for
 * @property \Carbon\Carbon|null $date
 * @property string|null $remarks
 * @property-read Temple $temple
 * @property-read Item $item
 * @property-read Member|null $user
 */
class Usage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'temple_id',
        'item_id',
        'used_by',
        'quantity',
        'used_for',
        'date',
        'remarks',
    ];

    protected $dates = ['deleted_at', 'date'];

    /**
     * @return BelongsTo<Temple, Usage>
     */
    public function temple(): BelongsTo
    {
        return $this->belongsTo(Temple::class);
    }

    /**
     * @return BelongsTo<Item, Usage>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * @return BelongsTo<Member, Usage>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'used_by');
    }
}
