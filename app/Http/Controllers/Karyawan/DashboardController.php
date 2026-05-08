<?php

namespace App\Http\Controllers\Karyawan;

use App\Enums\DayType;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\Schedule;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $employee = auth()->user();
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfDay();
        $todaySchedule = Schedule::query()
            ->where('unit_id', $employee->unit_id)
            ->whereDate('work_date', today())
            ->first();

        $todayAttendance = $todaySchedule
            ? Attendance::where('employee_id', $employee->id)->where('schedule_id', $todaySchedule->id)->first()
            : null;

        $jadwalMengajarHariIni = collect();
        if ($employee->type->value === 'guru' && $todaySchedule && $todaySchedule->day_type !== DayType::Libur) {
            $employee->loadMissing(['teacherDetail.teacherSubjectUnits.subject', 'teacherDetail.teacherSubjectUnits.unit', 'teacherDetail.teacherSubjectUnits.class']);
            
            $dayName = $this->translateDayName(now()->dayOfWeekIso);
            
            $jadwalMengajarHariIni = ($employee->teacherDetail?->teacherSubjectUnits ?? collect())
                ->where('day_name', $dayName)
                ->sortBy('start_time')
                ->values();
            
            // Load permissions for today
            $todayPermissions = \App\Models\SubjectPermission::where('employee_id', $employee->id)
                ->whereDate('date', today())
                ->get()
                ->keyBy(function($perm) {
                    return $perm->teacher_subject_unit_id ?? $perm->jadwal_id;
                });
                
            $jadwalMengajarHariIni->each(function($item) use ($todayPermissions) {
                $item->current_permission = $todayPermissions->get($item->id) ?? $todayPermissions->get($item->jadwal_id);
            });
        }

        $latestPayrolls = Payroll::where('employee_id', $employee->id)
            ->whereIn('status', ['final', 'dibayar'])
            ->latest('year')
            ->latest('month')
            ->limit(2)
            ->get();

        $attendanceQuery = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereHas('schedule', function ($query) use ($periodStart, $periodEnd): void {
                $query->whereBetween('work_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);
            });

        if ($employee->type->value === 'guru') {
            $attendanceQuery->teacherSessions();
        } else {
            $attendanceQuery->dailyRecords();
        }

        $attendanceSummary = [
            'period_label' => sprintf(
                '%s - %s',
                $periodStart->translatedFormat('d M Y'),
                $periodEnd->translatedFormat('d M Y')
            ),
            'total_records' => (clone $attendanceQuery)->count(),
            'hadir_count' => (clone $attendanceQuery)->where('status', 'hadir')->count(),
            'non_hadir_count' => (clone $attendanceQuery)->where('status', '!=', 'hadir')->count(),
        ];

        $announcements = Announcement::with(['createdBy', 'unit'])
            ->where(function ($query) use ($employee): void {
                $query->where('is_global', true)
                    ->orWhere('unit_id', $employee->unit_id);
            })
            ->latest()
            ->limit(3)
            ->get();

        return view('karyawan.dashboard', compact(
            'todaySchedule',
            'todayAttendance',
            'jadwalMengajarHariIni',
            'latestPayrolls',
            'attendanceSummary',
            'announcements'
        ));
    }

    private function translateDayName(int $dayOfWeek): string
    {
        return match ($dayOfWeek) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
            default => 'Senin',
        };
    }
}
