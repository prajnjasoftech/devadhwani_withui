<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApiRequestLogger
{
    public function handle(Request $request, Closure $next)
    {
        $requestId = (string) Str::uuid();
        $startTime = microtime(true);

        $response = $next($request);

        // ---------- REQUEST ----------
        $requestData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'headers' => collect($request->headers->all())->except([
                'authorization',
            ]),
            'body' => $request->except([
                'password', 'pin', 'cvv', 'card_number',
            ]),
            'user_id' => optional($request->user())->id,
        ];

        // ---------- RESPONSE ----------
        $responseBody = null;

        if ($response instanceof JsonResponse) {
            $responseBody = $response->getData(true);
        }

        // Limit response size (important)
        if ($responseBody && strlen(json_encode($responseBody)) > 5000) {
            $responseBody = 'RESPONSE TOO LARGE';
        }

        // ---------- COMBINED LOG ----------
        Log::channel('api')->info('API REQUEST-RESPONSE', [
            'request_id' => $requestId,
            'status' => $response->getStatusCode(),
            'duration' => round((microtime(true) - $startTime) * 1000).' ms',
            'request' => $requestData,
            'response' => $responseBody,
        ]);

        return $response;
    }
}
