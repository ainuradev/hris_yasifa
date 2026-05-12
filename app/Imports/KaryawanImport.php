<?php

namespace App\Imports;

use App\Models\Employee;
use App\Services\EmployeeImportService;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class KaryawanImport implements SkipsEmptyRows, ToModel, WithHeadingRow, WithValidation
{
    public function __construct(
        private readonly ?int $unitId = null,
        private readonly string $role = 'karyawan',
        private readonly string $status = 'aktif',
    ) {}

    public function model(array $row): ?Employee
    {
        $service = app(EmployeeImportService::class);
        $mapping = collect(array_keys($row))
            ->mapWithKeys(fn (string $heading) => [$heading => $heading])
            ->all();

        $data = $service->cleanRow($row, $mapping);

        if (Employee::where('nik', $data['nik'] ?? null)->orWhere('email', $data['email'] ?? null)->exists()) {
            return null;
        }

        $data['unit_id'] = $this->unitId ?? $data['unit_id'] ?? null;
        $data['password'] = Hash::make($service->generatePassword($data['date_of_birth'] ?? null));
        $data['must_change_password'] = true;
        $data['type'] = $data['type'] ?? 'guru';
        $data['role'] = $this->role;
        $data['status'] = $this->status;

        return new Employee($data);
    }

    public function rules(): array
    {
        return [
            '*.name' => ['required', 'string', 'max:255'],
            '*.nik' => ['required', 'string', 'max:255'],
            '*.email' => ['required', 'email', 'max:255'],
        ];
    }
}
