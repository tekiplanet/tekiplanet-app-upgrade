<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserPresenceRequest;
use App\Services\UserPresenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserPresenceController extends Controller
{
    protected $presenceService;

    public function __construct(UserPresenceService $presenceService)
    {
        $this->presenceService = $presenceService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Update current user's presence status
     */
    public function updatePresence(UpdateUserPresenceRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $status = $request->validated('status');

            $this->presenceService->updateUserActivity($user, $status);

            return response()->json([
                'success' => true,
                'message' => 'Presence updated successfully',
                'presence' => $this->presenceService->getUserPresence($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update presence',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current user's presence status
     */
    public function getMyPresence(): JsonResponse
    {
        try {
            $user = Auth::user();
            $presence = $this->presenceService->getUserPresence($user);

            return response()->json([
                'success' => true,
                'presence' => $presence,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get presence',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get another user's presence status
     */
    public function getUserPresence(string $userId): JsonResponse
    {
        try {
            $user = \App\Models\User::findOrFail($userId);
            $presence = $this->presenceService->getUserPresence($user);

            return response()->json([
                'success' => true,
                'presence' => $presence,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user presence',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark user as online (heartbeat)
     */
    public function heartbeat(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            Log::info("Heartbeat received from user {$user->id}", [
                'user_id' => $user->id,
                'timestamp' => now()->toISOString()
            ]);
            
            $this->presenceService->markUserOnline($user);
            
            Log::info("Successfully processed heartbeat for user {$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Heartbeat received',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process heartbeat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process heartbeat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get multiple users' presence status
     */
    public function getMultipleUsersPresence(Request $request): JsonResponse
    {
        try {
            $userIds = $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'required|uuid|exists:users,id',
            ])['user_ids'];

            $users = \App\Models\User::whereIn('id', $userIds)->get();

            $presenceData = [];

            foreach ($users as $user) {
                $presenceData[$user->id] = $this->presenceService->getUserPresence($user);
            }

            return response()->json([
                'success' => true,
                'presence' => $presenceData,
            ]);
        } catch (\Exception $e) {
            Log::error('getMultipleUsersPresence error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get users presence',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
