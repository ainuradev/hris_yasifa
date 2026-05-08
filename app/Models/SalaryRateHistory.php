<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryRateHistory extends Model
{
    use HasFactory;

    protected $table = 'salary_rate_history';

    public $timestamps = false;

    protected $fillable = [
        'salary_rate_id',
        'old_rate',
        'new_rate',
        'reason',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'old_rate' => 'decimal:2',
        'new_rate' => 'decimal:2',
    ];

    public function salaryRate(): BelongsTo
    {
        return $this->belongsTo(SalaryRate::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'changed_by');
    }
}
