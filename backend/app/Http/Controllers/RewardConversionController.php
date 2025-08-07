<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RewardConversionService;
use Illuminate\Http\JsonResponse;
use Exception;

class RewardConversionController extends Controller
{
    protected $rewardConversionService;

    public function __construct(RewardConversionService $rewardConversionService)
    {
        $this->rewardConversionService = $rewardConversionService;
    }

    /**
     * Initiate a reward conversion for the authenticated user.
     */
    public function initiate(Request $request): JsonResponse
    {
        $user = Auth::user();
        try {
            $userTask = $this->rewardConversionService->initiateConversion($user);
            // Return only task info, not reward details
            $task = $userTask->task()->select('id', 'title', 'description', 'task_type_id', 'min_points', 'max_points')->first();
            return response()->json([
                'success' => true,
                'message' => 'Conversion task assigned successfully.',
                'data' => [
                    'user_conversion_task_id' => $userTask->id,
                    'task' => $task,
                    'status' => $userTask->status,
                    'assigned_at' => $userTask->assigned_at,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
