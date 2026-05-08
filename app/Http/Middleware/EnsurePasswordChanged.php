<?php

namespace App\Http\Middleware;

use App\Enums\EmployeeRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $employee = $request->user();

        if (! $employee->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('admin.profile.*', 'karyawan.profile.*', 'logout')) {
            return $next($request);
        }

        $route = $employee->isAdmin()
            ? 'admin.profile.edit'
            : 'karyawan.profile.edit';

        return redirect()
            ->route($route)
            ->with('error', 'Silakan ganti password bawaan terlebih dahulu sebelum melanjutkan.');
    }
}
