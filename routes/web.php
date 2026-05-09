<?php

use App\Http\Controllers\Admin\AbsensiController as AdminAbsensiController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmployeeController as AdminEmployeeController;

use App\Http\Controllers\Admin\JadwalGuruController as AdminJadwalGuruController;
use App\Http\Controllers\Admin\PenggajianController as AdminPenggajianController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Karyawan\AbsensiController as KaryawanAbsensiController;
use App\Http\Controllers\Karyawan\CutiController as KaryawanCutiController;
use App\Http\Controllers\Karyawan\DashboardController as KaryawanDashboardController;
use App\Http\Controllers\Karyawan\GajiController as KaryawanGajiController;
use App\Http\Controllers\Karyawan\JadwalController as KaryawanJadwalController;
use App\Http\Controllers\Karyawan\PengumumanController as KaryawanPengumumanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/lupa-password', [PasswordResetController::class, 'showRequestForm'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetController::class, 'sendOtp'])->name('password.otp.send');
    Route::get('/lupa-password/verifikasi', [PasswordResetController::class, 'showVerifyForm'])->name('password.otp.verify');
    Route::post('/lupa-password/verifikasi', [PasswordResetController::class, 'verifyOtp'])->name('password.otp.submit');
    Route::post('/lupa-password/resend', [PasswordResetController::class, 'resendOtp'])->name('password.otp.resend');
    Route::get('/lupa-password/reset', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/lupa-password/reset', [PasswordResetController::class, 'resetPassword'])->name('password.reset.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin_pusat,admin_unit'])->group(function (): void {
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('password.changed')->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('subjects', \App\Http\Controllers\Admin\SubjectController::class)->except(['create', 'show', 'edit']);

        Route::get('/karyawan', [AdminEmployeeController::class, 'index'])->name('karyawan.index');
        Route::get('/karyawan/export', [AdminEmployeeController::class, 'export'])->name('karyawan.export');
        Route::get('/karyawan/import', \App\Livewire\Admin\Karyawan\Import::class)->name('karyawan.import');
        Route::get('/karyawan/create', [AdminEmployeeController::class, 'create'])->name('karyawan.create');
        Route::post('/karyawan', [AdminEmployeeController::class, 'store'])->name('karyawan.store');
        Route::get('/karyawan/{employee}', [AdminEmployeeController::class, 'show'])->name('karyawan.show');
        Route::get('/karyawan/{employee}/edit', [AdminEmployeeController::class, 'edit'])->name('karyawan.edit');
        Route::put('/karyawan/{employee}', [AdminEmployeeController::class, 'update'])->name('karyawan.update');
        Route::delete('/karyawan/{employee}', [AdminEmployeeController::class, 'destroy'])->name('karyawan.destroy');
        Route::post('/karyawan/{employee}/reset-password', [AdminEmployeeController::class, 'resetPassword'])->name('karyawan.reset-password');
        Route::post('/karyawan/{employee}/salary-components', [AdminEmployeeController::class, 'storeSalaryComponent'])->name('karyawan.salary-components.store');
        Route::put('/karyawan/{employee}/salary-components/{component}', [AdminEmployeeController::class, 'updateSalaryComponent'])->name('karyawan.salary-components.update');
        Route::delete('/karyawan/{employee}/salary-components/{component}', [AdminEmployeeController::class, 'destroySalaryComponent'])->name('karyawan.salary-components.destroy');


        // Unified Rombel Management (Gabungan Jadwal Guru & Kelas)
        Route::resource('rombel', \App\Http\Controllers\Admin\JadwalKelasController::class)->parameters([
            'rombel' => 'jadwal_kela'
        ]);
        Route::post('/rombel/{jadwal_kela}/slot', [\App\Http\Controllers\Admin\JadwalKelasController::class, 'saveSlot'])->name('rombel.slot.save');
        Route::get('/rombel/{jadwal_kela}/teachers', [\App\Http\Controllers\Admin\JadwalKelasController::class, 'teachersBySubject'])->name('rombel.teachers');
        Route::get('/rombel-guru/{teacherDetail}', [\App\Http\Controllers\Admin\JadwalGuruController::class, 'show'])->name('rombel.guru.show');
        Route::post('/rombel-guru/{teacherDetail}', [\App\Http\Controllers\Admin\JadwalGuruController::class, 'save'])->name('rombel.guru.save');
        Route::post('/rombel-guru/{teacherDetail}/slot', [\App\Http\Controllers\Admin\JadwalGuruController::class, 'saveSlot'])->name('rombel.guru.slot.save');

        Route::resource('salary-components', \App\Http\Controllers\Admin\SalaryComponentController::class)->except(['show']);
        Route::resource('salary-rates', \App\Http\Controllers\Admin\SalaryRateController::class)->except(['show', 'create', 'edit']);

        Route::get('/absensi', [AdminAbsensiController::class, 'index'])->name('absensi.index');
        Route::get('/absensi/export', [AdminAbsensiController::class, 'export'])->name('absensi.export');
        Route::get('/absensi/{employee}', [AdminAbsensiController::class, 'show'])->name('absensi.show');
        Route::patch('/absensi/leave/{leaveRequest}/approve', [AdminAbsensiController::class, 'approveLeave'])->name('absensi.approve');
        Route::patch('/absensi/leave/{leaveRequest}/reject', [AdminAbsensiController::class, 'rejectLeave'])->name('absensi.reject');
        
        // Per-session approval routes
        Route::patch('/absensi/session/{attendance}/approve', [AdminAbsensiController::class, 'approveSessionSesi'])->name('absensi.session.approve');
        Route::patch('/absensi/session/{attendance}/reject', [AdminAbsensiController::class, 'rejectSessionSesi'])->name('absensi.session.reject');

        Route::get('/penggajian', [AdminPenggajianController::class, 'index'])->name('penggajian.index');
        Route::get('/penggajian/generate', [AdminPenggajianController::class, 'generateForm'])->name('penggajian.generate.form');
        Route::post('/penggajian/generate', [AdminPenggajianController::class, 'generate'])->name('penggajian.generate.store');
        Route::get('/penggajian/{payroll}', [AdminPenggajianController::class, 'show'])->name('penggajian.show');
        Route::patch('/penggajian/{payroll}/finalize', [AdminPenggajianController::class, 'finalize'])->name('penggajian.finalize');
        Route::patch('/penggajian/{payroll}/paid', [AdminPenggajianController::class, 'markPaid'])->name('penggajian.markPaid');

        Route::get('/pengumuman', [AdminAnnouncementController::class, 'index'])->name('pengumuman.index');
        Route::post('/pengumuman', [AdminAnnouncementController::class, 'store'])->name('pengumuman.store');
        Route::get('/pengumuman/{pengumuman}/edit', [AdminAnnouncementController::class, 'edit'])->name('pengumuman.edit');
        Route::put('/pengumuman/{pengumuman}', [AdminAnnouncementController::class, 'update'])->name('pengumuman.update');
        Route::delete('/pengumuman/{pengumuman}', [AdminAnnouncementController::class, 'destroy'])->name('pengumuman.destroy');

        Route::resource('holidays', \App\Http\Controllers\Admin\HolidayController::class)->only(['index', 'store', 'destroy']);

        Route::get('/approvals', [\App\Http\Controllers\Admin\ApprovalController::class, 'index'])->name('approvals.index');
        Route::patch('/approvals/permission/{permission}/approve', [\App\Http\Controllers\Admin\ApprovalController::class, 'approvePermission'])->name('approvals.permission.approve');
        Route::patch('/approvals/permission/{permission}/reject', [\App\Http\Controllers\Admin\ApprovalController::class, 'rejectPermission'])->name('approvals.permission.reject');
        Route::patch('/approvals/correction/{correction}/approve', [\App\Http\Controllers\Admin\ApprovalController::class, 'approveCorrection'])->name('approvals.correction.approve');
        Route::patch('/approvals/correction/{correction}/reject', [\App\Http\Controllers\Admin\ApprovalController::class, 'rejectCorrection'])->name('approvals.correction.reject');

        Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});

