<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttendanceStatus;
use App\Enums\DayType;
use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\TeacherSubjectUnit;
use App\Models\Unit;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use App\Exports\AbsensiExport;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiController extends Controller
{
    public function index(Request $request, ScheduleService $scheduleService): View
    {
        $admin = $request->user();
        $date = Carbon::parse($request->input('date', today()->toDateString()))->toDateString();
        
        // Ensure schedules exist for this date
        if ($admin->isAdminPusat()) {
            $units = \App\Models\Unit::all();
            foreach ($units as $unit) {
                $scheduleService->ensureScheduleExists($unit->id, $date);
            }
        } else {
            $scheduleService->ensureScheduleExists($admin->unit_id, $date);
        }
        $selectedUnitId = $admin->isAdminPusat()
            ? ($request->filled('unit_id') ? $request->integer('unit_id') : null)
            : (int) $admin->unit_id;

        $attendances = Attendance::with(['employee.unit', 'schedule.unit', 'teacherSubjectUnit.subject'])
            ->whereHas('employee', function ($query) use ($admin): void {
                $query->visibleToAdmin($admin);
            })
            ->whereHas('schedule', function ($query) use ($date, $selectedUnitId): void {
                $query->whereDate('work_date', $date)
                    ->when($selectedUnitId, fn ($scheduleQuery) => $scheduleQuery->where('unit_id', $selectedUnitId));
            })
            ->latest('checked_in_at')
            ->paginate(15, ['*'], 'absensi_page')
            ->withQueryString();

        // Fetch pending session-based leaves for teachers
        $pendingSessionLeaves = Attendance::query()
            ->with(['employee.unit', 'teacherSubjectUnit.subject'])
            ->teacherSessions()
            ->where('status', AttendanceStatus::Izin->value)
            ->where('is_approved', false)
            ->whereHas('employee', function ($query) use ($admin): void {
                $query->visibleToAdmin($admin);
            })
            ->latest()
            ->paginate(10, ['*'], 'sessions_page');

        $summaryQuery = Attendance::query()
            ->whereHas('employee', function ($query) use ($admin): void {
                $query->visibleToAdmin($admin);
            })
            ->whereHas('schedule', function ($query) use ($date, $selectedUnitId): void {
                $query->whereDate('work_date', $date)
                    ->when($selectedUnitId, fn ($scheduleQuery) => $scheduleQuery->where('unit_id', $selectedUnitId));
            });

        $summary = [
            'hadir' => (clone $summaryQuery)->where('status', AttendanceStatus::Hadir->value)->count(),
            'terlambat' => (clone $summaryQuery)->where('status', AttendanceStatus::Terlambat->value)->count(),
            'izin' => (clone $summaryQuery)->where('status', AttendanceStatus::Izin->value)->count(),
            'sakit' => (clone $summaryQuery)->where('status', AttendanceStatus::Sakit->value)->count(),
            'alpa' => (clone $summaryQuery)->where('status', AttendanceStatus::Alpa->value)->count(),
        ];
        $totalHadir = $summary['hadir'] + $summary['terlambat'];
        $summary['persentase_kehadiran'] = array_sum($summary) > 0
            ? round(($totalHadir / array_sum($summary)) * 100, 2)
            : 0;
        $summary['sesi_guru'] = (clone $summaryQuery)->teacherSessions()->count();
        $summary['absensi_harian'] = (clone $summaryQuery)->dailyRecords()->count();

        $units = $admin->isAdminPusat()
            ? Unit::orderBy('name')->get()
            : Unit::whereKey($admin->unit_id)->get();
        $pendingLeaves = LeaveRequest::with('employee')
            ->where('status', LeaveStatus::Pending->value)
            ->whereHas('employee', function ($query) use ($admin): void {
                $query->visibleToAdmin($admin);
            })
            ->latest('start_date')
            ->paginate(20, ['*'], 'leave_page')
            ->withQueryString();

        // -------------------------------------------------------------
        // CALCULATE MONTHLY REKAP MATRIKS
        // -------------------------------------------------------------
        $month = Carbon::parse($date)->month;
        $year = Carbon::parse($date)->year;
        $daysInMonth = Carbon::parse($date)->daysInMonth;

        $employeesList = Employee::query()
            ->with(['teacherDetail.teacherSubjectUnits'])
            ->visibleToAdmin($admin)
            ->when($selectedUnitId, fn($q) => $q->where('unit_id', $selectedUnitId))
            ->whereIn('role', ['karyawan', 'admin_unit'])
            ->orderBy('name')
            ->get();

        $monthlyAttendances = Attendance::query()
            ->dailyRecords()
            ->with('schedule')
            ->whereHas('schedule', function ($q) use ($month, $year) {
                $q->whereMonth('work_date', $month)
                  ->whereYear('work_date', $year);
            })
            ->get()
            ->groupBy('employee_id');

        $rekapMatriks = [];
        foreach ($employeesList as $employee) {
            $empAtts = $monthlyAttendances->get($employee->id, collect());
            $days = [];
            
            $hadir = 0; $terlambat = 0; $izin = 0; $alpa = 0; $sakit = 0;
            
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $currentDate = Carbon::create($year, $month, $i)->format('Y-m-d');
                $att = $empAtts->first(fn($a) => Carbon::parse($a->schedule->work_date)->format('Y-m-d') === $currentDate);
                
                if ($att) {
                    $statusValue = $att->status->value ?? $att->status;
                    if ($statusValue === 'hadir') { $days[$i] = 'H'; $hadir++; }
                    elseif ($statusValue === 'terlambat') { $days[$i] = 'T'; $terlambat++; }
                    elseif ($statusValue === 'izin') { $days[$i] = 'I'; $izin++; }
                    elseif ($statusValue === 'sakit') { $days[$i] = 'S'; $sakit++; }
                    elseif ($statusValue === 'alpa') { $days[$i] = 'A'; $alpa++; }
                    else { $days[$i] = '-'; }
                } else {
                    $days[$i] = '';
                }
            }
            
            $jtm = '-';
            if (($employee->type->value ?? $employee->type) === 'guru' && $employee->teacherDetail) {
                $presentDates = $empAtts->filter(fn($a) => in_array($a->status->value ?? $a->status, ['hadir', 'terlambat']))
                    ->pluck('schedule.work_date')
                    ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                    ->unique()
                    ->toArray();
                
                if (!empty($presentDates)) {
                    $jtmTotal = (float) $employee->teacherDetail->teacherSubjectUnits->sum(function ($session) use ($presentDates): float {
                        $count = 0;
                        foreach ($presentDates as $dateStr) {
                            $dt = Carbon::parse($dateStr);
                            $dayName = match ($dt->dayOfWeekIso) {
                                1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', default => 'Minggu',
                            };
                            if ($dayName === $session->day_name) {
                                $count++;
                            }
                        }
                        if ($count === 0 || !$session->start_time || !$session->end_time) return 0;
                        $minutes = Carbon::parse($session->start_time)->diffInMinutes(Carbon::parse($session->end_time));
                        return round(($minutes / 60) * $count, 2);
                    });
                    $jtm = $jtmTotal;
                } else {
                    $jtm = 0;
                }
            }

            $rekapMatriks[] = [
                'name' => $employee->name,
                'type' => ucfirst(str_replace('_', ' ', $employee->type->value ?? $employee->type)),
                'days' => $days,
                'summary' => [
                    'hadir' => $hadir,
                    'terlambat' => $terlambat,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpa' => $alpa,
                    'jtm' => $jtm,
                ]
            ];
        }

        return view('admin.absensi.index', compact(
            'attendances', 
            'summary', 
            'units', 
            'date', 
            'pendingLeaves', 
            'pendingSessionLeaves', 
            'selectedUnitId',
            'rekapMatriks'
        ));
    }

    public function approveSessionSesi(Request $request, Attendance $attendance): RedirectResponse
    {
        $admin = $request->user();
        
        if (! $admin->isAdminPusat() && $attendance->employee->unit_id !== $admin->unit_id) {
            abort(403);
        }

        $attendance->update([
            'is_approved' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        session()->flash('success', 'Izin sesi guru telah disetujui.');
        return back();
    }

    public function rejectSessionSesi(Request $request, Attendance $attendance): RedirectResponse
    {
        $admin = $request->user();
        
        if (! $admin->isAdminPusat() && $attendance->employee->unit_id !== $admin->unit_id) {
            abort(403);
        }

        // When rejected, we could either delete the record or mark it as rejected.
        // For simplicity and to allow teacher to try again, we'll delete it.
        $attendance->delete();

        session()->flash('success', 'Izin sesi guru telah ditolak.');
        return back();
    }

    public function show(Request $request, Employee $employee): View
    {
        $this->authorizeEmployeeAccess($employee, $request->user());

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $attendances = Attendance::with(['schedule', 'teacherSubjectUnit.subject', 'teacherSubjectUnit.unit'])
            ->where('employee_id', $employee->id)
            ->whereHas('schedule', function ($query) use ($month, $year): void {
                $query->whereMonth('work_date', $month)
                    ->whereYear('work_date', $year);
            })
            ->latest('checked_in_at')
            ->paginate(20)
            ->withQueryString();

        $baseSummaryQuery = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereHas('schedule', function ($query) use ($month, $year): void {
                $query->whereMonth('work_date', $month)
                    ->whereYear('work_date', $year);
            });

        $rekap = [
            'hadir' => (clone $baseSummaryQuery)->where('status', AttendanceStatus::Hadir->value)->count(),
            'terlambat' => (clone $baseSummaryQuery)->where('status', AttendanceStatus::Terlambat->value)->count(),
            'izin' => (clone $baseSummaryQuery)->where('status', AttendanceStatus::Izin->value)->count(),
            'sakit' => (clone $baseSummaryQuery)->where('status', AttendanceStatus::Sakit->value)->count(),
            'alpa' => (clone $baseSummaryQuery)->where('status', AttendanceStatus::Alpa->value)->count(),
        ];

        $total = array_sum($rekap);
        $totalHadir = $rekap['hadir'] + $rekap['terlambat'];
        $rekap['persentase_kehadiran'] = $total > 0 ? round(($totalHadir / $total) * 100, 2) : 0;
        $rekap['sesi_guru'] = (clone $baseSummaryQuery)->teacherSessions()->count();
        $rekap['absensi_harian'] = (clone $baseSummaryQuery)->dailyRecords()->count();

        return view('admin.absensi.show', compact('employee', 'attendances', 'rekap', 'month', 'year'));
    }

    public function export(Request $request)
    {
        $admin = $request->user();

        $month = $request->filled('month') ? (int) $request->month : now()->month;
        $year  = $request->filled('year') ? (int) $request->year : now()->year;

        $unitId = $admin->isAdminPusat()
            ? ($request->filled('unit_id') ? (int) $request->unit_id : null)
            : (int) $admin->unit_id;

        $unitName = $unitId ? (Unit::find($unitId)?->name ?? 'Semua') : 'Semua Unit';
        $filename = 'rekap-absensi-' . str($unitName)->slug() . '-' . $month . '-' . $year . '.xlsx';

        return Excel::download(new AbsensiExport($unitId, $month, $year), $filename);
    }

    public function approveLeave(LeaveRequest $leaveRequest): RedirectResponse
    {
        abort_if(request()->user()->isAdminPusat(), 403, 'Hanya Admin Unit yang berhak menyetujui pengajuan cuti.');
        $employee = $leaveRequest->employee()->firstOrFail();
        $this->authorizeEmployeeAccess($employee, request()->user());

        DB::transaction(function () use ($leaveRequest, $employee): void {
            $leaveRequest->update([
                'status' => LeaveStatus::Disetujui->value,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            $attendanceStatus = $leaveRequest->leave_type === LeaveType::Sakit
                ? AttendanceStatus::Sakit->value
                : AttendanceStatus::Izin->value;

            foreach (CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date) as $date) {
                $schedule = Schedule::query()
                    ->where('unit_id', $employee->unit_id)
                    ->whereDate('work_date', $date->format('Y-m-d'))
                    ->where('day_type', '!=', DayType::Libur->value)
                    ->first();

                if (! $schedule) {
                    continue;
                }



                Attendance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'schedule_id' => $schedule->id,
                        'teacher_subject_unit_id' => null,
                        'jadwal_id' => null,
                    ],
                    [
                        'status' => $attendanceStatus,
                        'notes' => $leaveRequest->reason,
                        'checked_in_at' => null,
                        'checked_out_at' => null,
                    ]
                );
            }
        });

        session()->flash('success', 'Pengajuan cuti berhasil disetujui.');

        return back();
    }

    public function rejectLeave(LeaveRequest $leaveRequest): RedirectResponse
    {
        abort_if(request()->user()->isAdminPusat(), 403, 'Hanya Admin Unit yang berhak menolak pengajuan cuti.');
        $employee = $leaveRequest->employee()->firstOrFail();
        $this->authorizeEmployeeAccess($employee, request()->user());

        $leaveRequest->update([
            'status' => LeaveStatus::Ditolak->value,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        session()->flash('success', 'Pengajuan cuti berhasil ditolak.');

        return back();
    }

    private function authorizeEmployeeAccess(Employee $employee, Employee $admin): void
    {
        if ($admin->isAdminPusat()) {
            return;
        }

        abort_if($employee->unit_id !== $admin->unit_id, 403);
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
