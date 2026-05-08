<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'schedule_id',
        'correction_date',
        'reason',
        'proof_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'correction_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewed_by');
    }
}
