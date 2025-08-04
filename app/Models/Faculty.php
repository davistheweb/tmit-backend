<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $fillable = ['name', 'abbrev'];

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}
