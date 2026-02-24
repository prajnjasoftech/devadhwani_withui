<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $temple_id
 * @property int $category_id
 * @property string $item_name
 * @property string $unit
 * @property float $min_quantity
 * @property string|null $description
 * @property string $status
 * @property-read Category $category
 * @property-read Temple $temple
 */
class Item extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'temple_id',
        'category_id',
        'item_name',
        'unit',
        'min_quantity',
        'description',
        'status',
    ];

    protected $casts = [
        'min_quantity' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<Category, Item>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Temple, Item>
     */
    public function temple(): BelongsTo
    {
        return $this->belongsTo(Temple::class);
    }
}
