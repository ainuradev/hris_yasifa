<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $identifier = $request->identifier;
        
        // Cari karyawan berdasarkan NIK, NUPTK, atau Email
        $employee = Employee::where('nik', $identifier)
            ->orWhere('nuptk', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (!$employee) {
            return back()->withInput()->with('error', 'Data tidak ditemukan. Silakan periksa kembali NIK / NUPTK / Email Anda, atau hubungi Admin.');
        }

        if (!$employee->email) {
            return back()->withInput()->with('error', 'Akun Anda tidak memiliki email yang terdaftar. Silakan hubungi Admin untuk reset password manual.');
        }

        // Generate 6 digit OTP
        $otp = (string) random_int(100000, 999999);
        
        // Simpan OTP di Cache selama 10 menit
        $cacheKey = 'otp_reset_' . $employee->id;
        Cache::put($cacheKey, $otp, now()->addMinutes(10));

        // Kirim email
        try {
            Mail::to($employee->email)->send(new SendOtpMail($otp, $employee->name));
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal mengirim email OTP. Pastikan konfigurasi SMTP sudah benar.');
        }

        // Simpan info ke session untuk form verifikasi
        session()->put('otp_employee_id', $employee->id);
        session()->put('otp_email', $this->maskEmail($employee->email));

        return redirect()->route('password.otp.verify')->with('success', 'Kode OTP berhasil dikirim!');
    }

    public function showVerifyForm()
    {
        if (!session()->has('otp_employee_id')) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);

        $employeeId = session('otp_employee_id');
        if (!$employeeId) {
            return redirect()->route('password.request')->with('error', 'Sesi telah berakhir. Silakan ulangi.');
        }

        $cacheKey = 'otp_reset_' . $employeeId;
        $savedOtp = Cache::get($cacheKey);

        if (!$savedOtp || $savedOtp !== $request->otp) {
            return back()->with('error', 'Kode OTP salah atau sudah kedaluwarsa.');
        }

        // Jika benar, set status validasi OTP berhasil di session
        session()->put('otp_verified', true);
        Cache::forget($cacheKey); // Hapus OTP setelah digunakan

        return redirect()->route('password.reset');
    }

    public function resendOtp(Request $request)
    {
        $employeeId = session('otp_employee_id');
        if (!$employeeId) {
            return redirect()->route('password.request')->with('error', 'Sesi telah berakhir. Silakan ulangi.');
        }

        $employee = Employee::find($employeeId);
        if (!$employee || !$employee->email) {
            return redirect()->route('password.request')->with('error', 'Terjadi kesalahan sistem.');
        }

        $otp = (string) random_int(100000, 999999);
        $cacheKey = 'otp_reset_' . $employee->id;
        Cache::put($cacheKey, $otp, now()->addMinutes(10));

        try {
            Mail::to($employee->email)->send(new SendOtpMail($otp, $employee->name));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim ulang email OTP.');
        }

        return back()->with('success', 'Kode OTP baru telah dikirimkan ke email Anda.');
    }

    public function showResetForm()
    {
        if (!session('otp_verified') || !session('otp_employee_id')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        if (!session('otp_verified') || !session('otp_employee_id')) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $employee = Employee::find(session('otp_employee_id'));
        
        if ($employee) {
            $employee->password = Hash::make($request->password);
            $employee->must_change_password = false;
            $employee->save();
        }

        // Bersihkan session
        session()->forget(['otp_employee_id', 'otp_email', 'otp_verified']);

        return redirect()->route('login')->with('success', 'Password berhasil diubah! Silakan login dengan password baru Anda.');
    }

    private function maskEmail($email)
    {
        $parts = explode("@", $email);
        $name = $parts[0];
        $domain = $parts[1];
        
        $maskedName = substr($name, 0, 2) . str_repeat("*", max(0, strlen($name) - 2));
        
        return $maskedName . "@" . $domain;
    }
}
