<?php

namespace App\Models;

use App\Enums\EmployeeRequestStatus;
use App\Enums\EmployeeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'requested_by',
        'approved_by',
        'name',
        'nik',
        'email',
        'date_of_birth',
        'type',
        'status',
        'employment_status',
        'contract_end_date',
        'jabatan',
        'salary_rate_id',
        'approval_document_path',
        'approval_notes',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'contract_end_date' => 'date',
        'approved_at' => 'datetime',
        'type' => EmployeeType::class,
        'status' => EmployeeRequestStatus::class,
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function salaryRate(): BelongsTo
    {
        return $this->belongsTo(SalaryRate::class);
    }
}
