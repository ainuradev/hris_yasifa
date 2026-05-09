<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Unit;
use App\Models\Employee;
use App\Models\TeacherDetail;
use App\Models\NonTeacherDetail;
use App\Models\SalaryRate;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\TeacherSubjectUnit;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Enums\EmployeeType;
use App\Enums\EmployeeRole;
use App\Enums\EmployeeStatus;
use App\Enums\AttendanceStatus;
use App\Http\Controllers\Admin\PenggajianController;
use Illuminate\Http\Request;

class MasterSimulationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Memulai Seeding Data Master & Simulasi Lengkap...');

        // 1. Bersihkan Data (TRUNCATE)
        $this->command->warn('Membersihkan data lama...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Attendance::truncate();
        DB::table('payrolls')->truncate();
        DB::table('payroll_details')->truncate();
        DB::table('payroll_history')->truncate();
        DB::table('payroll_snapshots')->truncate();
        Schedule::truncate();
        TeacherSubjectUnit::truncate();
        TeacherDetail::truncate();
        NonTeacherDetail::truncate();
        Employee::truncate();
        SalaryRate::truncate();
        SchoolClass::truncate();
        Subject::truncate();
        Unit::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Buat Unit
        $this->command->info('Membuat Unit...');
        $unitData = [
            ['name' => 'MTs Sirojul Falah', 'jenjang' => 'MTs', 'kepala_unit' => 'H. Ahmad Syarif'],
            ['name' => 'MA Sirojul Falah', 'jenjang' => 'MA', 'kepala_unit' => 'Hj. Siti Aminah'],
            ['name' => 'MI Sirojul Falah', 'jenjang' => 'MI', 'kepala_unit' => 'Bpk. Yusuf'],
        ];
        $units = [];
        foreach ($unitData as $ud) {
            $units[] = Unit::create($ud);
        }

        // 3. Buat Admin Pusat
        Employee::create([
            'name' => 'Super Admin HRIS',
            'nik' => '123456789',
            'email' => 'admin@hris.com',
            'password' => 'password',
            'role' => EmployeeRole::AdminPusat,
            'type' => EmployeeType::NonGuru,
            'status' => EmployeeStatus::Aktif,
        ]);

        // 4. Buat Master Data per Unit (Subjects, Classes, Rates, Employees)
        $subjects_list = ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'IPA', 'IPS', 'PAI', 'PJOK', 'Seni Budaya'];
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        foreach ($units as $unit) {
            $this->command->info("Memproses Unit: {$unit->name}...");

            // Create Salary Rates for this unit
            $rateGuru = SalaryRate::create([
                'unit_id' => $unit->id,
                'jabatan' => 'Guru',
                'type' => EmployeeType::Guru,
                'rate' => 25000, // per JP
            ]);
            $rateStaff = SalaryRate::create([
                'unit_id' => $unit->id,
                'jabatan' => 'Staff Administrasi',
                'type' => EmployeeType::NonGuru,
                'rate' => 50000, // per hari
            ]);

            // Create Subjects
            $unitSubjects = [];
            foreach ($subjects_list as $sname) {
                $unitSubjects[] = Subject::create([
                    'unit_id' => $unit->id,
                    'name' => $sname,
                    'jp_per_week' => 4,
                ]);
            }

            // Create Classes
            $classes = [];
            $levels = $unit->jenjang === 'MI' ? [1, 2, 3, 4, 5, 6] : [7, 8, 9];
            if ($unit->jenjang === 'MA') $levels = [10, 11, 12];
            
            foreach ($levels as $lv) {
                foreach (['A', 'B'] as $sec) {
                    $classes[] = SchoolClass::create([
                        'unit_id' => $unit->id,
                        'name' => "Kelas {$lv}{$sec}",
                        'level' => $lv,
                        'academic_year' => '2025/2026',
                    ]);
                }
            }

            // Create Admin Unit
            Employee::create([
                'unit_id' => $unit->id,
                'name' => "Admin {$unit->jenjang}",
                'nik' => 'ADM' . $unit->id . rand(100, 999),
                'email' => strtolower($unit->jenjang) . "@hris.com",
                'password' => 'password',
                'role' => EmployeeRole::AdminUnit,
                'type' => EmployeeType::NonGuru,
                'status' => EmployeeStatus::Aktif,
            ]);

            // Track slots to avoid collisions: [class_id][day][time] = true
            $busySlots = [];

            // Create 5 Teachers
            for ($i = 1; $i <= 5; $i++) {
                $teacher = Employee::create([
                    'unit_id' => $unit->id,
                    'name' => "Guru {$unit->jenjang} {$i}",
                    'nik' => 'TCH' . $unit->id . $i . rand(10, 99),
                    'nuptk' => 'NUPTK' . $unit->id . $i . rand(1000, 9999),
                    'email' => "guru{$i}.{$unit->jenjang}@hris.com",
                    'password' => 'password',
                    'role' => EmployeeRole::Karyawan,
                    'type' => EmployeeType::Guru,
                    'status' => EmployeeStatus::Aktif,
                    'tmt_pegawai' => now()->subYears(rand(1, 5)),
                ]);

                $td = TeacherDetail::create([
                    'employee_id' => $teacher->id,
                    'salary_rate_id' => $rateGuru->id,
                    'jabatan' => 'Guru Mapel',
                ]);

                // Assign 4 subjects/classes
                $assignedCount = 0;
                $maxAttempts = 50;
                $attempts = 0;

                while ($assignedCount < 4 && $attempts < $maxAttempts) {
                    $cls = $classes[rand(0, count($classes) - 1)];
                    $day = $days[rand(0, 5)];
                    $startTime = sprintf('%02d:00', 7 + ($assignedCount * 2));
                    
                    if (!isset($busySlots[$cls->id][$day][$startTime])) {
                        TeacherSubjectUnit::create([
                            'teacher_detail_id' => $td->id,
                            'unit_id' => $unit->id,
                            'class_id' => $cls->id,
                            'subject_id' => $unitSubjects[rand(0, count($unitSubjects)-1)]->id,
                            'day_name' => $day,
                            'start_time' => $startTime,
                            'end_time' => sprintf('%02d:00', 9 + ($assignedCount * 2)),
                            'hours_per_week' => 2,
                        ]);
                        $busySlots[$cls->id][$day][$startTime] = true;
                        $assignedCount++;
                    }
                    $attempts++;
                }
            }

            // Create 3 Non-Teachers
            for ($i = 1; $i <= 3; $i++) {
                $staff = Employee::create([
                    'unit_id' => $unit->id,
                    'name' => "Staff {$unit->jenjang} {$i}",
                    'nik' => 'STF' . $unit->id . $i . rand(10, 99),
                    'email' => "staff{$i}.{$unit->jenjang}@hris.com",
                    'password' => 'password',
                    'role' => EmployeeRole::Karyawan,
                    'type' => EmployeeType::NonGuru,
                    'status' => EmployeeStatus::Aktif,
                ]);

                NonTeacherDetail::create([
                    'employee_id' => $staff->id,
                    'salary_rate_id' => $rateStaff->id,
                    'jabatan' => 'Staff Operasional',
                ]);
            }
        }

        // 5. Create Daily Schedules (45 days ago to today)
        $this->command->info('Membuat Jadwal Kerja Harian (Schedules)...');
        $startDate = now()->subDays(45);
        $endDate = now();
        $dateRunner = $startDate->copy();

        while ($dateRunner <= $endDate) {
            foreach ($units as $unit) {
                Schedule::create([
                    'unit_id' => $unit->id,
                    'work_date' => $dateRunner->format('Y-m-d'),
                    'check_in_start' => '07:00:00',
                    'check_in_end' => '08:30:00',
                    'day_type' => $dateRunner->isWeekend() ? 'libur' : 'normal',
                ]);
            }
            $dateRunner->addDay();
        }

        // 6. Generate Attendance Data
        $this->command->info('Membuat Data Absensi (Simulasi Hadir 90%)...');
        $allSchedules = Schedule::all();
        $allEmployees = Employee::with('teacherDetail.teacherSubjectUnits', 'nonTeacherDetail')->whereNotNull('unit_id')->get();
        $attendances = [];
        $nowTimestamp = now();

        foreach ($allSchedules as $sched) {
            if ($sched->day_type === 'libur') continue;
            
            $date = Carbon::parse($sched->work_date);
            $dayNameIndo = $this->getIndoDayName($date);

            foreach ($allEmployees as $emp) {
                if ($emp->unit_id != $sched->unit_id) continue;

                // Template to ensure all rows have same keys for batch insert
                $template = [
                    'employee_id' => $emp->id,
                    'schedule_id' => $sched->id,
                    'teacher_subject_unit_id' => null,
                    'jadwal_id' => null,
                    'checked_in_at' => null,
                    'checked_out_at' => null,
                    'status' => null,
                    'is_approved' => true,
                    'created_at' => $nowTimestamp,
                    'updated_at' => $nowTimestamp,
                ];

                // 90% chance to be present
                if (rand(1, 100) <= 90) {
                    $status = rand(1, 10) == 1 ? AttendanceStatus::Terlambat : AttendanceStatus::Hadir;
                    
                    // Daily Check-in
                    $attendances[] = array_merge($template, [
                        'checked_in_at' => $date->copy()->setTime(7, $status == AttendanceStatus::Terlambat ? rand(31, 59) : rand(0, 30)),
                        'checked_out_at' => $date->copy()->setTime(14, rand(30, 59)),
                        'status' => $status->value,
                    ]);

                    // If teacher, check their sessions
                    if ($emp->type == EmployeeType::Guru && $emp->teacherDetail) {
                        $sessions = $emp->teacherDetail->teacherSubjectUnits->where('day_name', $dayNameIndo);
                        foreach ($sessions as $session) {
                            $attendances[] = array_merge($template, [
                                'teacher_subject_unit_id' => $session->id,
                                'jadwal_id' => $session->jadwal_id,
                                'status' => AttendanceStatus::Hadir->value,
                            ]);
                        }
                    }
                } else {
                    // Alpa
                    $attendances[] = array_merge($template, [
                        'status' => AttendanceStatus::Alpa->value,
                    ]);
                }

                // Batch insert to save memory
                if (count($attendances) >= 500) {
                    DB::table('attendances')->insert($attendances);
                    $attendances = [];
                }
            }
        }
        if (!empty($attendances)) {
            DB::table('attendances')->insert($attendances);
        }

        // 7. Generate Payroll for last month
        $this->command->info('Generate Penggajian Bulan Lalu...');
        $lastMonth = now()->subMonth();
        $adminUser = Employee::where('role', 'admin_pusat')->first();
        $controller = app(PenggajianController::class);

        foreach ($units as $unit) {
            $req = Request::create('/admin/penggajian/generate', 'POST', [
                'month' => $lastMonth->month,
                'year' => $lastMonth->year,
                'unit_id' => $unit->id,
            ]);
            $req->setUserResolver(fn() => $adminUser);
            
            try {
                app()->call([$controller, 'generate'], ['request' => $req]);
            } catch (\Exception $e) {
                $this->command->error("Gagal generate gaji untuk {$unit->name}: " . $e->getMessage());
            }
        }

        $this->command->info('Seeding Selesai!');
    }

    private function getIndoDayName(Carbon $date): string
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
