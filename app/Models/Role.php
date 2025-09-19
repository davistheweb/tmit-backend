<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'description'];

    public function staff() {
        return $this->hasMany(Staff::class);
    }

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }
}

