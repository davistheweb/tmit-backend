<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['reg_number', 'name', 'email', 'password', 'status', 'department_id'];

    public function profile() {
        return $this->hasOne(StudentProfile::class);
    }

    public function results() {
        return $this->hasMany(Result::class);
    }

    public function department()
{
    return $this->belongsTo(Department::class);
}


    protected $hidden = [
        'password', 'remember_token',
    ];
}