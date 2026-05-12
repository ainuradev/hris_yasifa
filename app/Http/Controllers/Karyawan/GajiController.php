<?php

namespace App\Http\Controllers\Karyawan;

use App\Enums\PayrollStatus;
use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

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
        try {
            if ((int) $payroll->employee_id !== (int) auth()->id()) {
                abort(403);
            }

            $payroll->load(['employee.unit', 'payrollDetails']);

            return view('karyawan.gaji.show', compact('payroll'));
        } catch (Throwable $exception) {
            if ($exception instanceof HttpExceptionInterface) {
                throw $exception;
            }

            try {
                Log::error('Gagal membuka slip gaji karyawan.', [
                    'user_id' => auth()->id(),
                    'payroll_id' => $payroll->id,
                    'exception' => $exception,
                ]);
            } catch (Throwable) {
                // Keep the HTTP error available even when storage/logs is not writable.
            }

            abort(500, 'Slip gaji gagal dibuka. Silakan hubungi admin.');
        }
    }
}
