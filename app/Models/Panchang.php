<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Panchang extends Model
{
    use HasFactory,SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'datetime',
        'latitude',
        'longitude',
        'timezone',
        'day',
        'tithi',
        'nakshatra',
        'yoga',
        'karana',
        'sunrise',
        'sunset',
        'raw_data',
        'day_name',
        'day_raw',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];
}
