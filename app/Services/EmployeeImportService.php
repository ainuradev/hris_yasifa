<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeImportService
{
    public function cleanRow(array $row, array $mapping): array
    {
        $cleanData = [];

        foreach ($mapping as $dbField => $excelHeader) {
            if ($excelHeader === '' || $excelHeader === null) continue;

            $value = $row[$excelHeader] ?? null;
            $cleanData[$dbField] = $this->normalizeField($dbField, $value);
        }

        return $cleanData;
    }

    private function normalizeField(string $field, mixed $value): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if (empty($value)) return null;

        return match ($field) {
            'gender' => $this->normalizeGender($value),
            'date_of_birth', 'tmt_pegawai', 'contract_end_date' => $this->normalizeDate($value),
            'type' => $this->normalizeType($value),
            'status_perkawinan' => $this->normalizeMaritalStatus($value),
            'status_kepegawaian' => $this->normalizeEmploymentStatus($value),
            'pendidikan_terakhir' => $this->normalizeEducation($value),
            default => $value,
        };
    }

    private function normalizeGender(string $value): string
    {
        $lower = strtolower($value);
        if (in_array($lower, ['l', 'laki-laki', 'laki laki', 'pria', 'male', 'm'])) {
            return 'laki_laki';
        }
        if (in_array($lower, ['p', 'perempuan', 'wanita', 'female', 'f'])) {
            return 'perempuan';
        }
        return 'laki_laki'; // default
    }

    private function normalizeDate(string $value): ?string
    {
        try {
            // Check if it's an Excel numeric date
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function normalizeType(string $value): string
    {
        $lower = strtolower($value);
        return str_contains($lower, 'non') || str_contains($lower, 'tu') || str_contains($lower, 'satpam') 
               ? 'non_guru' 
               : 'guru';
    }

    private function normalizeMaritalStatus(string $value): string
    {
        $lower = strtolower($value);
        if (str_contains($lower, 'belum')) return 'Belum Kawin';
        if (str_contains($lower, 'cerai mati') || str_contains($lower, 'janda mati') || str_contains($lower, 'duda mati')) return 'Cerai Mati';
        if (str_contains($lower, 'cerai') || str_contains($lower, 'janda') || str_contains($lower, 'duda')) return 'Cerai Hidup';
        if (str_contains($lower, 'kawin') || str_contains($lower, 'nikah')) return 'Kawin';
        return 'Belum Kawin';
    }

    private function normalizeEmploymentStatus(string $value): string
    {
        $lower = strtolower($value);
        if (str_contains($lower, 'pns')) return 'PNS';
        if (str_contains($lower, 'pppk')) return 'PPPK';
        if (str_contains($lower, 'gty') || str_contains($lower, 'tetap yayasan')) return 'GTY';
        if (str_contains($lower, 'pty')) return 'PTY';
        if (str_contains($lower, 'honor')) return 'Honorer';
        return 'Lainnya';
    }

    private function normalizeEducation(string $value): string
    {
        $val = strtoupper($value);
        if (in_array($val, ['SD', 'SMP', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'])) return $val;
        if (str_contains($val, 'SMA') || str_contains($val, 'SMK') || str_contains($val, 'MA')) return 'SMA/Sederajat';
        return 'S1'; // fallback
    }

    public function generatePassword(?string $dateOfBirth): string
    {
        if (!$dateOfBirth) {
            return 'password';
        }

        try {
            return Carbon::parse($dateOfBirth)->format('dmY');
        } catch (\Exception $e) {
            return 'password';
        }
    }
}
