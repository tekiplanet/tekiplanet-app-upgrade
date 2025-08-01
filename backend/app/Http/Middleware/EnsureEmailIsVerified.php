<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->email_verified_at) {
            return response()->json([
                'message' => 'Email verification required',
                'requires_verification' => true,
                'email' => $request->user()->email
            ], 403);
        }

        return $next($request);
    }
} 