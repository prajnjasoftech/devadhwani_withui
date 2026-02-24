<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $fillable = ['name', 'phone', 'email', 'role', 'role_id'];
}
