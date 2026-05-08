<?php

namespace App\Http\Controllers\Karyawan;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Concerns\HandlesTeacherAttendance;
use App\Models\Schedule;
use App\Models\SubjectPermission;
use App\Models\TeacherSubjectUnit;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    use HandlesTeacherAttendance;

    public function index(ScheduleService $scheduleService): View|RedirectResponse
    {
        $employee = auth()->user();

        $dayNameMap = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
        ];
        $todayName = $dayNameMap[now()->dayOfWeekIso] ?? 'Senin';

        $scheduleService->ensureSchedulesForRange(
            $employee->unit_id,
            now()->startOfWeek(Carbon::MONDAY),
            now()->endOfWeek(Carbon::SATURDAY)
        );

        $groupedTeacherSubjectUnits = $employee->teacherDetail
            ? $employee->teacherDetail->teacherSubjectUnits()
                ->with('subject', 'unit')
                ->get()
                ->groupBy('day_name')
            : collect();

        $todayTeacherSubjectUnits = $this->todayTeacherSubjectUnits($employee);

        $weekSchedules = Schedule::where('unit_id', $employee->unit_id)
            ->whereBetween('work_date', [
                now()->startOfWeek(Carbon::MONDAY)->startOfDay(),
                now()->endOfWeek(Carbon::SATURDAY)->endOfDay(),
            ])
            ->orderBy('work_date')
            ->get()
            ->keyBy(fn ($schedule) => $dayNameMap[$schedule->work_date->dayOfWeekIso] ?? 'Unknown');

        $todaySchedule = $weekSchedules->get($todayName);
        $todayDailyAttendance = $todaySchedule
            ? Attendance::query()
                ->where('employee_id', $employee->id)
                ->where('schedule_id', $todaySchedule->id)
                ->dailyRecords()
                ->first()
            : null;

        $dailyAttendances = Attendance::query()
            ->where('employee_id', $employee->id)
            ->dailyRecords()
            ->whereHas('schedule', function ($query) {
                $query->whereBetween('work_date', [
                    now()->startOfWeek(Carbon::MONDAY)->startOfDay(),
                    now()->endOfWeek(Carbon::SATURDAY)->endOfDay(),
                ]);
            })
            ->with('schedule')
            ->get()
            ->keyBy('schedule_id');

        $sessionAttendances = Attendance::where('employee_id', $employee->id)
            ->teacherSessions()
            ->with('schedule')
            ->whereHas('schedule', function ($query) {
                $query->whereBetween('work_date', [
                    now()->startOfWeek(Carbon::MONDAY)->startOfDay(),
                    now()->endOfWeek(Carbon::SATURDAY)->endOfDay(),
                ]);
            })
            ->get();

        $subjectPermissions = SubjectPermission::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [
                now()->startOfWeek(Carbon::MONDAY)->toDateString(),
                now()->endOfWeek(Carbon::SATURDAY)->toDateString(),
            ])
            ->get();

        $dailyLeaves = \App\Models\LeaveRequest::where('employee_id', $employee->id)
            ->where('status', \App\Enums\LeaveStatus::Disetujui->value)
            ->where(function ($query) {
                $query->whereBetween('start_date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->orWhereBetween('end_date', [now()->startOfWeek(), now()->endOfWeek()]);
            })
            ->get();

        $attendancePeriodLabel = sprintf(
            '%s - %s',
            now()->startOfWeek()->translatedFormat('d M Y'),
            now()->endOfWeek()->translatedFormat('d M Y')
        );

        return view('karyawan.jadwal.index', compact(
            'groupedTeacherSubjectUnits',
            'weekSchedules',
            'todayTeacherSubjectUnits',
            'attendancePeriodLabel',
            'sessionAttendances',
            'subjectPermissions',
            'todaySchedule',
            'todayName',
            'todayDailyAttendance',
            'dailyAttendances',
            'dailyLeaves'
        ));
    }

    public function izinSesi(
        Request $request,
        TeacherSubjectUnit $teacherSubjectUnit,
        ScheduleService $scheduleService
    ): RedirectResponse {
        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:255'],
            'schedule_date' => ['required', 'date'],
        ]);

        $employee = auth()->user();
        $scheduleDate = Carbon::parse($validated['schedule_date'])->toDateString();
        $schedule = $scheduleService->ensureScheduleExists($employee->unit_id, $scheduleDate);

        abort_unless($teacherSubjectUnit->teacher_detail_id === $employee->teacherDetail?->id, 403);

        $sessionStart = Carbon::parse($scheduleDate . ' ' . $teacherSubjectUnit->start_time->format('H:i:s'));
        if ($sessionStart->lte(now())) {
            session()->flash('error', 'Izin per jam hanya bisa diajukan sebelum sesi dimulai.');
            return back();
        }

        $dailyAttendance = Attendance::query()
            ->where('employee_id', $employee->id)
            ->where('schedule_id', $schedule->id)
            ->dailyRecords()
            ->first();

        if (($dailyAttendance->status->value ?? $dailyAttendance->status ?? null) === AttendanceStatus::Alpa->value) {
            session()->flash('error', 'Izin per jam tidak tersedia saat absensi harian berstatus Alpa.');
            return back();
        }

        $exists = SubjectPermission::query()
            ->where('employee_id', $employee->id)
            ->where('schedule_id', $schedule->id)
            ->where('jadwal_id', $teacherSubjectUnit->jadwal_id)
            ->whereDate('date', $scheduleDate)
            ->exists();

        if ($exists) {
            session()->flash('error', 'Permintaan izin untuk sesi ini sudah ada.');
            return back();
        }

        SubjectPermission::create([
            'employee_id' => $employee->id,
            'schedule_id' => $schedule->id,
            'teacher_subject_unit_id' => $teacherSubjectUnit->id,
            'jadwal_id' => $teacherSubjectUnit->jadwal_id,
            'date' => $scheduleDate,
            'reason' => $validated['notes'],
            'status' => 'pending',
        ]);

        session()->flash('success', 'Permintaan izin sesi telah dikirim dan menunggu persetujuan Admin.');
        return back();
    }
}
