<?php

namespace App\Http\Middleware;

use App\Enums\EmployeeRole;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $currentRole = $user->role instanceof EmployeeRole
            ? $user->role->value
            : (string) $user->role;

        if (! in_array($currentRole, $roles, true)) {
            return $this->redirectToDashboard($currentRole);
        }

        return $next($request);
    }

    protected function redirectToDashboard(string $role): RedirectResponse
    {
        return match ($role) {
            EmployeeRole::AdminPusat->value, EmployeeRole::AdminUnit->value => redirect()->route('admin.dashboard'),
            EmployeeRole::Karyawan->value => redirect()->route('karyawan.dashboard'),
            default => redirect()->route('login'),
        };
    }
}
