<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Hanya memasukkan data master esensial: Unit, Jabatan (Salary Rates), Mapel, Kelas, dan Akun Admin.
     */
    public function run(): void
    {
        $now = now();
        $today = Carbon::today();
        $contractEndDate = $today->copy()->addYears(5)->format('Y-m-d'); // Kontrak default 5 tahun untuk admin

        DB::transaction(function () use ($now, $contractEndDate) {
            $unitIds = $this->seedUnits($now);
            $salaryRateIds = $this->seedSalaryRates($now);
            $subjectIds = $this->seedSubjects();
            $classIds = $this->seedClasses($unitIds, $now);
            
            // Seed Admin Pusat & Admin Unit
            $this->seedAdmins($unitIds, $contractEndDate, $now);
            
            // Masukkan master data komponen gaji bawaan (opsional, jika ada)
            // $this->seedDefaultSalaryComponents($now);
            
            $this->command->info('✅ Master Data & Akun Admin berhasil di-seed. Database siap untuk production!');
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

    private function seedSubjects(): array
    {
        $subjectIds = [];

        foreach ([
            'Pendidikan Agama Islam',
            'Pendidikan Pancasila',
            'Bahasa Indonesia',
            'Matematika',
            'Ilmu Pengetahuan Alam dan Sosial (IPAS)',
            'Ilmu Pengetahuan Alam (IPA)',
            'Ilmu Pengetahuan Sosial (IPS)',
            'Bahasa Inggris',
            'Seni Budaya',
            'Pendidikan Jasmani, Olahraga, dan Kesehatan (PJOK)',
            'Muatan Lokal',
        ] as $subjectName) {
            $subjectIds[$subjectName] = DB::table('subjects')->insertGetId([
                'name' => $subjectName,
            ]);
        }

        return $subjectIds;
    }

    private function seedClasses(array $unitIds, Carbon $now): array
    {
        $classIds = [];
        $classes = [
            ['unit_id' => $unitIds['MI'], 'name' => '4A', 'level' => 4, 'major' => null],
            ['unit_id' => $unitIds['MI'], 'name' => '5A', 'level' => 5, 'major' => null],
            ['unit_id' => $unitIds['MI'], 'name' => '6A', 'level' => 6, 'major' => null],
            ['unit_id' => $unitIds['MTs'], 'name' => '7.1', 'level' => 7, 'major' => null],
            ['unit_id' => $unitIds['MTs'], 'name' => '8.1', 'level' => 8, 'major' => null],
            ['unit_id' => $unitIds['MTs'], 'name' => '9.1', 'level' => 9, 'major' => null],
            ['unit_id' => $unitIds['MA'], 'name' => 'X IPA 1', 'level' => 10, 'major' => 'IPA'],
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

        return $classIds;
    }

    private function seedAdmins(array $unitIds, string $contractEndDate, Carbon $now): int
    {
        $adminBirthDate = '1990-01-10';
        $adminDefaultPassword = 'admin123';

        // Admin Pusat
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

        // Admin Unit
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
                'emergency_contact_phone' => '081234567890',
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
}
