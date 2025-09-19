<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StaffPermissionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('staff')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $routeName = $request->route()->getName();

        // Check if this route has a required permission
        $required = DB::table('route_permissions')
            ->join('permissions', 'route_permissions.permission_id', '=', 'permissions.id')
            ->where('route_permissions.route_name', $routeName)
            ->value('permissions.name');

        if ($required && !$user->hasPermission($required)) {
            return response()->json(['error' => 'Unauthorized: Missing permission'], 403);
        }

        return $next($request);
    }
}
