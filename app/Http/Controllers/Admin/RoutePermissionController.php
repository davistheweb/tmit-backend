<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoutePermissionController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'route_name' => 'required|string',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        DB::table('route_permissions')->updateOrInsert(
            ['route_name' => $request->route_name],
            ['permission_id' => $request->permission_id, 'updated_at' => now()]
        );

        return response()->json(['message' => 'Route permission assigned successfully']);
    }

    public function list() {
        $routes = DB::table('route_permissions')
            ->join('permissions', 'route_permissions.permission_id', '=', 'permissions.id')
            ->select('route_permissions.route_name', 'permissions.name as permission')
            ->get();

        return response()->json($routes);
    }
}
