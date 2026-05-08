<?php

namespace App\Models;

use App\Enums\EmployeeRole;
use App\Enums\EmployeeStatus;
use App\Enums\EmployeeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class Employee extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'employees';

    protected $fillable = [
        'password',
        'unit_id',
        'name',
        'nik',
        'nuptk',
        'email',
        'phone',
        'address',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'emergency_contact_name',
        'emergency_contact_phone',
        'photo_path',
        'type',
        'role',
        'status',
        'contract_end_date',
        'must_change_password',
        'npk',
        'agama',
        'nama_ibu_kandung',
        'status_perkawinan',
        'pendidikan_terakhir',
        'tahun_lulus',
        'status_kepegawaian',
        'tmt_pegawai',
        'no_sk_pengangkatan',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'type' => EmployeeType::class,
        'role' => EmployeeRole::class,
        'status' => EmployeeStatus::class,
        'contract_end_date' => 'date',
        'date_of_birth' => 'date',
        'tmt_pegawai' => 'date',
        'must_change_password' => 'boolean',
        'password' => 'hashed',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function teacherDetail(): HasOne
    {
        return $this->hasOne(TeacherDetail::class);
    }

    public function nonTeacherDetail(): HasOne
    {
        return $this->hasOne(NonTeacherDetail::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class, 'employee_id');
    }

    public function payrollSnapshots(): HasMany
    {
        return $this->hasMany(PayrollSnapshot::class, 'employee_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function subjectPermissions(): HasMany
    {
        return $this->hasMany(SubjectPermission::class);
    }

    public function attendanceCorrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    public function auditTrails(): HasMany
    {
        return $this->hasMany(AuditTrail::class, 'actor_employee_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    public function payrollHistories(): HasMany
    {
        return $this->hasMany(PayrollHistory::class, 'changed_by');
    }

    public function salaryRateHistories(): HasMany
    {
        return $this->hasMany(SalaryRateHistory::class, 'changed_by');
    }

    public function salaryComponents(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }

    public function employeeRequests(): HasMany
    {
        return $this->hasMany(EmployeeRequest::class, 'requested_by');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'employee_subjects')->withTimestamps();
    }

    public function approvedEmployeeRequests(): HasMany
    {
        return $this->hasMany(EmployeeRequest::class, 'approved_by');
    }

    public function isAdminPusat(): bool
    {
        return $this->role === EmployeeRole::AdminPusat;
    }

    public function isAdminUnit(): bool
    {
        return $this->role === EmployeeRole::AdminUnit;
    }

    public function isAdmin(): bool
    {
        return $this->isAdminPusat() || $this->isAdminUnit();
    }

    public function scopeVisibleToAdmin(Builder $query, self $admin): Builder
    {
        if ($admin->isAdminPusat()) {
            return $query;
        }

        return $query->where('unit_id', $admin->unit_id);
    }
}
