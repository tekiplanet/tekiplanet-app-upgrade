<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request)
    {
        // For API requests, return null so a 401 is sent (prevents Route [login] not defined error)
        if ($request->expectsJson()) {
            return null;
        }
        // Existing logic for web/admin
        if ($request->is('admin*')) {
            return route('admin.login');
        }
        return route('login');
    }
}
