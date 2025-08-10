<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $fillable = [
        'student_id', 'course_code', 'course_title', 'score', 'grade', 'term', 'session', 'semester',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function results()
{
    return $this->hasMany(Result::class);
}
 public function course()
{
    return $this->belongsTo(Course::class);
}

}
