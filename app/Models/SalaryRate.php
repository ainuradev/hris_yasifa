<?php

namespace App\Models;

use App\Enums\EmployeeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'jabatan',
        'type',
        'rate',
    ];

    protected $casts = [
        'type' => EmployeeType::class,
        'rate' => 'decimal:2',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function teacherDetails(): HasMany
    {
        return $this->hasMany(TeacherDetail::class);
    }

    public function nonTeacherDetails(): HasMany
    {
        return $this->hasMany(NonTeacherDetail::class);
    }

    public function salaryRateHistories(): HasMany
    {
        return $this->hasMany(SalaryRateHistory::class);
    }
}
