<?php

namespace App\Http\Middleware;

use App\Enums\EmployeeRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $currentRole = $user->role instanceof EmployeeRole
            ? $user->role->value
            : (string) $user->role;

        if (! in_array($currentRole, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
