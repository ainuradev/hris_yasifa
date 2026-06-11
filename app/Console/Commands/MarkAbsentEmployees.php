<?php

namespace App\Console\Commands;

use App\Enums\AttendanceStatus;
use App\Enums\DayType;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\ScheduleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkAbsentEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mark-alpa {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark employees who have no attendance record as Alpa for the given date (default today)';

    /**
     * Execute the console command.
     */
    public function handle(ScheduleService $scheduleService)
    {
        $dateStr = $this->argument('date') ?: today()->toDateString();
        $this->info("Processing Auto-Alpa for date: {$dateStr}");

        $employees = Employee::where('status', 'aktif')->get();
        $count = 0;

        foreach ($employees as $employee) {
            $schedule = $scheduleService->ensureScheduleExists($employee->unit_id, $dateStr);

            // Skip if it's a holiday or off-day
            if ($schedule->day_type === DayType::Libur) {
                continue;
            }

            // Check if daily attendance record already exists
            $exists = Attendance::query()
                ->where('employee_id', $employee->id)
                ->where('schedule_id', $schedule->id)
                ->dailyRecords()
                ->exists();

            if (!$exists) {
                Attendance::create([
                    'employee_id' => $employee->id,
                    'schedule_id' => $schedule->id,
                    'status' => AttendanceStatus::Alpa->value,
                    'notes' => 'Sistem: Otomatis Alpa (Tidak ada record absensi)',
                    'is_approved' => true,
                ]);
                $count++;
            }
        }

        $this->info("Successfully marked {$count} employees as Alpa.");
    }
}
