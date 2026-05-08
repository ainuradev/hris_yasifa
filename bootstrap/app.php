<?php

use App\Enums\EmployeeRole;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('login'));

        $middleware->redirectUsersTo(function (Request $request): string {
            $user = $request->user();

            if (! $user) {
                return route('login');
            }

            if ($user->must_change_password) {
                return $user->isAdmin()
                    ? route('admin.profile.edit')
                    : route('karyawan.profile.edit');
            }

            $role = $user->role instanceof EmployeeRole
                ? $user->role
                : EmployeeRole::from((string) $user->role);

            return match ($role) {
                EmployeeRole::AdminPusat, EmployeeRole::AdminUnit => route('admin.dashboard'),
                EmployeeRole::Karyawan => route('karyawan.dashboard'),
            };
        });

        $middleware->alias([
            'role' => EnsureRole::class,
            'password.changed' => EnsurePasswordChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        // Auto-delete expired announcements daily at midnight
        $schedule->command('model:prune', ['--model' => \App\Models\Announcement::class])->daily();
    })
    ->create();
