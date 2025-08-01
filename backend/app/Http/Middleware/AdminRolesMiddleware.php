<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\AdminRole;

class AdminRolesMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        // Super admin can access everything
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Convert string roles to enum
        $requiredRoles = array_map(fn($role) => AdminRole::from($role), $roles);

        if (!$admin->hasAnyRole($requiredRoles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
} 