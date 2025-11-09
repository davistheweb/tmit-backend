<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'student_id', 'reference', 'title', 'description', 'amount', 'status', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
