<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\UserPresenceService;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    protected $presenceService;

    public function __construct(UserPresenceService $presenceService)
    {
        $this->presenceService = $presenceService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track activity for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            
            // Don't track activity for presence-related endpoints to avoid infinite loops
            if (!$request->is('api/presence/*')) {
                try {
                    $this->presenceService->markUserOnline($user);
                } catch (\Exception $e) {
                    // Log error but don't break the request
                    \Log::error('Failed to track user activity: ' . $e->getMessage());
                }
            }
        }

        return $response;
    }
}
