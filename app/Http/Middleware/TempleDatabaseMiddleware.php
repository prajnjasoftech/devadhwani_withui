<?php

namespace App\Http\Middleware;

use App\Helpers\TenantHelper;
use App\Models\Temple;
use Closure;
use Illuminate\Http\Request;

class TempleDatabaseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $templeId = $request->route('temple');
        $temple = Temple::find($templeId);

        if (! $temple || ! $temple->database_name) {
            return response()->json(['error' => 'Invalid temple'], 404);
        }

        TenantHelper::setConnection($temple->database_name);

        return $next($request);
    }
}
