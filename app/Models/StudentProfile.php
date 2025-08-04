<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = [
    'student_id',
    'reg_number',
    'email',
    'surname',
    'middle_name',
    'last_name',
    'gender',
    'dob',
    'country',
    'state',
    'lga',
    'home_town',
    'phone',
    'nin',
    'contact_address',
    'blood_group',
    'genotype',
    'religion',
    'bio',
    'image_path',
    'certifications_path',
    'department',
    'year',
];

// App\Models\StudentProfile.php
public function student()
{
    return $this->belongsTo(Student::class);
}

}
