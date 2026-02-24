<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {

            $request = request();

            Log::channel('api')->error('API FATAL ERROR', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_id' => optional($request->user())->id,
                'body' => $request->except([
                    'password',
                    'pin',
                    'cvv',
                    'card_number',
                ]),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Return JSON only for API requests
        if ($request->is('api/*')) {
            return response()->json([
                'message' => 'Unauthenticated. Please provide a valid token.',
            ], 401);
        }

        // All other requests redirect to login
        return redirect()->guest(route('login'));
    }
}
