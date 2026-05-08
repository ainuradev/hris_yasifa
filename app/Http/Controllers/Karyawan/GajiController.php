<?php

namespace App\Http\Controllers\Karyawan;

use App\Enums\PayrollStatus;
use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Contracts\View\View;

class GajiController extends Controller
{
    public function index(): View
    {
        $payrolls = Payroll::with('employee.unit')
            ->where('employee_id', auth()->id())
            ->whereIn('status', [PayrollStatus::Final->value, PayrollStatus::Dibayar->value])
            ->latest('year')
            ->latest('month')
            ->paginate(20);

        return view('karyawan.gaji.index', compact('payrolls'));
    }

    public function show(Payroll $payroll): View
    {
        if ((int) $payroll->employee_id !== (int) auth()->id()) {
            abort(403);
        }

        $payroll->load('payrollDetails');

        return view('karyawan.gaji.show', compact('payroll'));
    }
}
