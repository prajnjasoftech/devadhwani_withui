<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property int|null $temple_id
 * @property int|null $role_id
 * @property string $name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $role
 * @property-read Temple|null $temple
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TemplePooja> $poojas
 */
class Member extends Model
{
    use HasApiTokens, HasFactory, SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = ['name', 'phone', 'email', 'role', 'role_id', 'temple_id'];

    /**
     * @return BelongsTo<Temple, Member>
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
        return $this->hasMany(TemplePooja::class, 'member_id');
    }
}
