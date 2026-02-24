<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['donor_name', 'phone', 'amount', 'mode', 'donation_date'];
}
