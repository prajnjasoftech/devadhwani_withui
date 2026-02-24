<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantHelper
{
    public static function setConnection($dbName)
    {
        Config::set('database.connections.tenant.database', $dbName);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}
