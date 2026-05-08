<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryComponent extends Model
{
    protected $fillable = [
        'unit_id',
        'name',
        'type',
        'default_amount',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