Route::prefix('karyawan')->name('karyawan.')->middleware(['auth', 'role:karyawan'])->group(function (): void {
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('password.changed')->group(function (): void {
        Route::get('/dashboard', [KaryawanDashboardController::class, 'index'])->name('dashboard');

        Route::get('/absensi', [KaryawanAbsensiController::class, 'index'])->name('absensi.index');
        Route::post('/absensi', [KaryawanAbsensiController::class, 'store'])->name('absensi.store');
        Route::post('/absensi/koreksi', [KaryawanAbsensiController::class, 'koreksi'])->name('absensi.koreksi');

        Route::get('/jadwal', [KaryawanJadwalController::class, 'index'])->name('jadwal.index');
        Route::post('/jadwal/sesi/{teacherSubjectUnit}/izin', [KaryawanJadwalController::class, 'izinSesi'])->name('jadwal.sesi.izin');

        Route::get('/gaji', [KaryawanGajiController::class, 'index'])->name('gaji.index');
        Route::get('/gaji/{payroll}', [KaryawanGajiController::class, 'show'])->name('gaji.show');

        Route::get('/cuti', [KaryawanCutiController::class, 'index'])->name('cuti.index');
        Route::post('/cuti', [KaryawanCutiController::class, 'store'])->name('cuti.store');

        Route::get('/pengumuman', [KaryawanPengumumanController::class, 'index'])->name('pengumuman.index');
    });
});
