<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeeType;
use App\Exports\EmployeesExport;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryRate;
use App\Models\SalaryRateHistory;
use App\Models\Unit;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $admin = $request->user();

        $stats = [
            'total_aktif' => Employee::visibleToAdmin($admin)->where('status', 'aktif')->count(),
            'total_guru' => Employee::visibleToAdmin($admin)->where('type', EmployeeType::Guru->value)->count(),
            'total_non_guru' => Employee::visibleToAdmin($admin)->where('type', EmployeeType::NonGuru->value)->count(),
            'kontrak_habis_bulan_ini' => Employee::visibleToAdmin($admin)->whereNotNull('contract_end_date')
                ->whereMonth('contract_end_date', now()->month)
                ->whereYear('contract_end_date', now()->year)
                ->count(),
        ];

        $units = $admin->isAdminPusat()
            ? Unit::orderBy('name')->get()
            : collect();

        return view('admin.karyawan.index', compact('stats', 'units'));
    }

    public function exportExcel(Request $request)
    {
        try {
            $admin = $request->user();

            $unitId = $admin->isAdminPusat()
                ? ($request->filled('unit_id') ? (int) $request->unit_id : null)
                : (int) $admin->unit_id;

            $type   = $request->filled('type')   ? $request->type   : null;
            $status = $request->filled('status') ? $request->status : null;

            $unitName = $unitId ? (Unit::find($unitId)?->name ?? 'Semua') : 'Semua Unit';
            $filename = 'data-karyawan-' . str($unitName)->slug() . '-' . now()->format('Ymd') . '.xlsx';

            return Excel::download(new EmployeesExport($unitId, $type, $status), $filename);
        } catch (Throwable $exception) {
            try {
                Log::error('Gagal export data karyawan.', [
                    'user_id' => $request->user()?->id,
                    'filters' => $request->only(['unit_id', 'type', 'status']),
                    'exception' => $exception,
                ]);
            } catch (Throwable) {
                // Keep the user-facing error available even when storage/logs is not writable.
            }

            return back()->with('error', 'Export Excel gagal. Pastikan folder storage dan bootstrap/cache writable, lalu coba lagi.');
        }
    }

    public function export(Request $request)
    {
        return $this->exportExcel($request);
    }

    public function create(Request $request): View
    {

        $units = Unit::orderBy('name')->get();
        $salaryRates = SalaryRate::orderBy('jabatan')
            ->get()
            ->groupBy(fn (SalaryRate $salaryRate) => $salaryRate->type->value);

        return view('admin.karyawan.create', compact('units', 'salaryRates'));
    }

    public function store(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:255', 'unique:employees,nik'],
            'nuptk' => ['nullable', 'string', 'max:255', 'unique:employees,nuptk', 'required_if:type,' . EmployeeType::Guru->value],
            'email' => ['required', 'email', 'max:255', 'unique:employees,email'],
            'date_of_birth' => ['required', 'date'],
            'type' => ['required', Rule::in([EmployeeType::Guru->value, EmployeeType::NonGuru->value])],
            'role' => ['required', Rule::in(['admin_pusat', 'admin_unit', 'karyawan'])],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'contract_end_date' => ['nullable', 'date'],
            'jabatan' => ['required', 'string', 'max:255'],
            'salary_rate_id' => ['required', 'exists:salary_rates,id'],
        ]);

        if ($request->user()->isAdminUnit()) {
            $validated['unit_id'] = $request->user()->unit_id;
            // Force role to karyawan if Admin Unit creates it, or allow them to create admin_unit? 
            // Usually they only create karyawan.
            $validated['role'] = 'karyawan';
        }

        $defaultPassword = $this->defaultPasswordForRole($validated['date_of_birth'], $validated['role']);

        DB::transaction(function () use ($validated, $defaultPassword): void {
            $employee = new Employee();
            $employee->fill(collect($validated)->except(['jabatan', 'salary_rate_id'])->all());
            $employee->password = Hash::make($defaultPassword);
            $employee->must_change_password = true;
            $employee->save();

            if ($validated['type'] === EmployeeType::Guru->value) {
                $employee->teacherDetail()->create([
                    'salary_rate_id' => $validated['salary_rate_id'],
                    'jabatan' => $validated['jabatan'],
                ]);
            } else {
                $employee->nonTeacherDetail()->create([
                    'salary_rate_id' => $validated['salary_rate_id'],
                    'jabatan' => $validated['jabatan'],
                ]);
            }
        });

        $defaultPasswordMessage = in_array($validated['role'], ['admin_pusat', 'admin_unit'], true)
            ? 'Password default admin adalah admin123'
            : 'Password default menggunakan tanggal lahir format DDMMYYYY';

        session()->flash('success', "Data karyawan berhasil ditambahkan. {$defaultPasswordMessage} dan wajib diganti saat login pertama.");

        return redirect()->route('admin.karyawan.index');
    }

    public function show(Employee $employee): View
    {
        $this->authorizeEmployeeAccess($employee, request()->user());

        $employee->load([
            'unit',
            'teacherDetail.salaryRate',
            'teacherDetail.homeroomClass',
            'teacherDetail.teacherSubjectUnits.subject',
            'teacherDetail.teacherSubjectUnits.unit',
            'teacherDetail.teacherSubjectUnits.class',
            'nonTeacherDetail.salaryRate',
            'attendances' => fn ($query) => $query->with('schedule')->latest('checked_in_at')->limit(10),
            'payrolls' => fn ($query) => $query->latest('year')->latest('month')->limit(5),
            'salaryComponents.salaryComponent'
        ]);

        $admin = request()->user();
        $availableComponents = \App\Models\SalaryComponent::with('unit')
            ->when(!$admin->isAdminPusat(), function ($query) use ($admin) {
                $query->whereNull('unit_id')->orWhere('unit_id', $admin->unit_id);
            })
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('admin.karyawan.show', compact('employee', 'availableComponents'));
    }

    public function edit(Employee $employee): View
    {
        $this->authorizeEmployeeAccess($employee, request()->user());
        $employee->load(['teacherDetail', 'nonTeacherDetail']);
        $units = Unit::orderBy('name')->get();
        $salaryRates = SalaryRate::orderBy('jabatan')
            ->get()
            ->groupBy(fn (SalaryRate $salaryRate) => $salaryRate->type->value);

        return view('admin.karyawan.edit', compact('employee', 'units', 'salaryRates'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorizeEmployeeAccess($employee, $request->user());

        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:255', Rule::unique('employees', 'nik')->ignore($employee->id)],
            'nuptk' => ['nullable', 'string', 'max:255', Rule::unique('employees', 'nuptk')->ignore($employee->id), 'required_if:type,' . EmployeeType::Guru->value],
            'email' => ['required', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($employee->id)],
            'date_of_birth' => ['nullable', 'date'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'type' => ['required', Rule::in([EmployeeType::Guru->value, EmployeeType::NonGuru->value])],
            'role' => ['required', Rule::in(['admin_pusat', 'admin_unit', 'karyawan'])],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'contract_end_date' => ['nullable', 'date'],
            'jabatan' => ['required', 'string', 'max:255'],
            'salary_rate_id' => ['required', 'exists:salary_rates,id'],
        ]);

        DB::transaction(function () use ($validated, $employee): void {
            $previousRateId = $employee->teacherDetail?->salary_rate_id ?? $employee->nonTeacherDetail?->salary_rate_id;

            $employee->fill(collect($validated)->except(['password', 'jabatan', 'salary_rate_id'])->all());

            if (! empty($validated['password'])) {
                $employee->password = Hash::make($validated['password']);
                $employee->must_change_password = true;
            }

            $employee->save();

            if ($validated['type'] === EmployeeType::Guru->value) {
                if ($employee->nonTeacherDetail) {
                    $employee->nonTeacherDetail->delete();
                }

                $detail = $employee->teacherDetail()->updateOrCreate(
                    ['employee_id' => $employee->id],
                    [
                        'salary_rate_id' => $validated['salary_rate_id'],
                        'jabatan' => $validated['jabatan'],
                    ]
                );

                if ($previousRateId && $previousRateId !== $detail->salary_rate_id) {
                    $oldRate = SalaryRate::find($previousRateId);
                    $newRate = SalaryRate::find($detail->salary_rate_id);

                    SalaryRateHistory::create([
                        'salary_rate_id' => $detail->salary_rate_id,
                        'old_rate' => $oldRate?->rate ?? 0,
                        'new_rate' => $newRate?->rate ?? 0,
                        'reason' => 'Perubahan salary rate untuk karyawan: '.$employee->name,
                        'changed_by' => auth()->id(),
                        'changed_at' => now(),
                    ]);
                }
            } else {
                if ($employee->teacherDetail) {
                    $employee->teacherDetail->delete();
                }

                $detail = $employee->nonTeacherDetail()->updateOrCreate(
                    ['employee_id' => $employee->id],
                    [
                        'salary_rate_id' => $validated['salary_rate_id'],
                        'jabatan' => $validated['jabatan'],
                    ]
                );

                if ($previousRateId && $previousRateId !== $detail->salary_rate_id) {
                    $oldRate = SalaryRate::find($previousRateId);
                    $newRate = SalaryRate::find($detail->salary_rate_id);

                    SalaryRateHistory::create([
                        'salary_rate_id' => $detail->salary_rate_id,
                        'old_rate' => $oldRate?->rate ?? 0,
                        'new_rate' => $newRate?->rate ?? 0,
                        'reason' => 'Perubahan salary rate untuk karyawan: '.$employee->name,
                        'changed_by' => auth()->id(),
                        'changed_at' => now(),
                    ]);
                }
            }
        });

        session()->flash('success', ! empty($validated['password'])
            ? 'Data karyawan berhasil diperbarui. Password baru akan wajib diganti saat login berikutnya.'
            : 'Data karyawan berhasil diperbarui.');

        return redirect()->route('admin.karyawan.show', $employee);
    }

    public function storeSalaryComponent(Request $request, Employee $employee)
    {
        abort_unless($request->user()->isAdminPusat() || $request->user()->unit_id === $employee->unit_id, 403);
        
        $validated = $request->validate([
            'salary_component_id' => 'required|exists:salary_components,id',
            'amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        $component = \App\Models\SalaryComponent::find($validated['salary_component_id']);
        if ($validated['amount'] === null) {
            $validated['amount'] = $component->default_amount;
        }

        $employee->salaryComponents()->updateOrCreate(
            ['salary_component_id' => $validated['salary_component_id']],
            [
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
            ]
        );

        app(AuditTrailService::class)->record(
            $request->user(),
            'employee.salary_component.assigned',
            $employee,
            'Komponen gaji ditambahkan ke karyawan.',
            null,
            $validated
        );

        return back()->with('success', 'Komponen gaji berhasil ditambahkan ke karyawan.');
    }

    public function updateSalaryComponent(Request $request, Employee $employee, \App\Models\EmployeeSalaryComponent $component)
    {
        abort_unless($request->user()->isAdminPusat() || $request->user()->unit_id === $employee->unit_id, 403);
        abort_unless($component->employee_id === $employee->id, 403);

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0',
        ]);

        if ($validated['amount'] === null) {
            $validated['amount'] = $component->salaryComponent->default_amount;
        }

        $beforeAmount = $component->amount;

        $component->update([
            'amount' => $validated['amount'],
        ]);

        app(AuditTrailService::class)->record(
            $request->user(),
            'employee.salary_component.updated',
            $employee,
            'Nominal komponen gaji diperbarui.',
            ['amount' => $beforeAmount],
            ['amount' => $validated['amount']]
        );

        return back()->with('success', 'Nominal komponen gaji berhasil diperbarui.');
    }

    public function destroySalaryComponent(Request $request, Employee $employee, \App\Models\EmployeeSalaryComponent $component)
    {
        abort_unless($request->user()->isAdminPusat() || $request->user()->unit_id === $employee->unit_id, 403);
        abort_unless($component->employee_id === $employee->id, 403);

        $before = $component->toArray();
        $component->delete();

        app(AuditTrailService::class)->record(
            $request->user(),
            'employee.salary_component.deleted',
            $employee,
            'Komponen gaji dihapus dari karyawan.',
            $before,
            null
        );

        return back()->with('success', 'Komponen gaji berhasil dihapus dari karyawan.');
    }

    public function resetPassword(Employee $employee, Request $request): RedirectResponse
    {
        $this->authorizeEmployeeAccess($employee, $request->user());

        $employee->update([
            'password' => Hash::make('123456'),
            'must_change_password' => true,
        ]);

        app(AuditTrailService::class)->record(
            $request->user(),
            'employee.password.reset',
            $employee,
            'Password direset oleh admin.',
            null,
            ['default_password' => '123456']
        );

        return back()->with('success', 'Password berhasil direset ke default 123456.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorizeEmployeeAccess($employee, request()->user());

        if ($employee->payrolls()->where('status', 'dibayar')->exists()) {
            session()->flash('error', 'Karyawan tidak bisa dihapus karena memiliki payroll yang sudah dibayar.');

            return redirect()->route('admin.karyawan.index');
        }

        $employee->delete();

        session()->flash('success', 'Data karyawan berhasil dihapus.');

        return redirect()->route('admin.karyawan.index');
    }

    private function defaultPasswordForRole(string $dateOfBirth, string $role): string
    {
        if (in_array($role, ['admin_pusat', 'admin_unit'], true)) {
            return 'admin123';
        }

        return Carbon::parse($dateOfBirth)->format('dmY');
    }

    private function authorizeEmployeeAccess(Employee $employee, Employee $admin): void
    {
        if ($admin->isAdminPusat()) {
            return;
        }

        abort_if($employee->unit_id !== $admin->unit_id, 403);
    }
}
