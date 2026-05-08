<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'schedule_id',
        'teacher_subject_unit_id',
        'jadwal_id',
        'date',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'date' => 'date',
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

    public function teacherSubjectUnit(): BelongsTo
    {
        return $this->belongsTo(TeacherSubjectUnit::class);
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(TeacherSubjectUnit::class, 'jadwal_id', 'jadwal_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewed_by');
    }
}
