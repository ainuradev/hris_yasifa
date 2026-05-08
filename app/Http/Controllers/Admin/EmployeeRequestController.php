<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeeRequestStatus;
use App\Enums\EmployeeRole;
use App\Enums\EmployeeType;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeRequest;
use App\Models\SalaryRate;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class EmployeeRequestController extends Controller
{
    public function index(Request $request): View
    {
        $admin = $request->user();

        $employeeRequests = EmployeeRequest::with(['unit', 'requester', 'approver', 'salaryRate'])
            ->when(! $admin->isAdminPusat(), fn ($query) => $query->where('unit_id', $admin->unit_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->value()))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $units = $admin->isAdminPusat()
            ? Unit::orderBy('name')->get()
            : Unit::whereKey($admin->unit_id)->get();

        $salaryRates = SalaryRate::orderBy('jabatan')
            ->get()
            ->groupBy(fn (SalaryRate $salaryRate) => $salaryRate->type->value);

        return view('admin.pengajuan-karyawan.index', compact('employeeRequests', 'units', 'salaryRates'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->isAdminUnit(), 403);

        $unit = $request->user()->unit;
        $salaryRates = SalaryRate::orderBy('jabatan')
            ->get()
            ->groupBy(fn (SalaryRate $salaryRate) => $salaryRate->type->value);

        return view('admin.pengajuan-karyawan.create', compact('unit', 'salaryRates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $admin = $request->user();
        abort_unless($admin->isAdminUnit(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => [
                'required',
                'string',
                'max:255',
                'unique:employees,nik',
                'unique:employee_requests,nik',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:employees,email',
                'unique:employee_requests,email',
            ],
            'date_of_birth' => ['required', 'date'],
            'type' => ['required', Rule::in([EmployeeType::Guru->value, EmployeeType::NonGuru->value])],
            'employment_status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'contract_end_date' => ['nullable', 'date'],
            'jabatan' => ['required', 'string', 'max:255'],
            'salary_rate_id' => ['required', 'exists:salary_rates,id'],
            'approval_document' => ['required', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(4096)],
            'approval_notes' => ['nullable', 'string'],
        ]);

        $documentPath = $request->file('approval_document')->store('employee-requests', 'public');

        EmployeeRequest::create([
            'unit_id' => $admin->unit_id,
            'requested_by' => $admin->id,
            'name' => $validated['name'],
            'nik' => $validated['nik'],
            'email' => $validated['email'],
            'date_of_birth' => $validated['date_of_birth'],
            'type' => $validated['type'],
            'status' => EmployeeRequestStatus::Pending,
            'employment_status' => $validated['employment_status'],
            'contract_end_date' => $validated['contract_end_date'] ?? null,
            'jabatan' => $validated['jabatan'],
            'salary_rate_id' => $validated['salary_rate_id'],
            'approval_document_path' => $documentPath,
            'approval_notes' => $validated['approval_notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.employee-requests.index')
            ->with('success', 'Pengajuan karyawan berhasil dikirim dan menunggu persetujuan admin pusat.');
    }

    public function approve(Request $request, EmployeeRequest $employeeRequest): RedirectResponse
    {
        $admin = $request->user();
        abort_unless($admin->isAdminPusat(), 403);
        abort_if($employeeRequest->status !== EmployeeRequestStatus::Pending, 422, 'Pengajuan ini sudah diproses.');

        DB::transaction(function () use ($employeeRequest, $admin): void {
            $defaultPassword = Carbon::parse($employeeRequest->date_of_birth)->format('dmY');

            $employee = new Employee();
            $employee->fill([
                'unit_id' => $employeeRequest->unit_id,
                'name' => $employeeRequest->name,
                'nik' => $employeeRequest->nik,
                'email' => $employeeRequest->email,
                'type' => $employeeRequest->type,
                'role' => EmployeeRole::Karyawan,
                'status' => $employeeRequest->employment_status,
                'contract_end_date' => $employeeRequest->contract_end_date,
                'must_change_password' => true,
            ]);
            $employee->password = Hash::make($defaultPassword);
            $employee->save();

            if ($employeeRequest->type === EmployeeType::Guru) {
                $employee->teacherDetail()->create([
                    'salary_rate_id' => $employeeRequest->salary_rate_id,
                    'jabatan' => $employeeRequest->jabatan,
                ]);
            } else {
                $employee->nonTeacherDetail()->create([
                    'salary_rate_id' => $employeeRequest->salary_rate_id,
                    'jabatan' => $employeeRequest->jabatan,
                ]);
            }

            $employeeRequest->update([
                'status' => EmployeeRequestStatus::Approved,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);
        });

        return back()->with('success', 'Pengajuan karyawan disetujui dan data karyawan sudah dibuat.');
    }

    public function reject(Request $request, EmployeeRequest $employeeRequest): RedirectResponse
    {
        $admin = $request->user();
        abort_unless($admin->isAdminPusat(), 403);
        abort_if($employeeRequest->status !== EmployeeRequestStatus::Pending, 422, 'Pengajuan ini sudah diproses.');

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string'],
        ]);

        $employeeRequest->update([
            'status' => EmployeeRequestStatus::Rejected,
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'Pengajuan karyawan ditolak.');
    }
}
