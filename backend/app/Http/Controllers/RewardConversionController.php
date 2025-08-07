<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RewardConversionService;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\UserConversionTask;

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

    /**
     * Get the authenticated user's conversion tasks and rewards balance.
     */
    public function getUserTasks(): JsonResponse
    {
        $user = Auth::user();
        
        try {
            // Get user's conversion tasks with task details
            $userTasks = UserConversionTask::with(['task', 'task.taskType', 'task.rewardType'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Fetched user tasks', [
                'user_id' => $user->id,
                'learn_rewards' => $user->learn_rewards,
                'task_count' => $userTasks->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'tasks' => $userTasks,
                    'learn_rewards' => $user->learn_rewards,
                    'total_tasks' => $userTasks->count(),
                    'completed_tasks' => $userTasks->where('status', 'completed')->count(),
                    'active_tasks' => $userTasks->whereIn('status', ['assigned', 'in_progress'])->count(),
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching user tasks: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tasks'
            ], 500);
        }
    }

    /**
     * Debug method to check user's learning rewards and available tasks.
     */
    public function debug(): JsonResponse
    {
        $user = Auth::user();
        
        try {
            // Get user's learning rewards
            $learnRewards = $user->learn_rewards;
            
            // Get all conversion tasks
            $allTasks = \App\Models\ConversionTask::all();
            
            // Get eligible tasks for this user
            $eligibleTasks = \App\Models\ConversionTask::where('min_points', '<=', $learnRewards)
                ->where('max_points', '>=', $learnRewards)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $user->id,
                    'learn_rewards' => $learnRewards,
                    'total_conversion_tasks' => $allTasks->count(),
                    'eligible_tasks' => $eligibleTasks->count(),
                    'all_tasks' => $allTasks,
                    'eligible_tasks_list' => $eligibleTasks
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Debug error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Debug failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
