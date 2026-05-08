<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TeacherDetail extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'salary_rate_id',
        'jabatan',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function salaryRate(): BelongsTo
    {
        return $this->belongsTo(SalaryRate::class);
    }

    public function teacherSubjectUnits(): HasMany
    {
        return $this->hasMany(TeacherSubjectUnit::class);
    }

    public function homeroomClass(): HasOne
    {
        return $this->hasOne(SchoolClass::class, 'homeroom_teacher_id');
    }

    /**
     * Subject competencies (Mata Pelajaran yang dikuasai/diampu)
     */
    public function subjects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'employee_subjects', 'employee_id', 'subject_id', 'employee_id');
    }
}
