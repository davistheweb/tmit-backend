<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method bool hasPermission(string $permissionName)
 * @method bool hasRole(string $roleName)
 * @method \Illuminate\Support\Collection allPermissions()
 */
class Staff extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name', 'email', 'password',
        'phone', 'address', 'dob', 'gender',
        'certification', 'lga', 'passport',
    ];

    protected $hidden = ['password'];

    // 🔹 Many-to-Many: Staff can have multiple roles
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_staff');
    }

    // 🔹 Many-to-Many: Staff can have multiple permissions directly
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_staff');
    }

    // 🔹 Check if staff has a specific role
    public function hasRole($roleName)
    {
        return $this->roles->contains('name', $roleName);
    }

    // 🔹 Check if staff has a specific permission (direct or via roles)
    public function hasPermission($permissionName)
    {
        // check direct permissions
        if ($this->permissions->contains('name', $permissionName)) {
            return true;
        }

        // check permissions via roles
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('name', $permissionName)) {
                return true;
            }
        }

        return false;
    }

    // 🔹 Get all permissions (direct + via roles)
    public function allPermissions()
    {
        $rolePermissions = $this->roles->load('permissions')->pluck('permissions')->flatten();
        return $rolePermissions->merge($this->permissions)->unique('id');
    }
}
