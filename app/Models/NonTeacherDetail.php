<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NonTeacherDetail extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'salary_rate_id',
        'jabatan',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function salaryRate(): BelongsTo
    {
        return $this->belongsTo(SalaryRate::class);
    }
}
