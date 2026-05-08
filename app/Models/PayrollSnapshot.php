<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'unit_id',
        'rate_gaji',
        'verified_jp_total',
        'daily_allowance_rate',
        'daily_allowance_total',
        'payload',
    ];

    protected $casts = [
        'rate_gaji' => 'decimal:2',
        'verified_jp_total' => 'decimal:2',
        'daily_allowance_rate' => 'decimal:2',
        'daily_allowance_total' => 'decimal:2',
        'payload' => 'array',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
