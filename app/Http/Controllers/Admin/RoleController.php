<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // 🔹 Assign a role to a staff
    public function assign(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'role_id'  => 'required|exists:roles,id',
        ]);

        $staff = Staff::findOrFail($request->staff_id);

        // attach role via pivot table (many-to-many)
        $staff->roles()->syncWithoutDetaching([$request->role_id]);

        return response()->json([
            'message' => 'Role assigned successfully',
            'staff'   => $staff->load('roles')
        ]);
    }

    public function store(Request $request) {
    $request->validate([
        'name' => 'required|string|unique:roles',
        'description' => 'nullable|string',
    ]);

    $role = Role::create($request->only('name', 'description'));

    return response()->json(['message' => 'Role created successfully', 'role' => $role]);
}

    // 🔹 List all roles
    public function listRoles()
    {
        return response()->json(Role::all());
    }
}
