<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TeacherSubjectUnit extends Model
{
    use HasFactory;

    protected $table = 'teacher_subject_unit';

    public $timestamps = false;

    protected $fillable = [
        'jadwal_id',
        'teacher_detail_id',
        'unit_id',
        'class_id',
        'subject_id',
        'day_name',
        'start_time',
        'end_time',
        'hours_per_week',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $teacherSubjectUnit): void {
            $teacherSubjectUnit->jadwal_id ??= (string) Str::uuid();
        });
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'hours_per_week' => 'integer',
    ];

    public function teacherDetail(): BelongsTo
    {
        return $this->belongsTo(TeacherDetail::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
