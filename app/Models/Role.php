<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'temple_id',
        'role_name',
        'role',
    ];

    protected $casts = [
        'role' => 'array', // automatically cast JSON into PHP array
    ];

    // Relationship
    public function temple()
    {
        return $this->belongsTo(Temple::class);
    }
}
