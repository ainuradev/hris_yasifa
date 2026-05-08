<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();
        $today = Carbon::today();
        $contractEndDate = $today->copy()->addYear()->format('Y-m-d');
        $payrollPeriodStart = Carbon::create($today->year, 4, 1)->startOfDay();
        $schedulePeriodEnd = $today->copy()->addDays(7)->endOfDay();
        $attendancePeriodEnd = $today->copy()->subDay()->endOfDay();

        DB::transaction(function () use ($now, $today, $contractEndDate, $payrollPeriodStart, $schedulePeriodEnd, $attendancePeriodEnd) {
            $unitIds = $this->seedUnits($now);
            $salaryRateIds = $this->seedSalaryRates($now);
            $subjectIds = $this->seedSubjects($unitIds);
            $classIds = $this->seedClasses($unitIds, $now);
            $adminPusatId = $this->seedAdmins($unitIds, $contractEndDate, $now);

            $teachers = $this->seedTeachers($unitIds, $salaryRateIds, $subjectIds, $classIds, $contractEndDate, $now);

            $this->seedNonTeachers($unitIds, $salaryRateIds, $contractEndDate, $now);
            $this->seedAnnouncements($adminPusatId, $now);
            $this->seedLeaveRequests($teachers, $now);
            $this->seedEmployeeRequests($unitIds, $adminPusatId, $salaryRateIds, $now);

            $scheduleIds = $this->seedSchedules($unitIds, $payrollPeriodStart, $schedulePeriodEnd, $now);
            $this->seedTeacherAttendancesAndPayrolls(
                $teachers,
                $scheduleIds,
                $adminPusatId,
                $payrollPeriodStart,
                $attendancePeriodEnd,
                $today,
                $now
            );

            // Tambah absensi 5 hari kerja terakhir untuk bar chart dashboard
            $this->seedRecentDailyAttendances($teachers, $scheduleIds, $unitIds, $today, $now);

            // Seed session-level attendances for current week past days
            $this->seedCurrentWeekSessionAttendances($teachers, $scheduleIds, $today, $now);

            // Simulasi April 2026
            $this->call(April2026SimulationSeeder::class);
        });
    }

    private function seedUnits(Carbon $now): array
    {
        $unitIds = [];

        foreach ([
            ['name' => 'MI Sirojul Falah', 'jenjang' => 'MI', 'kepala_unit' => null],
            ['name' => 'MTs Sirojul Falah', 'jenjang' => 'MTs', 'kepala_unit' => null],
            ['name' => 'MA Sirojul Falah', 'jenjang' => 'MA', 'kepala_unit' => null],
        ] as $unit) {
            $unitIds[$unit['jenjang']] = DB::table('units')->insertGetId([
                ...$unit,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $unitIds;
    }

    private function seedSalaryRates(Carbon $now): array
    {
        $salaryRateIds = [];

        foreach ([
            ['jabatan' => 'Guru', 'type' => 'guru', 'rate' => 45000.00],
            ['jabatan' => 'Guru Senior', 'type' => 'guru', 'rate' => 60000.00],
            ['jabatan' => 'Staff Admin', 'type' => 'non_guru', 'rate' => 2200000.00],
            ['jabatan' => 'Security', 'type' => 'non_guru', 'rate' => 2000000.00],
        ] as $rate) {
            $salaryRateIds[$rate['jabatan']] = DB::table('salary_rates')->insertGetId([
                ...$rate,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $salaryRateIds;
    }

    private function seedSubjects(array $unitIds): array
    {
        $subjectIds = [];

        // 1. MATA PELAJARAN GLOBAL (Umum & Keagamaan - Semua Jenjang)
        $globalSubjects = [
            'Pendidikan Pancasila', 'Bahasa Indonesia', 'Matematika', 
            'Pendidikan Jasmani, Olahraga, dan Kesehatan (PJOK)', 
            'Seni Budaya', 'Al-Qur’an Hadis', 'Akidah Akhlak', 'Fikih', 
            'Sejarah Kebudayaan Islam (SKI)', 'Bahasa Arab'
        ];

        foreach ($globalSubjects as $name) {
            $id = DB::table('subjects')->insertGetId([
                'unit_id' => null,
                'name' => $name,
                'jp_per_week' => 4,
            ]);
            // Map to all units for seeder reference
            foreach ($unitIds as $uId) {
                $subjectIds[$uId][$name] = $id;
            }
        }

        // 2. MATA PELAJARAN KHUSUS MTs & MA
        $mtsMaSubjects = ['Bahasa Inggris', 'Informatika'];
        foreach ($mtsMaSubjects as $name) {
            $idMts = DB::table('subjects')->insertGetId(['unit_id' => $unitIds['MTs'], 'name' => $name, 'jp_per_week' => 4]);
            $idMa = DB::table('subjects')->insertGetId(['unit_id' => $unitIds['MA'], 'name' => $name, 'jp_per_week' => 4]);
            $subjectIds[$unitIds['MTs']][$name] = $idMts;
            $subjectIds[$unitIds['MA']][$name] = $idMa;
        }

        // 3. KHUSUS MI
        $miSubjects = ['IPAS'];
        foreach ($miSubjects as $name) {
            $id = DB::table('subjects')->insertGetId(['unit_id' => $unitIds['MI'], 'name' => $name, 'jp_per_week' => 4]);
            $subjectIds[$unitIds['MI']][$name] = $id;
        }

        // 4. KHUSUS MTs
        $mtsOnlySubjects = ['IPA', 'IPS'];
        foreach ($mtsOnlySubjects as $name) {
            $id = DB::table('subjects')->insertGetId(['unit_id' => $unitIds['MTs'], 'name' => $name, 'jp_per_week' => 4]);
            $subjectIds[$unitIds['MTs']][$name] = $id;
        }

        // 5. KHUSUS MA (Peminatan)
        $maPeminatan = [
            'Fisika', 'Kimia', 'Biologi', 'Matematika Lanjutan',
            'Ekonomi', 'Geografi', 'Sosiologi', 'Sejarah Peminatan',
            'Bahasa dan Sastra Indonesia', 'Bahasa Inggris Lanjutan', 'Bahasa Arab Lanjutan'
        ];
        foreach ($maPeminatan as $name) {
            $id = DB::table('subjects')->insertGetId(['unit_id' => $unitIds['MA'], 'name' => $name, 'jp_per_week' => 4]);
            $subjectIds[$unitIds['MA']][$name] = $id;
        }

        return $subjectIds;
    }

    private function seedClasses(array $unitIds, Carbon $now): array
    {
        $classIds = [];
        $classes = [
            // MI
            ['unit_id' => $unitIds['MI'], 'name' => '4A', 'level' => 4, 'major' => null],
            ['unit_id' => $unitIds['MI'], 'name' => '4B', 'level' => 4, 'major' => null],
            ['unit_id' => $unitIds['MI'], 'name' => '5A', 'level' => 5, 'major' => null],
            ['unit_id' => $unitIds['MI'], 'name' => '6A', 'level' => 6, 'major' => null],
            // MTs
            ['unit_id' => $unitIds['MTs'], 'name' => '7.1', 'level' => 7, 'major' => null],
            ['unit_id' => $unitIds['MTs'], 'name' => '7.2', 'level' => 7, 'major' => null],
            ['unit_id' => $unitIds['MTs'], 'name' => '8.1', 'level' => 8, 'major' => null],
            ['unit_id' => $unitIds['MTs'], 'name' => '9.1', 'level' => 9, 'major' => null],
            // MA
            ['unit_id' => $unitIds['MA'], 'name' => 'X IPA 1', 'level' => 10, 'major' => 'IPA'],
            ['unit_id' => $unitIds['MA'], 'name' => 'X IPA 2', 'level' => 10, 'major' => 'IPA'],
            ['unit_id' => $unitIds['MA'], 'name' => 'X IPS 1', 'level' => 10, 'major' => 'IPS'],
            ['unit_id' => $unitIds['MA'], 'name' => 'XI IPA 1', 'level' => 11, 'major' => 'IPA'],
            ['unit_id' => $unitIds['MA'], 'name' => 'XII IPA 1', 'level' => 12, 'major' => 'IPA'],
        ];

        foreach ($classes as $class) {
            $classIds[$class['name']] = DB::table('classes')->insertGetId([
                ...$class,
                'academic_year' => '2025/2026',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Add legacy names for backward compatibility in seeder if needed
        $classIds['Kelas 4 MI'] = $classIds['4A'];
        $classIds['Kelas 5 MI'] = $classIds['5A'];
        $classIds['Kelas 6 MI'] = $classIds['6A'];
        $classIds['Kelas 7 MTs'] = $classIds['7.1'];
        $classIds['Kelas 8 MTs'] = $classIds['8.1'];
        $classIds['Kelas 9 MTs'] = $classIds['9.1'];
        $classIds['Kelas 10 MA'] = $classIds['X IPA 1'];
        $classIds['Kelas 11 MA'] = $classIds['XI IPA 1'];
        $classIds['Kelas 12 MA'] = $classIds['XII IPA 1'];

        return $classIds;
    }

    private function seedAdmins(array $unitIds, string $contractEndDate, Carbon $now): int
    {
        $adminBirthDate = '1990-01-10';
        $adminDefaultPassword = 'admin123';

        $adminPusatId = DB::table('employees')->insertGetId([
            'unit_id' => null,
            'name' => 'Administrator Pusat',
            'nik' => 'ADMIN0001',
            'email' => 'admin@sirojulfalah.test',
            'phone' => '081200000000',
            'address' => 'Pusat Yayasan Sirojul Falah',
            'place_of_birth' => 'Bogor',
            'date_of_birth' => $adminBirthDate,
            'gender' => 'laki_laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'Kawin',
            'pendidikan_terakhir' => 'S1',
            'status_kepegawaian' => 'PTY',
            'emergency_contact_name' => 'Keluarga Admin Pusat',
            'emergency_contact_phone' => '081200000000',
            'password' => Hash::make($adminDefaultPassword),
            'must_change_password' => true,
            'type' => 'non_guru',
            'role' => 'admin_pusat',
            'status' => 'aktif',
            'contract_end_date' => $contractEndDate,
            'remember_token' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([
            ['unit_id' => $unitIds['MI'], 'name' => 'Admin MI Sirojul Falah', 'nik' => 'ADM001', 'email' => 'admin-mi@sirojulfalah.test'],
            ['unit_id' => $unitIds['MTs'], 'name' => 'Admin MTs Sirojul Falah', 'nik' => 'ADM002', 'email' => 'admin-mts@sirojulfalah.test'],
            ['unit_id' => $unitIds['MA'], 'name' => 'Admin MA Sirojul Falah', 'nik' => 'ADM003', 'email' => 'admin-ma@sirojulfalah.test'],
        ] as $adminUnit) {
            DB::table('employees')->insert([
                'unit_id' => $adminUnit['unit_id'],
                'name' => $adminUnit['name'],
                'nik' => $adminUnit['nik'],
                'email' => $adminUnit['email'],
                'phone' => '081234567890',
                'address' => 'Jl. Yayasan Sirojul Falah No. 1',
                'place_of_birth' => 'Bandung',
                'date_of_birth' => $adminBirthDate,
                'gender' => 'laki_laki',
                'agama' => 'Islam',
                'status_perkawinan' => 'Kawin',
                'pendidikan_terakhir' => 'S1',
                'status_kepegawaian' => 'PTY',
                'emergency_contact_name' => 'Keluarga Admin Unit',
                'emergency_contact_phone' => '081200000001',
                'password' => Hash::make($adminDefaultPassword),
                'must_change_password' => true,
                'type' => 'non_guru',
                'role' => 'admin_unit',
                'status' => 'aktif',
                'contract_end_date' => $contractEndDate,
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $adminPusatId;
    }

    private function seedTeachers(array $unitIds, array $salaryRateIds, array $subjectIds, array $classIds, string $contractEndDate, Carbon $now): array
    {
        $teachers = [];

        foreach ([
            [
                'unit_id' => $unitIds['MI'],
                'name' => 'Ahmad Fauzi',
                'nik' => 'GR001',
                'nuptk' => 'NUPTK001',
                'email' => 'ahmad.fauzi@sirojulfalah.test',
                'date_of_birth' => '1992-05-15',
                'gender' => 'laki_laki',
                'jabatan' => 'Guru',
                'salary_rate_id' => $salaryRateIds['Guru'],
                'attendance_exceptions' => [
                    '2026-04-14' => ['status' => 'alpa', 'notes' => 'Tidak hadir tanpa keterangan.'],
                ],
                'sessions' => [
                    ['subject' => 'Matematika', 'day_name' => 'Senin', 'class_name' => 'Kelas 4 MI', 'start_time' => '07:30:00', 'end_time' => '09:30:00', 'hours_per_week' => 2],
                    ['subject' => 'Matematika', 'day_name' => 'Selasa', 'class_name' => 'Kelas 5 MI', 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'hours_per_week' => 2],
                    ['subject' => 'Matematika', 'day_name' => 'Kamis', 'class_name' => 'Kelas 6 MI', 'start_time' => '07:30:00', 'end_time' => '09:30:00', 'hours_per_week' => 2],
                ],
            ],
            [
                'unit_id' => $unitIds['MTs'],
                'name' => 'Siti Aisyah',
                'nik' => 'GR002',
                'nuptk' => 'NUPTK002',
                'email' => 'siti.aisyah@sirojulfalah.test',
                'date_of_birth' => '1993-11-21',
                'gender' => 'perempuan',
                'jabatan' => 'Guru Senior',
                'salary_rate_id' => $salaryRateIds['Guru Senior'],
                'attendance_exceptions' => [
                    '2026-04-08' => ['status' => 'sakit', 'notes' => 'Izin sakit dengan surat dokter.'],
                ],
                'sessions' => [
                    ['subject' => 'IPA', 'day_name' => 'Rabu', 'class_name' => 'Kelas 7 MTs', 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'hours_per_week' => 2],
                    ['subject' => 'IPA', 'day_name' => 'Jumat', 'class_name' => 'Kelas 8 MTs', 'start_time' => '07:45:00', 'end_time' => '09:45:00', 'hours_per_week' => 2],
                    ['subject' => 'IPA', 'day_name' => 'Sabtu', 'class_name' => 'Kelas 9 MTs', 'start_time' => '08:15:00', 'end_time' => '10:15:00', 'hours_per_week' => 2],
                ],
            ],
            [
                'unit_id' => $unitIds['MA'],
                'name' => 'Budi Santoso',
                'nik' => 'GR003',
                'nuptk' => 'NUPTK003',
                'email' => 'budi.santoso@sirojulfalah.test',
                'date_of_birth' => '1991-02-03',
                'gender' => 'laki_laki',
                'jabatan' => 'Guru',
                'salary_rate_id' => $salaryRateIds['Guru'],
                'attendance_exceptions' => [],
                'sessions' => [
                    // Senin — 3 sesi
                    ['subject' => 'Bahasa Indonesia', 'day_name' => 'Senin', 'class_name' => 'Kelas 10 MA', 'start_time' => '07:30:00', 'end_time' => '09:00:00', 'hours_per_week' => 2],
                    ['subject' => 'Sejarah Kebudayaan Islam (SKI)',          'day_name' => 'Senin', 'class_name' => 'Kelas 11 MA', 'start_time' => '09:15:00', 'end_time' => '10:45:00', 'hours_per_week' => 2],
                    ['subject' => 'Pendidikan Pancasila',             'day_name' => 'Senin', 'class_name' => 'Kelas 12 MA', 'start_time' => '11:00:00', 'end_time' => '12:30:00', 'hours_per_week' => 2],
                    // Selasa — 2 sesi
                    ['subject' => 'Bahasa Inggris',   'day_name' => 'Selasa', 'class_name' => 'Kelas 10 MA', 'start_time' => '07:30:00', 'end_time' => '09:00:00', 'hours_per_week' => 2],
                    ['subject' => 'Pendidikan Pancasila',             'day_name' => 'Selasa', 'class_name' => 'Kelas 11 MA', 'start_time' => '09:15:00', 'end_time' => '10:45:00', 'hours_per_week' => 2],
                    // Rabu — 3 sesi
                    ['subject' => 'Bahasa Indonesia', 'day_name' => 'Rabu', 'class_name' => 'Kelas 11 MA', 'start_time' => '07:30:00', 'end_time' => '09:00:00', 'hours_per_week' => 2],
                    ['subject' => 'Sejarah Kebudayaan Islam (SKI)',          'day_name' => 'Rabu', 'class_name' => 'Kelas 10 MA', 'start_time' => '09:15:00', 'end_time' => '10:45:00', 'hours_per_week' => 2],
                    ['subject' => 'Bahasa Inggris',   'day_name' => 'Rabu', 'class_name' => 'Kelas 12 MA', 'start_time' => '11:00:00', 'end_time' => '12:30:00', 'hours_per_week' => 2],
                    // Kamis — 2 sesi
                    ['subject' => 'Pendidikan Pancasila',             'day_name' => 'Kamis', 'class_name' => 'Kelas 12 MA', 'start_time' => '07:30:00', 'end_time' => '09:00:00', 'hours_per_week' => 2],
                    ['subject' => 'Sejarah Kebudayaan Islam (SKI)',          'day_name' => 'Kamis', 'class_name' => 'Kelas 11 MA', 'start_time' => '09:15:00', 'end_time' => '10:45:00', 'hours_per_week' => 2],
                    // Jumat — 3 sesi
                    ['subject' => 'Bahasa Indonesia', 'day_name' => 'Jumat', 'class_name' => 'Kelas 12 MA', 'start_time' => '07:30:00', 'end_time' => '09:00:00', 'hours_per_week' => 2],
                    ['subject' => 'Bahasa Inggris',   'day_name' => 'Jumat', 'class_name' => 'Kelas 10 MA', 'start_time' => '09:15:00', 'end_time' => '10:45:00', 'hours_per_week' => 2],
                    ['subject' => 'Pendidikan Pancasila',             'day_name' => 'Jumat', 'class_name' => 'Kelas 11 MA', 'start_time' => '11:00:00', 'end_time' => '12:30:00', 'hours_per_week' => 2],
                    // Sabtu — 2 sesi
                    ['subject' => 'Sejarah Kebudayaan Islam (SKI)',          'day_name' => 'Sabtu', 'class_name' => 'Kelas 12 MA', 'start_time' => '07:30:00', 'end_time' => '09:00:00', 'hours_per_week' => 2],
                    ['subject' => 'Bahasa Indonesia', 'day_name' => 'Sabtu', 'class_name' => 'Kelas 10 MA', 'start_time' => '09:15:00', 'end_time' => '10:45:00', 'hours_per_week' => 2],
                ],
            ],
        ] as $teacher) {
            $employeeId = DB::table('employees')->insertGetId([
                'unit_id' => $teacher['unit_id'],
                'name' => $teacher['name'],
                'nik' => $teacher['nik'],
                'nuptk' => $teacher['nuptk'],
                'npk' => 'NPK' . rand(100000, 999999),
                'email' => $teacher['email'],
                'phone' => '08123456789X',
                'address' => 'Jl. Guru No. ' . rand(1, 100),
                'place_of_birth' => 'Bandung',
                'date_of_birth' => $teacher['date_of_birth'],
                'gender' => $teacher['gender'],
                'agama' => 'Islam',
                'nama_ibu_kandung' => 'Ibu ' . $teacher['name'],
                'status_perkawinan' => 'Kawin',
                'pendidikan_terakhir' => 'S1',
                'tahun_lulus' => rand(2010, 2020),
                'status_kepegawaian' => 'GTY',
                'tmt_pegawai' => '2020-07-01',
                'no_sk_pengangkatan' => 'SK/YYS/' . rand(100, 999) . '/2020',
                'emergency_contact_name' => 'Keluarga ' . $teacher['name'],
                'emergency_contact_phone' => '08120000000X',
                'password' => Hash::make(Carbon::parse($teacher['date_of_birth'])->format('dmY')),
                'must_change_password' => true,
                'type' => 'guru',
                'role' => 'karyawan',
                'status' => 'aktif',
                'contract_end_date' => $contractEndDate,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $teacherDetailId = DB::table('teacher_details')->insertGetId([
                'employee_id' => $employeeId,
                'salary_rate_id' => $teacher['salary_rate_id'],
                'jabatan' => $teacher['jabatan'],
            ]);

            $sessions = [];

            foreach ($teacher['sessions'] as $session) {
                $jadwalId = (string) Str::uuid();
                $teacherSubjectUnitId = DB::table('teacher_subject_unit')->insertGetId([
                    'jadwal_id' => $jadwalId,
                    'teacher_detail_id' => $teacherDetailId,
                    'unit_id' => $teacher['unit_id'],
                    'class_id' => $classIds[$session['class_name']] ?? null,
                    'subject_id' => $subjectIds[$teacher['unit_id']][$session['subject']],
                    'day_name' => $session['day_name'],
                    'start_time' => $session['start_time'],
                    'end_time' => $session['end_time'],
                    'hours_per_week' => $session['hours_per_week'],
                ]);

                $sessions[] = [
                    ...$session,
                    'id' => $teacherSubjectUnitId,
                    'jadwal_id' => $jadwalId,
                ];
            }

            $teachers[] = [
                'employee_id' => $employeeId,
                'unit_id' => $teacher['unit_id'],
                'name' => $teacher['name'],
                'salary_rate' => $this->salaryRateAmount($teacher['jabatan']),
                'attendance_exceptions' => $teacher['attendance_exceptions'],
                'sessions' => $sessions,
            ];
        }

        return $teachers;
    }

    private function seedNonTeachers(array $unitIds, array $salaryRateIds, string $contractEndDate, Carbon $now): void
    {
        foreach ([
            [
                'unit_id' => $unitIds['MI'],
                'name' => 'Nisa Rahma',
                'nik' => 'NG001',
                'email' => 'nisa.rahma@sirojulfalah.test',
                'date_of_birth' => '1994-08-20',
                'gender' => 'perempuan',
                'jabatan' => 'Staff Admin',
                'salary_rate_id' => $salaryRateIds['Staff Admin'],
            ],
            [
                'unit_id' => $unitIds['MTs'],
                'name' => 'Dedi Kurniawan',
                'nik' => 'NG002',
                'email' => 'dedi.kurniawan@sirojulfalah.test',
                'date_of_birth' => '1990-12-12',
                'gender' => 'laki_laki',
                'jabatan' => 'Security',
                'salary_rate_id' => $salaryRateIds['Security'],
            ],
        ] as $nonTeacher) {
            $employeeId = DB::table('employees')->insertGetId([
                'unit_id' => $nonTeacher['unit_id'],
                'name' => $nonTeacher['name'],
                'nik' => $nonTeacher['nik'],
                'email' => $nonTeacher['email'],
                'phone' => '08121'.rand(100000, 999999),
                'address' => 'Alamat '.$nonTeacher['name'],
                'place_of_birth' => 'Garut',
                'date_of_birth' => $nonTeacher['date_of_birth'],
                'gender' => $nonTeacher['gender'],
                'emergency_contact_name' => 'Keluarga '.$nonTeacher['name'],
                'emergency_contact_phone' => '08128'.rand(100000, 999999),
                'password' => Hash::make(Carbon::parse($nonTeacher['date_of_birth'])->format('dmY')),
                'must_change_password' => true,
                'type' => 'non_guru',
                'role' => 'karyawan',
                'status' => 'aktif',
                'contract_end_date' => $contractEndDate,
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('non_teacher_details')->insert([
                'employee_id' => $employeeId,
                'salary_rate_id' => $nonTeacher['salary_rate_id'],
                'jabatan' => $nonTeacher['jabatan'],
            ]);
        }
    }

    private function seedAnnouncements(int $adminPusatId, Carbon $now): void
    {
        DB::table('announcements')->insert([
            'unit_id' => null,
            'created_by' => $adminPusatId,
            'title' => 'Pengumuman Sistem HRIS',
            'content' => 'Selamat datang di sistem HRIS Yayasan Sirojul Falah. Data contoh April sudah berisi jadwal mengajar guru, absensi per sesi, dan slip gaji berdasarkan realisasi jam hadir.',
            'category' => 'umum',
            'is_global' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function seedLeaveRequests(array $teachers, Carbon $now): void
    {
        if (empty($teachers)) return;

        $leaveData = [
            [
                'teacher_idx' => 0,
                'leave_type'  => 'sakit',
                'start_date'  => $now->copy()->addDays(2)->format('Y-m-d'),
                'end_date'    => $now->copy()->addDays(3)->format('Y-m-d'),
                'total_days'  => 2,
                'reason'      => 'Sakit demam berdarah, perlu istirahat.',
                'status'      => 'pending',
            ],
            [
                'teacher_idx' => 1,
                'leave_type'  => 'tahunan',
                'start_date'  => $now->copy()->addDays(5)->format('Y-m-d'),
                'end_date'    => $now->copy()->addDays(7)->format('Y-m-d'),
                'total_days'  => 3,
                'reason'      => 'Keperluan keluarga ke luar kota.',
                'status'      => 'pending',
            ],
            [
                'teacher_idx' => 2,
                'leave_type'  => 'penting',
                'start_date'  => $now->copy()->addDays(1)->format('Y-m-d'),
                'end_date'    => $now->copy()->addDays(1)->format('Y-m-d'),
                'total_days'  => 1,
                'reason'      => 'Mengurus keperluan administrasi penting di luar.',
                'status'      => 'pending',
            ],
            [
                'teacher_idx' => 0,
                'leave_type'  => 'tahunan',
                'start_date'  => $now->copy()->subDays(10)->format('Y-m-d'),
                'end_date'    => $now->copy()->subDays(8)->format('Y-m-d'),
                'total_days'  => 3,
                'reason'      => 'Liburan keluarga akhir bulan.',
                'status'      => 'disetujui',
            ],
            [
                'teacher_idx' => 1,
                'leave_type'  => 'sakit',
                'start_date'  => $now->copy()->subDays(5)->format('Y-m-d'),
                'end_date'    => $now->copy()->subDays(4)->format('Y-m-d'),
                'total_days'  => 2,
                'reason'      => 'Kontrol dokter rutin dan pemulihan.',
                'status'      => 'disetujui',
            ],
        ];

        foreach ($leaveData as $data) {
            $idx = $data['teacher_idx'];
            if (! isset($teachers[$idx])) continue;

            DB::table('leave_requests')->insert([
                'employee_id' => $teachers[$idx]['employee_id'],
                'leave_type'  => $data['leave_type'],
                'start_date'  => $data['start_date'],
                'end_date'    => $data['end_date'],
                'total_days'  => $data['total_days'],
                'reason'      => $data['reason'],
                'status'      => $data['status'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    private function seedEmployeeRequests(array $unitIds, int $adminPusatId, array $salaryRateIds, Carbon $now): void
    {
        DB::table('employee_requests')->insert([
            'unit_id' => $unitIds['MTs'],
            'requested_by' => $adminPusatId, // or unit admin, doesn't matter for seeding
            'name' => 'Fahmi Reza',
            'nik' => 'REQ001',
            'email' => 'fahmi.reza@sirojulfalah.test',
            'date_of_birth' => '1995-10-12',
            'type' => 'guru',
            'status' => 'pending',
            'employment_status' => 'aktif',
            'contract_end_date' => $now->copy()->addYear()->format('Y-m-d'),
            'jabatan' => 'Guru',
            'salary_rate_id' => $salaryRateIds['Guru'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function seedSchedules(array $unitIds, Carbon $periodStart, Carbon $periodEnd, Carbon $now): array
    {
        $scheduleConfigs = [
            'MI' => ['check_in_start' => '07:30:00', 'check_in_end' => '15:00:00'],
            'MTs' => ['check_in_start' => '07:30:00', 'check_in_end' => '15:00:00'],
            'MA' => ['check_in_start' => '07:30:00', 'check_in_end' => '15:00:00'],
        ];

        // Daftar Libur Nasional 2026 (Estimasi & Contoh)
        $holidays = [
            '2026-01-01', // Tahun Baru
            '2026-03-20', // Idul Fitri 1
            '2026-03-21', // Idul Fitri 2
            '2026-05-01', // Hari Buruh
            '2026-05-21', // Idul Adha
            '2026-08-17', // Hari Kemerdekaan
            '2026-12-25', // Natal
        ];

        $scheduleIds = [];
        // Kita paksa seed untuk seluruh tahun 2026
        $fullYearStart = Carbon::create(2026, 1, 1);
        $fullYearEnd = Carbon::create(2026, 12, 31);

        $dates = collect();
        for ($date = $fullYearStart->copy(); $date->lte($fullYearEnd); $date->addDay()) {
            $dates->push($date->copy());
        }

        foreach ($dates as $date) {
            $dateStr = $date->format('Y-m-d');
            $isHoliday = in_array($dateStr, $holidays);
            $isSunday = $date->dayOfWeekIso === 7;

            $dayType = ($isHoliday || $isSunday) ? 'libur' : ($date->dayOfWeekIso === 6 ? 'setengah_hari' : 'normal');

            foreach ($unitIds as $jenjang => $unitId) {
                $scheduleIds[$unitId][$dateStr] = DB::table('schedules')->insertGetId([
                    'unit_id' => $unitId,
                    'work_date' => $dateStr,
                    'check_in_start' => $scheduleConfigs[$jenjang]['check_in_start'],
                    'check_in_end' => $scheduleConfigs[$jenjang]['check_in_end'],
                    'day_type' => $dayType,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        return $scheduleIds;
    }

    private function seedTeacherAttendancesAndPayrolls(
        array $teachers,
        array $scheduleIds,
        int $adminPusatId,
        Carbon $periodStart,
        Carbon $periodEnd,
        Carbon $today,
        Carbon $now
    ): void {
        foreach ($teachers as $teacher) {
            $monthlyHours = 0.0;
            $alpaCount = 0;

            for ($date = $periodStart->copy(); $date->lte($periodEnd); $date->addDay()) {
                $dateKey = $date->format('Y-m-d');
                $dayName = $this->indonesianDayName($date);
                $scheduleId = $scheduleIds[$teacher['unit_id']][$dateKey] ?? null;

                if (! $scheduleId || $date->dayOfWeekIso === 7) {
                    continue;
                }

                $exception = $teacher['attendance_exceptions'][$dateKey] ?? null;
                $statusForDay = $exception['status'] ?? 'hadir';
                $notes = $exception['notes'] ?? 'Absensi harian otomatis dari seeder.';
                
                $checkedInAt = $statusForDay === 'hadir'
                    ? Carbon::parse($dateKey.' 07:30:00')
                    : null;
                $checkedOutAt = $statusForDay === 'hadir'
                    ? Carbon::parse($dateKey.' 15:00:00')
                    : null;

                DB::table('attendances')->insert([
                    'employee_id' => $teacher['employee_id'],
                    'schedule_id' => $scheduleId,
                    'teacher_subject_unit_id' => null,
                    'jadwal_id' => null,
                    'checked_in_at' => $checkedInAt,
                    'checked_out_at' => $checkedOutAt,
                    'latitude' => $statusForDay === 'hadir' ? -6.9147440 : null,
                    'longitude' => $statusForDay === 'hadir' ? 107.6098100 : null,
                    'status' => $statusForDay,
                    'notes' => $notes,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($statusForDay === 'hadir') {
                    $matchingSessions = collect($teacher['sessions'])
                        ->where('day_name', $dayName)
                        ->values();

                    foreach ($matchingSessions as $session) {
                        $monthlyHours += $this->calculateSessionHours($session['start_time'], $session['end_time']);
                    }
                }

                if ($statusForDay === 'alpa') {
                    $alpaCount++;
                }
            }

            $this->seedTeacherPayroll(
                $teacher,
                $monthlyHours,
                $alpaCount,
                $adminPusatId,
                $today,
                $now
            );
        }
    }

    private function seedTeacherPayroll(
        array $teacher,
        float $monthlyHours,
        int $alpaCount,
        int $adminPusatId,
        Carbon $today,
        Carbon $now
    ): void {
        $baseSalary = $teacher['salary_rate'] * $monthlyHours;
        $absenceDeduction = $alpaCount > 0 ? ($baseSalary / 22) * $alpaCount : 0;
        $netSalary = max($baseSalary - $absenceDeduction, 0);

        $payrollId = DB::table('payrolls')->insertGetId([
            'employee_id' => $teacher['employee_id'],
            'month' => 4,
            'year' => $today->year,
            'base_salary' => round($baseSalary, 2),
            'total_allowance' => 0,
            'total_deduction' => round($absenceDeduction, 2),
            'net_salary' => round($netSalary, 2),
            'status' => 'dibayar',
            'paid_at' => $today->format('Y-m-d'),
            'created_by' => $adminPusatId,
            'updated_by' => $adminPusatId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('payroll_details')->insert([
            'payroll_id' => $payrollId,
            'description' => sprintf(
                'Honor Mengajar Terealisasi (%s jam x Rp %s/jam)',
                rtrim(rtrim(number_format($monthlyHours, 2, '.', ''), '0'), '.'),
                number_format($teacher['salary_rate'], 0, ',', '.')
            ),
            'amount' => round($baseSalary, 2),
            'category' => 'tunjangan',
        ]);

        if ($absenceDeduction > 0) {
            DB::table('payroll_details')->insert([
                'payroll_id' => $payrollId,
                'description' => sprintf('Potongan Alpa (%d hari)', $alpaCount),
                'amount' => round($absenceDeduction, 2),
                'category' => 'potongan',
            ]);
        }

        DB::table('payroll_history')->insert([
            [
                'payroll_id' => $payrollId,
                'field_changed' => 'status',
                'old_value' => null,
                'new_value' => 'draft',
                'changed_by' => $adminPusatId,
                'changed_at' => $now->copy()->subMinutes(15),
            ],
            [
                'payroll_id' => $payrollId,
                'field_changed' => 'status',
                'old_value' => 'draft',
                'new_value' => 'final',
                'changed_by' => $adminPusatId,
                'changed_at' => $now->copy()->subMinutes(5),
            ],
            [
                'payroll_id' => $payrollId,
                'field_changed' => 'status',
                'old_value' => 'final',
                'new_value' => 'dibayar',
                'changed_by' => $adminPusatId,
                'changed_at' => $now,
            ],
        ]);
    }

    private function calculateSessionHours(string $startTime, string $endTime): float
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        return round($start->diffInMinutes($end) / 60, 2);
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

    private function salaryRateAmount(string $jabatan): float
    {
        return match ($jabatan) {
            'Guru Senior' => 60000.00,
            default => 45000.00,
        };
    }

    /**
     * Seed absensi harian untuk 5 hari kerja terakhir (termasuk hari ini).
     * Data ini dipakai bar chart "Kehadiran Mingguan" di dashboard admin.
     * Setiap sesi yang cocok di hari kerja tersebut akan dibuat record absensi-nya,
     * dengan status hadir/izin/sakit/alpa sesuai pola yang sudah ditentukan.
     */
    private function seedRecentDailyAttendances(
        array $teachers,
        array $scheduleIds,
        array $unitIds,
        Carbon $today,
        Carbon $now
    ): void {
        // Kumpulkan 5 hari kerja terakhir (termasuk hari ini, lewati Minggu)
        $workDays = [];
        $cursor   = $today->copy();
        while (count($workDays) < 5) {
            $cursor->subDay();
            if ($cursor->dayOfWeek !== 0) { // 0 = Minggu
                $workDays[] = $cursor->copy();
            }
        }
        $workDays = array_reverse($workDays); // urut dari terlama ke terbaru

        // Pola kehadiran per guru (indeks 0,1,2) per hari kerja (indeks 0-4)
        // Format: status absensi untuk setiap sesi yang cocok di hari tersebut
        $attendancePatterns = [
            0 => ['hadir', 'hadir', 'hadir', 'izin', 'hadir'],   // Ahmad Fauzi — 1 hari izin
            1 => ['hadir', 'sakit', 'hadir', 'hadir', 'hadir'],  // Siti Aisyah — 1 hari sakit
            2 => ['hadir', 'hadir', 'alpa',  'hadir', 'hadir'],  // Budi Santoso — 1 hari alpa
        ];

        foreach ($teachers as $tIdx => $teacher) {
            $pattern = $attendancePatterns[$tIdx] ?? array_fill(0, 5, 'hadir');

            foreach ($workDays as $dayIdx => $workDay) {
                $dateKey = $workDay->format('Y-m-d');
                $dayName = $this->indonesianDayName($workDay);
                $scheduleId = $scheduleIds[$teacher['unit_id']][$dateKey] ?? null;

                if (! $scheduleId) continue;

                $statusForDay = $pattern[$dayIdx] ?? 'hadir';

                // Cek duplikasi: skip jika record sudah ada
                $exists = DB::table('attendances')
                    ->where('employee_id', $teacher['employee_id'])
                    ->where('schedule_id', $scheduleId)
                    ->whereNull('teacher_subject_unit_id')
                    ->exists();

                if ($exists) continue;

                $checkedInAt  = $statusForDay === 'hadir'
                    ? Carbon::parse($dateKey.' 07:30:00')
                    : null;
                $checkedOutAt = $statusForDay === 'hadir'
                    ? Carbon::parse($dateKey.' 15:00:00')
                    : null;

                $notesMap = [
                    'hadir' => 'Hadir tepat waktu.',
                    'izin'  => 'Izin keperluan keluarga.',
                    'sakit' => 'Sakit, dilampirkan surat dokter.',
                    'alpa'  => 'Tidak hadir tanpa keterangan.',
                ];

                DB::table('attendances')->insert([
                    'employee_id'            => $teacher['employee_id'],
                    'schedule_id'            => $scheduleId,
                    'teacher_subject_unit_id'=> null,
                    'jadwal_id'              => null,
                    'checked_in_at'          => $checkedInAt,
                    'checked_out_at'         => $checkedOutAt,
                    'latitude'               => $statusForDay === 'hadir' ? -6.9147440 : null,
                    'longitude'              => $statusForDay === 'hadir' ? 107.6098100 : null,
                    'status'                 => $statusForDay,
                    'notes'                  => $notesMap[$statusForDay],
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ]);
            }
        }
    }

    /**
     * Seed per-session (teacher_subject_unit) attendances for current week past days.
     * This demonstrates the Hadir/Tidak Terpenuhi/Izin labels on the schedule cards.
     */
    private function seedCurrentWeekSessionAttendances(
        array $teachers,
        array $scheduleIds,
        Carbon $today,
        Carbon $now
    ): void {
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);

        // Only seed for days that have already passed this week
        for ($date = $weekStart->copy(); $date->lt($today); $date->addDay()) {
            if ($date->dayOfWeekIso === 7) continue; // skip Sunday

            $dateKey = $date->format('Y-m-d');
            $dayName = $this->indonesianDayName($date);

            foreach ($teachers as $tIdx => $teacher) {
                $scheduleId = $scheduleIds[$teacher['unit_id']][$dateKey] ?? null;
                if (! $scheduleId) continue;

                foreach ($teacher['sessions'] as $session) {
                    if ($session['day_name'] !== $dayName) continue;

                    // Check if session attendance already exists
                    $exists = DB::table('attendances')
                        ->where('employee_id', $teacher['employee_id'])
                        ->where('schedule_id', $scheduleId)
                        ->where('jadwal_id', $session['jadwal_id'])
                        ->exists();

                    if ($exists) continue;

                    // Budi Santoso (idx 2): Rabu sesi ke-2 = izin disetujui, Kamis sesi ke-1 = tidak hadir
                    // Other teachers: semua hadir
                    $status = 'hadir';
                    $isApproved = true;
                    $notes = 'Hadir sesi mengajar.';

                    if ($tIdx === 2 && $dayName === 'Rabu' && $session['subject'] === 'Sejarah Kebudayaan Islam (SKI)') {
                        $status = 'izin';
                        $isApproved = true;
                        $notes = 'Ada keperluan rapat mendadak.';
                    } elseif ($tIdx === 2 && $dayName === 'Kamis' && $session['subject'] === 'Sejarah Kebudayaan Islam (SKI)') {
                        // Skip — demonstrates "Tidak Terpenuhi" (no record)
                        continue;
                    }

                    DB::table('attendances')->insert([
                        'employee_id'             => $teacher['employee_id'],
                        'schedule_id'             => $scheduleId,
                        'teacher_subject_unit_id' => $session['id'],
                        'jadwal_id'               => $session['jadwal_id'],
                        'checked_in_at'           => $status === 'hadir' ? Carbon::parse($dateKey.' '.$session['start_time']) : Carbon::parse($dateKey.' '.$session['start_time']),
                        'status'                  => $status,
                        'notes'                   => $notes,
                        'is_approved'             => $isApproved,
                        'approved_by'             => $status === 'izin' ? null : null,
                        'approved_at'             => null,
                        'created_at'              => $now,
                        'updated_at'              => $now,
                    ]);
                }
            }
        }
    }
}
