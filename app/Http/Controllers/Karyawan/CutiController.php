<?php

namespace App\Http\Controllers\Karyawan;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CutiController extends Controller
{
    public function index(): View
    {
        $employee = auth()->user();

        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->latest('start_date')
            ->paginate(20);

        $usedAnnualLeave = LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type', LeaveType::Tahunan->value)
            ->where('status', LeaveStatus::Disetujui->value)
            ->whereYear('start_date', now()->year)
            ->sum('total_days');

        $remainingAnnualLeave = max(12 - $usedAnnualLeave, 0);

        return view('karyawan.cuti.index', compact('leaveRequests', 'remainingAnnualLeave', 'usedAnnualLeave'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'leave_type' => ['required', Rule::in([
                LeaveType::Tahunan->value,
                LeaveType::Sakit->value,
                LeaveType::Penting->value,
                LeaveType::Melahirkan->value,
            ])],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string'],
        ]);

        $employee = auth()->user();
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        if ($validated['leave_type'] === LeaveType::Tahunan->value) {
            $usedAnnualLeave = LeaveRequest::where('employee_id', $employee->id)
                ->where('leave_type', LeaveType::Tahunan->value)
                ->where('status', LeaveStatus::Disetujui->value)
                ->whereYear('start_date', now()->year)
                ->sum('total_days');

            if ((12 - $usedAnnualLeave) < $totalDays) {
                session()->flash('error', 'Sisa cuti tahunan tidak mencukupi.');

                return back()->withInput();
            }
        }

        $hasOverlap = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', [LeaveStatus::Pending->value, LeaveStatus::Disetujui->value])
            ->where(function ($query) use ($startDate, $endDate): void {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhere(function ($innerQuery) use ($startDate, $endDate): void {
                        $innerQuery->whereDate('start_date', '<=', $startDate->toDateString())
                            ->whereDate('end_date', '>=', $endDate->toDateString());
                    });
            })
            ->exists();

        if ($hasOverlap) {
            session()->flash('error', 'Sudah ada pengajuan cuti yang overlap pada tanggal tersebut.');

            return back()->withInput();
        }

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $validated['leave_type'],
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'status' => LeaveStatus::Pending->value,
        ]);

        session()->flash('success', 'Pengajuan cuti berhasil dikirim.');

        return back();
    }
}
