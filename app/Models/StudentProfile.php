<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = [
    'student_id',
    'bio',
    'year',
    'department',
    'image_path',
    'certificates_path',
    // Add other fields you're filling too
];
}
