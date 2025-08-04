<?php


namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Staff extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name', 'email', 'password',
        'phone', 'address', 'dob', 'gender',
        'certification', 'lga', 'passport',
    ];

    protected $hidden = ['password'];
}
