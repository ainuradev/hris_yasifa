<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $unitIds = $this->seedUnits();
            $this->seedAdmins($unitIds);
        });

        $this->command?->info('Production seed selesai: 3 unit dan 4 akun admin tersedia.');
    }

    private function seedUnits(): array
    {
        $unitIds = [];
        $now = now();

        foreach ([
            'MI' => ['name' => 'MI Sirojul Falah', 'kepala_unit' => null],
            'MTs' => ['name' => 'MTs Sirojul Falah', 'kepala_unit' => null],
            'MA' => ['name' => 'MA Sirojul Falah', 'kepala_unit' => null],
        ] as $jenjang => $unit) {
            $unitId = DB::table('units')
                ->where('jenjang', $jenjang)
                ->value('id');

            if ($unitId) {
                DB::table('units')
                    ->where('id', $unitId)
                    ->update([
                        'name' => $unit['name'],
                        'kepala_unit' => $unit['kepala_unit'],
                        'updated_at' => $now,
                    ]);
            } else {
                $unitId = DB::table('units')->insertGetId([
                    'jenjang' => $jenjang,
                    'name' => $unit['name'],
                    'kepala_unit' => $unit['kepala_unit'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $unitIds[$jenjang] = (int) $unitId;
        }

        return $unitIds;
    }

    private function seedAdmins(array $unitIds): void
    {
        $defaultPassword = (string) config('hris.seeders.admin_password', 'admin123');

        $this->upsertAdmin([
            'unit_id' => null,
            'name' => 'Administrator Pusat',
            'nik' => 'ADMIN0001',
            'email' => 'admin@sirojulfalah.test',
            'role' => 'admin_pusat',
        ], $defaultPassword);

        foreach ([
            ['unit_id' => $unitIds['MI'], 'name' => 'Admin MI Sirojul Falah', 'nik' => 'ADM001', 'email' => 'admin-mi@sirojulfalah.test'],
            ['unit_id' => $unitIds['MTs'], 'name' => 'Admin MTs Sirojul Falah', 'nik' => 'ADM002', 'email' => 'admin-mts@sirojulfalah.test'],
            ['unit_id' => $unitIds['MA'], 'name' => 'Admin MA Sirojul Falah', 'nik' => 'ADM003', 'email' => 'admin-ma@sirojulfalah.test'],
        ] as $admin) {
            $this->upsertAdmin([
                ...$admin,
                'role' => 'admin_unit',
            ], $defaultPassword);
        }
    }

    private function upsertAdmin(array $admin, string $defaultPassword): void
    {
        $now = now();
        $employeeId = DB::table('employees')
            ->where('email', $admin['email'])
            ->value('id');

        $payload = [
            'unit_id' => $admin['unit_id'],
            'name' => $admin['name'],
            'nik' => $admin['nik'],
            'email' => $admin['email'],
            'type' => 'non_guru',
            'role' => $admin['role'],
            'status' => 'aktif',
            'status_kepegawaian' => 'PTY',
            'agama' => 'Islam',
            'updated_at' => $now,
        ];

        if ($employeeId) {
            DB::table('employees')
                ->where('id', $employeeId)
                ->update($payload);

            return;
        }

        DB::table('employees')->insert([
            ...$payload,
            'password' => Hash::make($defaultPassword),
            'must_change_password' => true,
            'created_at' => $now,
        ]);
    }
}
