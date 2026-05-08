<?php

namespace App\Models\Concerns;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\TeacherSubjectUnit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

trait HandlesTeacherAttendance
{
    private function todayTeacherSubjectUnits(Employee $employee): Collection
    {
        $employee->loadMissing([
            'teacherDetail.teacherSubjectUnits.subject',
            'teacherDetail.teacherSubjectUnits.unit',
        ]);

        return ($employee->teacherDetail?->teacherSubjectUnits ?? collect())
            ->filter(fn ($item) => mb_strtolower((string) $item->day_name) === mb_strtolower($this->todayDayName()))
            ->sortBy('start_time')
            ->values();
    }

    private function activeTeacherSession(Collection $teacherSubjectUnits): ?TeacherSubjectUnit
    {
        $now = now();

        return $teacherSubjectUnits->first(function (TeacherSubjectUnit $item) use ($now): bool {
            $sessionStart = Carbon::parse(today()->format('Y-m-d').' '.$item->start_time?->format('H:i:s'));
            $sessionEnd = Carbon::parse(today()->format('Y-m-d').' '.$item->end_time?->format('H:i:s'));

            return $now->between($sessionStart, $sessionEnd);
        });
    }

    private function todayDayName(): string
    {
        return now()->translatedFormat('l');
    }

    private function teacherAttendanceForSchedule(Employee $employee, Schedule $schedule): Collection
    {
        return Attendance::query()
            ->with(['teacherSubjectUnit.subject', 'teacherSubjectUnit.unit'])
            ->where('employee_id', $employee->id)
            ->where('schedule_id', $schedule->id)
            ->teacherSessions()
            ->get()
            ->keyBy(fn (Attendance $attendance) => $attendance->jadwal_id ?? $attendance->teacher_subject_unit_id);
    }
}
