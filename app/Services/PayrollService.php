<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\EmployeeType;
use App\Enums\PayrollCategory;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\SubjectPermission;
use Carbon\Carbon;

class PayrollService
{
    public function buildPayroll(Employee $employee, int $month, int $year, int $unitId): array
    {
        if ($employee->type !== EmployeeType::Guru) {
            return $this->buildNonTeacherPayroll($employee, $month, $year);
        }

        $teacherDetail = $employee->teacherDetail;
        $ratePerJp = (float) ($teacherDetail?->salaryRate?->rate ?? 0);
        $verified = $this->calculateVerifiedTeacherJp($employee, $month, $year, $unitId);
        
        // Guru tidak menggunakan gaji dasar (base salary), semua berbasis honor/tunjangan
        $baseSalary = 0.0;
        $honorJP = $ratePerJp * $verified['jp_total'];

        $dailyAllowanceRate = (float) $employee->salaryComponents
            ->filter(fn ($component) => $component->salaryComponent?->type === 'tunjangan')
            ->filter(fn ($component) => mb_strtolower((string) $component->salaryComponent?->name) === 'tunjangan harian')
            ->sum('amount');

        $dailyAllowanceTotal = $dailyAllowanceRate * $verified['present_days'];
        $totalAllowance = $dailyAllowanceTotal;
        $totalDeduction = 0.0;

        $components = [
            [
                'description' => sprintf(
                    'Honor JP Tervalidasi (%s JP x Rp %s/JP)',
                    rtrim(rtrim(number_format($verified['jp_total'], 2, '.', ''), '0'), '.'),
                    number_format($ratePerJp, 0, ',', '.')
                ),
                'amount' => $honorJP,
                'category' => PayrollCategory::Tunjangan->value,
            ],
        ];

        if ($dailyAllowanceTotal > 0) {
            $components[] = [
                'description' => sprintf(
                    'Tunjangan Harian (%d hari x Rp %s)',
                    $verified['present_days'],
                    number_format($dailyAllowanceRate, 0, ',', '.')
                ),
                'amount' => $dailyAllowanceTotal,
                'category' => PayrollCategory::Tunjangan->value,
            ];
        }

        foreach ($employee->salaryComponents as $empComp) {
            $componentName = mb_strtolower((string) $empComp->salaryComponent?->name);
            if ($componentName === 'tunjangan harian') {
                continue;
            }

            $category = $empComp->salaryComponent->type === 'tunjangan'
                ? PayrollCategory::Tunjangan->value
                : PayrollCategory::Potongan->value;

            $components[] = [
                'description' => $empComp->salaryComponent->name,
                'amount' => (float) $empComp->amount,
                'category' => $category,
            ];

            if ($category === PayrollCategory::Tunjangan->value) {
                $totalAllowance += (float) $empComp->amount;
            } else {
                $totalDeduction += (float) $empComp->amount;
            }
        }

        $workingDays = $this->calculateEffectiveWorkingDays($month, $year, $unitId);
        $alpaCount = $this->calculateAlpaCount($employee, $month, $year, $unitId);
        $absenceDeduction = ($alpaCount > 0 && $workingDays > 0) ? ($baseSalary / $workingDays) * $alpaCount : 0.0;

        if ($absenceDeduction > 0) {
            $totalDeduction += $absenceDeduction;
            $components[] = [
                'description' => sprintf('Potongan Alpa (%d hari kerja)', $alpaCount),
                'amount' => $absenceDeduction,
                'category' => PayrollCategory::Potongan->value,
            ];
        }

        return [
            'base_salary' => 0.0,
            'total_allowance' => $totalAllowance + $honorJP,
            'total_deduction' => $totalDeduction,
            'net_salary' => max($honorJP + $totalAllowance - $totalDeduction, 0),
            'components' => $components,
            'snapshot' => [
                'unit_id' => $unitId,
                'rate_gaji' => $ratePerJp,
                'verified_jp_total' => $verified['jp_total'],
                'daily_allowance_rate' => $dailyAllowanceRate,
                'daily_allowance_total' => $dailyAllowanceTotal,
                'payload' => [
                    'present_dates' => $verified['present_dates'],
                    'approved_subject_permissions' => $verified['approved_permission_dates'],
                ],
            ],
        ];
    }

