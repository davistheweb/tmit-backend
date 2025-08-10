<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'title', 'unit', 'level', 'semester', 'department_id', 'session'
    ];

   public function departments()
{
    return $this->belongsToMany(Department::class, 'course_department', 'course_id', 'department_id');
}


    public function results()
    {
        return $this->hasMany(Result::class);
    }
}
