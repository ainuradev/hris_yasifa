<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiExport implements FromView, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(
        private readonly ?int $unitId,
        private readonly int $month,
        private readonly int $year,
    ) {}

    public function view(): View
    {
        $employees = Employee::query()
            ->with(['unit', 'teacherDetail.teacherSubjectUnits', 'nonTeacherDetail'])
            ->when($this->unitId, fn($q) => $q->where('unit_id', $this->unitId))
            ->whereIn('role', ['karyawan', 'admin_unit'])
            ->orderBy('unit_id')
            ->orderBy('name')
            ->get();

        $attendances = Attendance::query()
            ->dailyRecords()
            ->whereHas('schedule', function ($q) {
                $q->whereMonth('work_date', $this->month)
                  ->whereYear('work_date', $this->year);
            })
            ->get()
            ->groupBy('employee_id');

        $jtms = [];
        foreach ($employees as $employee) {
            $jtms[$employee->id] = 0;
            if ($employee->type->value === 'guru' && $employee->teacherDetail) {
                $presentDates = $attendances->get($employee->id, collect())
                    ->filter(fn($att) => $att->status->value === 'hadir' || $att->status === 'hadir')
                    ->pluck('schedule.work_date')
                    ->filter()
                    ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
                    ->unique()
                    ->toArray();

                if (!empty($presentDates)) {
                    $jtms[$employee->id] = (float) $employee->teacherDetail->teacherSubjectUnits
                        ->sum(function ($session) use ($presentDates): float {
                            $count = 0;
                            foreach ($presentDates as $dateStr) {
                                $date = Carbon::parse($dateStr);
                                if ($this->dayNameForDate($date) === $session->day_name) {
                                    $count++;
                                }
                            }
                            if ($count === 0 || !$session->start_time || !$session->end_time) return 0;
                            $minutes = Carbon::parse($session->start_time)->diffInMinutes(Carbon::parse($session->end_time));
                            return round(($minutes / 60) * $count, 2);
                        });
                }
            }
        }

        return view('exports.absensi', [
            'employees' => $employees,
            'attendances' => $attendances,
            'jtms' => $jtms,
            'month' => $this->month,
            'year' => $this->year,
            'exportedAt' => now()->translatedFormat('d F Y, H:i'),
        ]);
    }

    public function title(): string
    {
        return 'Rekap Absensi';
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

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a2744']],
            ],
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a2744']],
            ],
        ];
    }
}
