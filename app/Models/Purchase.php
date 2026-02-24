<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $temple_id
 * @property int $item_id
 * @property int $supplier_id
 * @property float $quantity
 * @property float $unit_price
 * @property float $total_price
 * @property \Carbon\Carbon $received_date
 * @property string $mode
 * @property string|null $remarks
 * @property-read Temple $temple
 * @property-read Item $item
 * @property-read Supplier $supplier
 */
class Purchase extends Model
{
    use HasFactory,SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'temple_id',
        'item_id',
        'supplier_id',
        'quantity',
        'unit_price',
        'total_price',
        'received_date',
        'mode',
        'remarks',
    ];

    protected $dates = ['deleted_at', 'received_date'];

    /**
     * @return BelongsTo<Temple, Purchase>
     */
    public function temple(): BelongsTo
    {
        return $this->belongsTo(Temple::class);
    }

    /**
     * @return BelongsTo<Item, Purchase>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * @return BelongsTo<Supplier, Purchase>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
