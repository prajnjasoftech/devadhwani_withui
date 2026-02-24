<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['event_name', 'description', 'event_date', 'location'];
}
