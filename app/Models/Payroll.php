<?php

namespace App\Models;

use App\Enums\PayrollStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'base_salary',
        'total_allowance',
        'total_deduction',
        'net_salary',
        'status',
        'paid_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => PayrollStatus::class,
        'paid_at' => 'date',
        'base_salary' => 'decimal:2',
        'total_allowance' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }

    public function payrollDetails(): HasMany
    {
        return $this->hasMany(PayrollDetail::class);
    }

    public function payrollHistories(): HasMany
    {
        return $this->hasMany(PayrollHistory::class);
    }

    public function snapshot(): HasOne
    {
        return $this->hasOne(PayrollSnapshot::class);
    }
}
