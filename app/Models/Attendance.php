<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'schedule_id',
        'teacher_subject_unit_id',
        'jadwal_id',
        'checked_in_at',
        'checked_out_at',
        'latitude',
        'longitude',
        'check_out_latitude',
        'check_out_longitude',
        'check_in_distance_meters',
        'check_out_distance_meters',
        'face_check_in_path',
        'face_check_out_path',
        'face_verified',
        'attendance_challenge_hash',
        'attendance_ip',
        'attendance_user_agent',
        'status',
        'notes',
        'is_approved',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'status' => AttendanceStatus::class,
        'is_approved' => 'boolean',
        'face_verified' => 'boolean',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $attendance): void {
            if ($attendance->jadwal_id && ! $attendance->teacher_subject_unit_id) {
                $attendance->teacher_subject_unit_id = TeacherSubjectUnit::query()
                    ->where('jadwal_id', $attendance->jadwal_id)
                    ->value('id');
            }

            if ($attendance->teacher_subject_unit_id && ! $attendance->jadwal_id) {
                $attendance->jadwal_id = TeacherSubjectUnit::query()
                    ->whereKey($attendance->teacher_subject_unit_id)
                    ->value('jadwal_id');
            }
        });
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
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

    public function scopeTeacherSessions(Builder $query): Builder
    {
        if (Schema::hasColumn($this->getTable(), 'jadwal_id')) {
            return $query->whereNotNull('jadwal_id');
        }

        if (! Schema::hasColumn($this->getTable(), 'teacher_subject_unit_id')) {
            return $query;
        }

        return $query->whereNotNull('teacher_subject_unit_id');
    }

    public function scopeDailyRecords(Builder $query): Builder
    {
        if (Schema::hasColumn($this->getTable(), 'jadwal_id')) {
            return $query->whereNull('jadwal_id');
        }

        if (! Schema::hasColumn($this->getTable(), 'teacher_subject_unit_id')) {
            return $query;
        }

        return $query->whereNull('teacher_subject_unit_id');
    }
}
