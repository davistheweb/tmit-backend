<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // 🔹 Assign a permission to a role
    public function assign(Request $request)
    {
        $request->validate([
            'role_id'       => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role = Role::findOrFail($request->role_id);

        // attach permission to role
        $role->permissions()->syncWithoutDetaching([$request->permission_id]);

        return response()->json(['message' => 'Permission assigned successfully']);
    }

    public function store(Request $request) {
    $request->validate([
        'name' => 'required|string|unique:permissions',
        'description' => 'nullable|string',
    ]);

    $permission = Permission::create($request->only('name', 'description'));

    return response()->json(['message' => 'Permission created successfully', 'permission' => $permission]);
}

    // 🔹 List all permissions
    public function listPermissions()
    {
        return response()->json(Permission::all());
    }
}
