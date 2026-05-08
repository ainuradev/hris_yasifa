<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'jenjang',
        'kepala_unit',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function teacherSubjectUnits(): HasMany
    {
        return $this->hasMany(TeacherSubjectUnit::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function curriculumSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_unit')
            ->withPivot('hours_per_week')
            ->withTimestamps();
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class);
    }
}