    private function buildNonTeacherPayroll(Employee $employee, int $month, int $year): array
    {
        $baseSalary = (float) ($employee->nonTeacherDetail?->salaryRate?->rate ?? 0);
        $totalAllowance = 0.0;
        $totalDeduction = 0.0;

        $components = [
            [
                'description' => 'Gaji Bulanan Sesuai Kesepakatan',
                'amount' => $baseSalary,
                'category' => PayrollCategory::Tunjangan->value,
            ],
        ];

        foreach ($employee->salaryComponents as $empComp) {
            $category = $empComp->salaryComponent->type === 'tunjangan'
                ? PayrollCategory::Tunjangan->value
                : PayrollCategory::Potongan->value;

            $components[] = [
                'description' => $empComp->salaryComponent->name,
                'amount' => (float) $empComp->amount,
                'category' => $category,
            ];

            if ($category === PayrollCategory::Tunjangan->value) {
                $totalAllowance += (float) $empComp->amount;
            } else {
                $totalDeduction += (float) $empComp->amount;
            }
        }

        // 1. Hitung Hari Kerja Efektif bulan ini
        $workingDays = $this->calculateEffectiveWorkingDays($month, $year, (int) $employee->unit_id);
        
        // 2. Hitung Hari Berbayar (Hadir, Terlambat, Sakit, Izin)
        // Kita tidak menghitung Alpa, tapi menghitung apa yang BERHAK dibayar.
        $paidDays = Attendance::query()
            ->dailyRecords()
            ->where('employee_id', $employee->id)
            ->whereIn('status', [
                AttendanceStatus::Hadir->value,
                AttendanceStatus::Terlambat->value,
                AttendanceStatus::Izin->value,
                AttendanceStatus::Sakit->value
            ])
            ->whereHas('schedule', function ($query) use ($month, $year): void {
                $query->whereMonth('work_date', $month)
                    ->whereYear('work_date', $year)
                    ->where('day_type', '!=', DayType::Libur->value);
            })
            ->count();

        // 3. Hitung Selisih Hari (Hari tidak dibayar / Unpaid Days)
        // Ini otomatis meng-cover Alpa DAN hari sebelum karyawan tersebut Join (jika baru masuk)
        $unpaidDays = max(0, $workingDays - $paidDays);
        $absenceDeduction = ($unpaidDays > 0 && $workingDays > 0) ? ($baseSalary / $workingDays) * $unpaidDays : 0.0;

        if ($absenceDeduction > 0) {
            $totalDeduction += $absenceDeduction;
            $components[] = [
                'description' => sprintf('Potongan Proporsional (%d/%d hari tidak masuk)', $unpaidDays, $workingDays),
                'amount' => $absenceDeduction,
                'category' => PayrollCategory::Potongan->value,
            ];
        }

        return [
            'base_salary' => $baseSalary,
            'total_allowance' => $totalAllowance,
            'total_deduction' => $totalDeduction,
            'net_salary' => max($baseSalary + $totalAllowance - $totalDeduction, 0),
            'components' => $components,
            'snapshot' => [
                'unit_id' => $employee->unit_id,
                'rate_gaji' => $baseSalary,
                'verified_jp_total' => 0,
                'daily_allowance_rate' => 0,
                'daily_allowance_total' => 0,
                'payload' => [
                    'alpa_count' => $alpaCount,
                ],
            ],
        ];
    }

    private function calculateVerifiedTeacherJp(Employee $employee, int $month, int $year, int $unitId): array
    {
        $presentDates = Attendance::query()
            ->dailyRecords()
            ->where('employee_id', $employee->id)
            ->whereIn('status', [AttendanceStatus::Hadir->value, AttendanceStatus::Terlambat->value])
            ->whereHas('schedule', function ($query) use ($month, $year, $unitId): void {
                $query->whereMonth('work_date', $month)
                    ->whereYear('work_date', $year)
                    ->where('unit_id', $unitId);
            })
            ->with('schedule')
            ->get()
            ->pluck('schedule.work_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->values();

        $teacherDetail = $employee->teacherDetail;
        if (! $teacherDetail || $presentDates->isEmpty()) {
            return [
                'jp_total' => 0,
                'present_days' => 0,
                'present_dates' => [],
                'approved_permission_dates' => [],
            ];
        }

        $sessions = $teacherDetail->teacherSubjectUnits()
            ->where('unit_id', $unitId)
            ->get();

        $approvedPermissions = SubjectPermission::query()
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy(fn ($permission) => $permission->date->toDateString());

        $jpTotal = 0.0;
        $permissionDates = [];

        foreach ($presentDates as $dateStr) {
            $dayName = $this->dayNameForDate(Carbon::parse($dateStr));
            $daySessions = $sessions->where('day_name', $dayName);
            $dayPermissions = $approvedPermissions->get($dateStr, collect());

            if ($dayPermissions->isNotEmpty()) {
                $permissionDates[] = $dateStr;
            }

            foreach ($daySessions as $session) {
                $isPermitted = $dayPermissions->contains(function ($permission) use ($session): bool {
                    return ($permission->jadwal_id && $permission->jadwal_id === $session->jadwal_id)
                        || ((int) $permission->teacher_subject_unit_id === (int) $session->id);
                });

                if (! $isPermitted) {
                    $jpTotal += (float) ($session->hours_per_week ?? 1);
                }
            }
        }

        return [
            'jp_total' => $jpTotal,
            'present_days' => $presentDates->count(),
            'present_dates' => $presentDates->all(),
            'approved_permission_dates' => array_values(array_unique($permissionDates)),
        ];
    }

    private function calculateAlpaCount(Employee $employee, int $month, int $year, int $unitId): int
    {
        return Attendance::query()
            ->dailyRecords()
            ->where('employee_id', $employee->id)
            ->where('status', AttendanceStatus::Alpa->value)
            ->whereHas('schedule', function ($query) use ($month, $year, $unitId): void {
                $query->whereMonth('work_date', $month)
                    ->whereYear('work_date', $year)
                    ->where('unit_id', $unitId)
                    ->where('day_type', '!=', DayType::Libur->value);
            })
            ->count();
    }

    /**
     * Menghitung jumlah hari kerja efektif (Bukan Libur) dalam sebulan untuk unit tertentu.
     */
    private function calculateEffectiveWorkingDays(int $month, int $year, int $unitId): int
    {
        return Schedule::query()
            ->where('unit_id', $unitId)
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->where('day_type', '!=', DayType::Libur->value)
            ->count();
    }

    private function dayNameForDate(Carbon $date): string
    {
        return match ($date->dayOfWeekIso) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            default => 'Minggu',
        };
    }
}
