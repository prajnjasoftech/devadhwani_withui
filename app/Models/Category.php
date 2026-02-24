<?php

namespace App\Models;

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
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read Temple $temple
 */
class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'temple_id',
        'name',
        'description',
    ];

    protected $dates = ['deleted_at'];

    /**
     * @return BelongsTo<Temple, Category>
     */
    public function temple(): BelongsTo
    {
        return $this->belongsTo(Temple::class);
    }

    /**
     * @return HasMany<Item>
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
