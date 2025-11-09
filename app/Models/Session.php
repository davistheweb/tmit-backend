<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    
    protected $table = 'school_sessions';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class, 'session_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'session_id');
    }
}
