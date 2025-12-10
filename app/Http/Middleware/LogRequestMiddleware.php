<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('Appel à '.$request->path(), [
            'user_id' => $request->header('X-User-ID'),
            'token' => $request->bearerToken(),
            'authenticated' => auth()->check(),
            'origin' => $request->header('Origin'),
        ]);

        return $next($request);
    }
}
