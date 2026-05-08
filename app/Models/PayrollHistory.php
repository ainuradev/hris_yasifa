<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollHistory extends Model
{
    use HasFactory;

    protected $table = 'payroll_history';

    public $timestamps = false;

    protected $fillable = [
        'payroll_id',
        'field_changed',
        'old_value',
        'new_value',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'changed_by');
    }
}
