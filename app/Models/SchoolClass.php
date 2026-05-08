<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'unit_id',
        'name',
        'level',
        'major',
        'academic_year',
        'allow_team_teaching',
        'homeroom_teacher_id',
    ];

    protected $casts = [
        'allow_team_teaching' => 'boolean',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function teacherSubjectUnits(): HasMany
    {
        return $this->hasMany(TeacherSubjectUnit::class, 'class_id');
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(TeacherDetail::class, 'homeroom_teacher_id');
    }
}
