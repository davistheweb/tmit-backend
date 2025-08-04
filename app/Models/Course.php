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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }
}
