<?php

namespace App\Http\Controllers\Auth;

use App\Enums\EmployeeRole;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($credentials['login']);
        $password = $credentials['password'];

        $employee = Employee::where('email', $login)
            ->orWhere('nik', $login)
            ->orWhere('nuptk', $login)
            ->first();

        if (! $employee || ! Auth::attempt(['email' => $employee->email, 'password' => $password])) {
            return back()
                ->withErrors([
                    'login' => 'Email/NIK/NUPTK atau password tidak sesuai.',
                ])
                ->onlyInput('login');
        }

        $request->session()->regenerate();

        /** @var Employee $employee */
        $employee = $request->user();

        if ($employee->must_change_password) {
            $route = $employee->isAdmin()
                ? 'admin.profile.edit'
                : 'karyawan.profile.edit';

            return redirect()
                ->route($route)
                ->with('error', 'Password bawaan masih aktif. Silakan ganti password terlebih dahulu.');
        }

        return match ($employee->role) {
            EmployeeRole::AdminPusat, EmployeeRole::AdminUnit => redirect()->route('admin.dashboard'),
            EmployeeRole::Karyawan => redirect()->route('karyawan.dashboard'),
            default => redirect()->route('login'),
        };
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
