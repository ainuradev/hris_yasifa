<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'unit_id',
        'name',
        'jp_per_week',
    ];

    protected $casts = [
        'jp_per_week' => 'integer',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_subjects');
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'subject_unit')
            ->withPivot('hours_per_week')
            ->withTimestamps();
    }

    public function teacherSubjectUnits(): HasMany
    {
        return $this->hasMany(TeacherSubjectUnit::class);
    }
}
