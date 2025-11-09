<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory;

  protected $fillable = [
        'department_id',
        'session_id',
        'level',
        'fee_type',
        'amount',
        'installment_first',
        'installment_second',
        'allow_installment',
        'is_mandatory',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'installment_first' => 'decimal:2',
        'installment_second' => 'decimal:2',
        'allow_installment' => 'boolean',
         'is_mandatory' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function scopeSchoolFees($query)
    {
        return $query->where('fee_type', 'school');
    }

    public function scopeAcceptanceFees($query)
    {
        return $query->where('fee_type', 'acceptance');
    }

    public function scopeHostelFees($query)
    {
        return $query->where('fee_type', 'hostel');
    }
}
