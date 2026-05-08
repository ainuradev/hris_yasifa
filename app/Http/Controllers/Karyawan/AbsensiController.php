<?php

namespace App\Http\Controllers\Karyawan;

use App\Enums\AttendanceStatus;
use App\Enums\DayType;
use App\Enums\EmployeeType;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Support\AttendanceLocation;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index(ScheduleService $scheduleService): View
    {
        $employee = auth()->user();
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfDay();
        
        $schedule = $scheduleService->ensureScheduleExists($employee->unit_id, today());

        $attendanceQuery = Attendance::query()
            ->with(['schedule', 'teacherSubjectUnit.subject', 'teacherSubjectUnit.unit'])
            ->where('employee_id', $employee->id)
            ->whereHas('schedule', function ($query) use ($periodStart, $periodEnd): void {
                $query->whereBetween('work_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);
            });

        $attendanceToday = $schedule
            ? Attendance::where('employee_id', $employee->id)
                ->where('schedule_id', $schedule->id)
                ->dailyRecords()
                ->first()
            : null;

        $attendances = $attendanceQuery
            ->dailyRecords()
            ->latest('checked_in_at')
            ->paginate(20);


        $attendancePeriodLabel = sprintf(
            '%s - %s',
            $periodStart->translatedFormat('d M Y'),
            $periodEnd->translatedFormat('d M Y')
        );

        return view('karyawan.absensi.index', compact(
            'schedule',
            'attendanceToday',
            'attendances',
            'attendancePeriodLabel'
        ));
    }

    public function store(Request $request, ScheduleService $scheduleService): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $employee = auth()->user();

        $schedule = $scheduleService->ensureScheduleExists($employee->unit_id, today());

        if ($schedule->day_type === DayType::Libur) {
            session()->flash('error', 'Hari ini adalah hari libur.');

            return back();
        }

        $now = now();
        $currentTime = $now->format('H:i');

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('schedule_id', $schedule->id)
            ->dailyRecords()
            ->first();

        if ($attendance && $attendance->checked_out_at) {
            session()->flash('error', 'Absensi hari ini sudah selesai.');

            return back();
        }

        // Check operational hours: 07:00 - 14:30
        if ($currentTime < '07:00' || $currentTime > '14:30') {
            session()->flash('error', 'Di luar jam operasional (07:00 - 14:30).');

            return back();
        }

        if ($attendance) {
            // Perform check-out
            $attendance->update([
                'checked_out_at' => $now,
                'notes' => $validated['notes'] ?? $attendance->notes,
            ]);

            session()->flash('success', 'Absensi pulang berhasil disimpan.');

            return back();
        }

        // Perform check-in (Tepat waktu <= 07:10)
        $status = $currentTime > '07:10' ? AttendanceStatus::Terlambat->value : AttendanceStatus::Hadir->value;

        Attendance::create([
            'employee_id' => $employee->id,
            'schedule_id' => $schedule->id,
            'checked_in_at' => $now,
            'checked_out_at' => null,
            'status' => $status,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Auto-attendance untuk jadwal mengajar (sesi)
        $employee->load('teacherDetail.teacherSubjectUnits');
        if ($employee->teacherDetail) {
            $dayNameMap = [
                1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
                4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
            ];
            $todayName = $dayNameMap[$now->dayOfWeekIso] ?? 'Senin';

            $sessions = $employee->teacherDetail->teacherSubjectUnits()->where('day_name', $todayName)->get();

            foreach ($sessions as $session) {
                // Determine status based on session start time
                $sessionStart = \Carbon\Carbon::parse($session->start_time)->format('H:i');
                $sessionStatus = ($currentTime > $sessionStart) ? AttendanceStatus::Alpa->value : AttendanceStatus::Hadir->value;

                // Check if existing record is there (e.g., Izin)
                $existingSession = Attendance::where('employee_id', $employee->id)
                    ->where('schedule_id', $schedule->id)
                    ->where('jadwal_id', $session->jadwal_id)
                    ->first();

                if (!$existingSession) {
                    Attendance::create([
                        'employee_id' => $employee->id,
                        'schedule_id' => $schedule->id,
                        'teacher_subject_unit_id' => $session->id,
                        'jadwal_id' => $session->jadwal_id,
                        'checked_in_at' => $now,
                        'status' => $sessionStatus,
                        'is_approved' => true,
                    ]);
                }
            }
        }

        session()->flash('success', 'Absensi berhasil disimpan.');

        return back();
    }
    public function koreksi(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'proof' => ['required', 'image', 'max:2048'], // Max 2MB
        ]);

        $employee = auth()->user();
        $proofPath = $request->file('proof')->store('corrections', 'public');

        \App\Models\AttendanceCorrection::create([
            'employee_id' => $employee->id,
            'date' => $validated['date'],
            'reason' => $validated['reason'],
            'proof_path' => $proofPath,
            'status' => 'pending',
        ]);

        session()->flash('success', 'Pengajuan koreksi absensi berhasil dikirim. Menunggu persetujuan Admin.');

        return back();
    }
}
