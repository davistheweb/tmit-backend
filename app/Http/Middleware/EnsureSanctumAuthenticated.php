<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSanctumAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('sanctum')->check() && !$request->user()) {
            return response()->json(['message' => 'user not authenticated, token not provided'], 401);
        }

        return $next($request);
    }
}
