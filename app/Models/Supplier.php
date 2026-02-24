<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $temple_id
 * @property string $name
 * @property string|null $contact_number
 * @property string|null $address
 * @property string|null $type
 * @property-read Temple $temple
 */
class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'temple_id',
        'name',
        'contact_number',
        'address',
        'type',
    ];

    protected $dates = ['deleted_at'];

    /**
     * @return BelongsTo<Temple, Supplier>
     */
    public function temple(): BelongsTo
    {
        return $this->belongsTo(Temple::class);
    }
}
