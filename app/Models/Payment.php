<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

  protected $fillable = [
        'student_id',
        'fee_structure_id',
        'session_id',
        'reference',
        'amount',
        'payment_type',
        'status',
        'gateway_response',
        'channel',
        'payment_method',
        'currency',
        'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }
}
