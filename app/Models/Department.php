<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'code' , 'faculty_id'];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

 public function courses()
{
    return $this->belongsToMany(Course::class, 'course_department', 'department_id', 'course_id');
}

}

