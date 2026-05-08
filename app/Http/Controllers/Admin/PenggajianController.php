<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttendanceStatus;
use App\Enums\EmployeeType;
use App\Enums\PayrollCategory;
use App\Enums\PayrollStatus;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\PayrollHistory;
use App\Models\Attendance;
use App\Models\Unit;
use App\Models\PayrollSnapshot;
use App\Services\AuditTrailService;
use App\Services\PayrollService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PenggajianController extends Controller
{
    public function index(Request $request): View
    {
        $admin = $request->user();
        $selectedUnitId = $admin->isAdminPusat()
            ? ($request->filled('unit_id') ? $request->integer('unit_id') : null)
            : (int) $admin->unit_id;

        $payrollQuery = Payroll::with(['employee.unit'])
            ->whereHas('employee', fn ($employeeQuery) => $employeeQuery->visibleToAdmin($admin))
            ->when($request->filled('month'), fn ($query) => $query->where('month', $request->integer('month')))
            ->when($request->filled('year'), fn ($query) => $query->where('year', $request->integer('year')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->value()))
            ->when($selectedUnitId, function ($query) use ($selectedUnitId): void {
                $query->whereHas('employee', fn ($employeeQuery) => $employeeQuery->where('unit_id', $selectedUnitId));
            });

        $totalNetSalary = (clone $payrollQuery)->sum('net_salary');
        $stats = [
            'total_slip' => (clone $payrollQuery)->count(),
            'draft' => (clone $payrollQuery)->where('status', PayrollStatus::Draft->value)->count(),
            'final' => (clone $payrollQuery)->where('status', PayrollStatus::Final->value)->count(),
            'dibayar' => (clone $payrollQuery)->where('status', PayrollStatus::Dibayar->value)->count(),
            'total_rp' => $totalNetSalary,
        ];
        $payrolls = $payrollQuery->latest('year')->latest('month')->paginate(20)->withQueryString();
        $units = $admin->isAdminPusat()
            ? Unit::orderBy('name')->get()
            : Unit::whereKey($admin->unit_id)->get();

        return view('admin.penggajian.index', compact('payrolls', 'totalNetSalary', 'units', 'stats', 'selectedUnitId'));
    }

    public function generateForm(Request $request): View
    {
        $admin = $request->user();
        $units = $admin->isAdminPusat()
            ? Unit::orderBy('name')->get()
            : Unit::whereKey($admin->unit_id)->get();
        $selectedUnitId = old('unit_id', $admin->isAdminPusat() ? null : $admin->unit_id);

        return view('admin.penggajian.generate', compact('units', 'selectedUnitId'));
    }

    public function generate(Request $request, PayrollService $payrollService, AuditTrailService $auditTrailService): RedirectResponse
    {
        $admin = $request->user();
        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2000'],
            'unit_id' => ['required', 'exists:units,id'],
        ]);

        $unitId = $admin->isAdminPusat() ? (int) $validated['unit_id'] : (int) $admin->unit_id;

        $createdCount = 0;
        $service = $payrollService;
        $auditService = $auditTrailService;

        DB::transaction(function () use ($validated, $unitId, $admin, &$createdCount, $service, $auditService): void {
            $employees = Employee::with([
                'teacherDetail.salaryRate',
                'teacherDetail.teacherSubjectUnits' => fn ($query) => $query->where('unit_id', $unitId)->with('subject'),
                'nonTeacherDetail.salaryRate',
                'salaryComponents.salaryComponent',
            ])
                ->where('unit_id', $unitId)
                ->where('status', 'aktif')
                ->get();

            foreach ($employees as $employee) {
                $exists = Payroll::query()
                    ->where('employee_id', $employee->id)
                    ->where('month', $validated['month'])
                    ->where('year', $validated['year'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $payrollData = $service->buildPayroll(
                    $employee,
                    (int) $validated['month'],
                    (int) $validated['year'],
                    $unitId
                );

                $payroll = Payroll::create([
                    'employee_id' => $employee->id,
                    'month' => $validated['month'],
                    'year' => $validated['year'],
                    'base_salary' => $payrollData['base_salary'],
                    'total_allowance' => $payrollData['total_allowance'],
                    'total_deduction' => $payrollData['total_deduction'],
                    'net_salary' => $payrollData['net_salary'],
                    'status' => PayrollStatus::Draft->value,
                    'created_by' => $admin->id,
                    'updated_by' => null,
                ]);

                foreach ($payrollData['components'] as $component) {
                    PayrollDetail::create([
                        'payroll_id' => $payroll->id,
                        'description' => $component['description'],
                        'amount' => $component['amount'],
                        'category' => $component['category'],
                    ]);
                }

                PayrollHistory::create([
                    'payroll_id' => $payroll->id,
                    'field_changed' => 'status',
                    'old_value' => null,
                    'new_value' => PayrollStatus::Draft->value,
                    'changed_by' => $admin->id,
                    'changed_at' => now(),
                ]);

                PayrollSnapshot::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $employee->id,
                    'unit_id' => $payrollData['snapshot']['unit_id'],
                    'rate_gaji' => $payrollData['snapshot']['rate_gaji'],
                    'verified_jp_total' => $payrollData['snapshot']['verified_jp_total'],
                    'daily_allowance_rate' => $payrollData['snapshot']['daily_allowance_rate'],
                    'daily_allowance_total' => $payrollData['snapshot']['daily_allowance_total'],
                    'payload' => $payrollData['snapshot']['payload'],
                ]);

                $auditService->record(
                    $admin,
                    'payroll.generated',
                    $payroll,
                    'Payroll draft dibuat.',
                    null,
                    [
                        'employee_id' => $employee->id,
                        'period' => sprintf('%02d/%d', $validated['month'], $validated['year']),
                        'net_salary' => $payrollData['net_salary'],
                    ]
                );

                $createdCount++;
            }
        });

        session()->flash('success', "Generate penggajian selesai. {$createdCount} slip berhasil dibuat.");

        return redirect()->route('admin.penggajian.index');
    }

    public function show(Payroll $payroll): View
    {
        $this->authorizePayrollAccess($payroll, request()->user());
        $payroll->load(['employee.unit', 'employee.teacherDetail.salaryRate', 'employee.nonTeacherDetail.salaryRate', 'payrollDetails', 'payrollHistories.changedBy', 'snapshot.unit']);

        return view('admin.penggajian.show', compact('payroll'));
    }

    public function finalize(Payroll $payroll): RedirectResponse
    {
        $this->authorizePayrollAccess($payroll, request()->user());

        if ($payroll->status !== PayrollStatus::Draft) {
            session()->flash('error', 'Hanya payroll draft yang bisa difinalisasi.');

            return back();
        }

        DB::transaction(function () use ($payroll): void {
            $oldStatus = $payroll->status->value;

            $payroll->update([
                'status' => PayrollStatus::Final->value,
                'updated_by' => auth()->id(),
            ]);

            PayrollHistory::create([
                'payroll_id' => $payroll->id,
                'field_changed' => 'status',
                'old_value' => $oldStatus,
                'new_value' => PayrollStatus::Final->value,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);

            app(AuditTrailService::class)->record(
                auth()->user(),
                'payroll.finalized',
                $payroll,
                'Payroll difinalisasi.',
                ['status' => $oldStatus],
                ['status' => PayrollStatus::Final->value]
            );
        });

        session()->flash('success', 'Payroll berhasil difinalisasi.');

        return back();
    }

    public function markPaid(Payroll $payroll): RedirectResponse
    {
        $this->authorizePayrollAccess($payroll, request()->user());

        if ($payroll->status !== PayrollStatus::Final) {
            session()->flash('error', 'Hanya payroll final yang bisa ditandai dibayar.');

            return back();
        }

        DB::transaction(function () use ($payroll): void {
            $oldStatus = $payroll->status->value;

            $payroll->update([
                'status' => PayrollStatus::Dibayar->value,
                'paid_at' => today(),
                'updated_by' => auth()->id(),
            ]);

            PayrollHistory::create([
                'payroll_id' => $payroll->id,
                'field_changed' => 'status',
                'old_value' => $oldStatus,
                'new_value' => PayrollStatus::Dibayar->value,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);

            app(AuditTrailService::class)->record(
                auth()->user(),
                'payroll.paid',
                $payroll,
                'Payroll ditandai dibayar.',
                ['status' => $oldStatus],
                ['status' => PayrollStatus::Dibayar->value, 'paid_at' => today()->toDateString()]
            );
        });

        session()->flash('success', 'Payroll berhasil ditandai dibayar.');

        return back();
    }

    private function authorizePayrollAccess(Payroll $payroll, Employee $admin): void
    {
        if ($admin->isAdminPusat()) {
            return;
        }

        abort_if((int) $payroll->employee?->unit_id !== (int) $admin->unit_id, 403);
    }
}
