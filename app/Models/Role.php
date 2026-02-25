<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'temple_id',
        'role_name',
        'role',
    ];

    protected $casts = [
        'role' => 'array', // automatically cast JSON into PHP array
    ];

    // Relationships
    public function temple()
    {
        return $this->belongsTo(Temple::class);
    }

    public function members()
    {
        return $this->hasMany(Member::class, 'role_id');
    }
}
