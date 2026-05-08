<?php

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromView, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(
        private readonly ?int $unitId,
        private readonly ?string $type,
        private readonly ?string $status,
    ) {}

    public function view(): View
    {
        $employees = Employee::query()
            ->with(['unit', 'teacherDetail', 'nonTeacherDetail'])
            ->when($this->unitId, fn($q) => $q->where('unit_id', $this->unitId))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->whereIn('role', ['karyawan', 'admin_unit'])
            ->orderBy('unit_id')
            ->orderBy('name')
            ->get();

        return view('exports.employees', [
            'employees' => $employees,
            'exportedAt' => now()->translatedFormat('d F Y, H:i'),
        ]);
    }

    public function title(): string
    {
        return 'Data Karyawan';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a2744']],
            ],
        ];
    }
}
