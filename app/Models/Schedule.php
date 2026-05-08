<?php

namespace App\Models;

use App\Enums\DayType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'work_date',
        'check_in_start',
        'check_in_end',
        'day_type',
    ];

    protected $casts = [
        'work_date' => 'date',
        'day_type' => DayType::class,
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function subjectPermissions(): HasMany
    {
        return $this->hasMany(SubjectPermission::class);
    }

    public function attendanceCorrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }
}
