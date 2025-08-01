<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\AdminRole;

class AdminRoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        // Super admin can access everything
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Convert string role to enum
        $requiredRole = AdminRole::from($role);

        if (!$admin->hasRole($requiredRole)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
} 