<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttendanceStatus;
use App\Enums\EmployeeRequestStatus;
use App\Enums\LeaveStatus;
use App\Enums\PayrollStatus;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeRequest;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $admin        = $request->user();
        $today        = today();
        $currentMonth = now()->month;
        $currentYear  = now()->year;

        // ── Stat 1: Total Karyawan Aktif ─────────────────────────────
        $totalEmployees = Employee::visibleToAdmin($admin)
            ->where('status', 'aktif')
            ->count();

        // ── Stat 2: Hadir Hari Ini ────────────────────────────────────
        $totalHadirToday = Attendance::where('status', AttendanceStatus::Hadir->value)
            ->whereHas('employee', fn ($q) => $q->visibleToAdmin($admin))
            ->whereHas('schedule', fn ($q) => $q->whereDate('work_date', $today))
            ->count();

        // ── Stat 3a: Karyawan Cuti (disetujui, aktif hari ini) — ADMIN PUSAT
        $karyawanCutiDisetujui = LeaveRequest::where('status', LeaveStatus::Disetujui->value)
            ->whereHas('employee', fn ($q) => $q->visibleToAdmin($admin))
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->count();

        // ── Stat 3b: Cuti Pending (menunggu persetujuan) — ADMIN UNIT
        $cutiPendingCount = LeaveRequest::where('status', LeaveStatus::Pending->value)
            ->whereHas('employee', fn ($q) => $q->visibleToAdmin($admin))
            ->count();

        // ── Stat 4: Total Payroll Dibayar Bulan Ini ───────────────────
        $paidPayrollTotal = Payroll::where('status', PayrollStatus::Dibayar->value)
            ->whereHas('employee', fn ($q) => $q->visibleToAdmin($admin))
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->sum('net_salary');

        // ── Pengajuan Karyawan Pending ────────────────────────────────
        $pendingEmployeeRequests = EmployeeRequest::when(
                ! $admin->isAdminPusat(),
                fn ($q) => $q->where('unit_id', $admin->unit_id)
            )
            ->where('status', EmployeeRequestStatus::Pending->value)
            ->count();

        // ── Kehadiran per Unit (progress bar) ────────────────────────
        $attendancePerUnit = ($admin->isAdminPusat()
            ? Unit::orderBy('name')->get()
            : Unit::whereKey($admin->unit_id)->get())
            ->map(function (Unit $unit) use ($currentMonth, $currentYear) {
                $totalAttendances = Attendance::whereHas('schedule', function ($q) use ($unit, $currentMonth, $currentYear): void {
                    $q->where('unit_id', $unit->id)
                        ->whereMonth('work_date', $currentMonth)
                        ->whereYear('work_date', $currentYear);
                })->count();

                $hadirCount = Attendance::where('status', AttendanceStatus::Hadir->value)
                    ->whereHas('schedule', function ($q) use ($unit, $currentMonth, $currentYear): void {
                        $q->where('unit_id', $unit->id)
                            ->whereMonth('work_date', $currentMonth)
                            ->whereYear('work_date', $currentYear);
                    })->count();

                return [
                    'unit'       => $unit,
                    'persentase' => $totalAttendances > 0 ? round(($hadirCount / $totalAttendances) * 100, 1) : 0,
                    'hadir'      => $hadirCount,
                    'total'      => $totalAttendances,
                ];
            });

        // ── Pengumuman Terbaru ────────────────────────────────────────
        $latestAnnouncements = Announcement::with(['createdBy', 'unit'])
            ->when(! $admin->isAdminPusat(), function ($q) use ($admin): void {
                $q->where(function ($sq) use ($admin): void {
                    $sq->where('is_global', true)->orWhere('unit_id', $admin->unit_id);
                });
            })
            ->latest()
            ->limit(3)
            ->get();

        // ── Cuti Pending List (panel kanan admin unit) ────────────────
        $cutiPendingList = LeaveRequest::with('employee')
            ->where('status', LeaveStatus::Pending->value)
            ->whereHas('employee', fn ($q) => $q->visibleToAdmin($admin))
            ->latest('start_date')
            ->limit(5)
            ->get();

        // ── Status Kehadiran Hari Ini ─────────────────────────────────
        $todayStatusSummary = [
            'hadir' => Attendance::where('status', AttendanceStatus::Hadir->value)
                ->whereHas('employee', fn ($q) => $q->visibleToAdmin($admin))
                ->whereHas('schedule', fn ($q) => $q->whereDate('work_date', $today))
                ->count(),
            'izin' => Attendance::where('status', AttendanceStatus::Izin->value)
                ->whereHas('employee', fn ($q) => $q->visibleToAdmin($admin))
                ->whereHas('schedule', fn ($q) => $q->whereDate('work_date', $today))
                ->count(),
            'sakit' => Attendance::where('status', AttendanceStatus::Sakit->value)
                ->whereHas('employee', fn ($q) => $q->visibleToAdmin($admin))
                ->whereHas('schedule', fn ($q) => $q->whereDate('work_date', $today))
                ->count(),
            'alpa' => Attendance::where('status', AttendanceStatus::Alpa->value)
                ->whereHas('employee', fn ($q) => $q->visibleToAdmin($admin))
                ->whereHas('schedule', fn ($q) => $q->whereDate('work_date', $today))
                ->count(),
        ];

        return view('admin.dashboard', [
            'isAdminPusat' => $admin->isAdminPusat(),
            'stats' => [
                'total_karyawan_aktif'            => $totalEmployees,
                'hadir_hari_ini'                  => $totalHadirToday,
                'karyawan_cuti_disetujui'         => $karyawanCutiDisetujui,
                'cuti_pending'                    => $cutiPendingCount,
                'pengajuan_karyawan_pending'      => $pendingEmployeeRequests,
                'total_payroll_dibayar_bulan_ini' => $paidPayrollTotal,
                'status_hari_ini'                 => $todayStatusSummary,
                'kehadiran_per_unit'              => $attendancePerUnit,
            ],
            'cutiPending'   => $cutiPendingList,
            'announcements' => $latestAnnouncements,
        ]);
    }
}
