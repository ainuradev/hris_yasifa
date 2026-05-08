<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Schedule;
use App\Http\Controllers\Admin\PenggajianController;
use Illuminate\Http\Request;

class April2026SimulationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Memulai Simulasi Absensi & Gaji Bulan April 2026...');

        $now = now();
        $aprilStart = Carbon::create(2026, 4, 1)->startOfDay();
        $aprilEnd = Carbon::create(2026, 4, 30)->endOfDay();
        $adminId = DB::table('employees')->where('role', 'admin_pusat')->value('id');

        // Bersihkan data absensi bulan April saja
        \App\Models\Attendance::whereHas('schedule', function($q) use ($aprilStart, $aprilEnd) {
                $q->whereBetween('work_date', [$aprilStart->format('Y-m-d'), $aprilEnd->format('Y-m-d')]);
            })
            ->delete();

        // Bersihkan data gaji bulan April
        $payrollIds = DB::table('payrolls')->where('month', 4)->where('year', 2026)->pluck('id');
        DB::table('payroll_history')->whereIn('payroll_id', $payrollIds)->delete();
        DB::table('payroll_details')->whereIn('payroll_id', $payrollIds)->delete();
        DB::table('payrolls')->whereIn('id', $payrollIds)->delete();

        $employees = Employee::with('teacherDetail.teacherSubjectUnits', 'nonTeacherDetail')->get();
        $schedules = Schedule::whereBetween('work_date', [$aprilStart->format('Y-m-d'), $aprilEnd->format('Y-m-d')])->get()->groupBy('unit_id');

        $this->command->info('1. Membuat simulasi kehadiran harian untuk semua karyawan...');
        
        $attendancesToInsert = [];

        foreach ($employees as $employee) {
            if (!$employee->unit_id) continue; // Skip admin pusat

            $unitSchedules = $schedules->get($employee->unit_id);
            if (!$unitSchedules) continue;

            foreach ($unitSchedules as $schedule) {
                $date = Carbon::parse($schedule->work_date);
                if ($date->dayOfWeekIso === 7) continue; // Skip Minggu

                // Randomize status: 80% hadir, 10% terlambat, 5% izin, 5% alpa
                $rand = rand(1, 100);
                if ($rand <= 80) {
                    $status = 'hadir';
                } elseif ($rand <= 90) {
                    $status = 'terlambat';
                } elseif ($rand <= 95) {
                    $status = 'izin';
                } else {
                    $status = 'alpa';
                }

                $checkedInAt = in_array($status, ['hadir', 'terlambat']) 
                    ? $date->copy()->setTime(7, $status === 'hadir' ? rand(0, 29) : rand(31, 59))
                    : null;
                $checkedOutAt = in_array($status, ['hadir', 'terlambat'])
                    ? $date->copy()->setTime(15, rand(0, 30))
                    : null;

                $attendancesToInsert[] = [
                    'employee_id' => $employee->id,
                    'schedule_id' => $schedule->id,
                    'teacher_subject_unit_id' => null,
                    'jadwal_id' => null,
                    'checked_in_at' => $checkedInAt,
                    'checked_out_at' => $checkedOutAt,
                    'latitude' => in_array($status, ['hadir', 'terlambat']) ? -6.9147440 : null,
                    'longitude' => in_array($status, ['hadir', 'terlambat']) ? 107.6098100 : null,
                    'status' => $status,
                    'notes' => 'Simulasi absensi April',
                    'is_approved' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // Jika guru, buatkan juga absensi sesinya
                if ($employee->type->value === 'guru' && $employee->teacherDetail && in_array($status, ['hadir', 'terlambat'])) {
                    $dayName = $this->indonesianDayName($date);
                    $sessions = $employee->teacherDetail->teacherSubjectUnits->where('day_name', $dayName);
                    
                    foreach ($sessions as $session) {
                        $attendancesToInsert[] = [
                            'employee_id' => $employee->id,
                            'schedule_id' => $schedule->id,
                            'teacher_subject_unit_id' => $session->id,
                            'jadwal_id' => $session->jadwal_id,
                            'checked_in_at' => $date->copy()->setTimeFromTimeString($session->start_time),
                            'checked_out_at' => $date->copy()->setTimeFromTimeString($session->end_time),
                            'latitude' => -6.9147440,
                            'longitude' => 107.6098100,
                            'status' => 'hadir',
                            'notes' => 'Hadir sesi mengajar',
                            'is_approved' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }
        }

        // Insert in chunks to avoid memory issues
        foreach (array_chunk($attendancesToInsert, 500) as $chunk) {
            DB::table('attendances')->insert($chunk);
        }

        $this->command->info('2. Generate Penggajian untuk bulan April...');
        
        $units = DB::table('units')->pluck('id');
        $controller = app(PenggajianController::class);
        $adminUser = Employee::find($adminId);

        foreach ($units as $unitId) {
            $request = Request::create('/admin/penggajian/generate', 'POST', [
                'month' => 4,
                'year' => 2026,
                'unit_id' => $unitId,
            ]);
            $request->setUserResolver(fn() => $adminUser);
            
            app()->call([$controller, 'generate'], ['request' => $request]);
        }

        // Ubah status gaji dari draft ke dibayar
        $newPayrolls = DB::table('payrolls')->where('month', 4)->where('year', 2026)->get();
        foreach ($newPayrolls as $p) {
            DB::table('payrolls')->where('id', $p->id)->update([
                'status' => 'dibayar',
                'paid_at' => Carbon::create(2026, 4, 25)->format('Y-m-d'),
                'updated_at' => $now
            ]);
            DB::table('payroll_history')->insert([
                'payroll_id' => $p->id,
                'field_changed' => 'status',
                'old_value' => 'draft',
                'new_value' => 'dibayar',
                'changed_by' => $adminId,
                'changed_at' => $now
            ]);
        }

        $this->command->info('Selesai! Data absensi dan slip gaji riil untuk April 2026 berhasil digenerate.');
    }

    private function indonesianDayName(Carbon $date): string
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
