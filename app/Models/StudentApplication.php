<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentApplication extends Model
{
      protected $fillable = [
        'reg_number',
        'name',
        'email',
        'password', // if stored here (hashed)
        'department',
        // add other fields you expect to create via mass-assignment
    ];
}
