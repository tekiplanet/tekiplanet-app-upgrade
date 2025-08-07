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
     * Get the authenticated user's conversion tasks and rewards balance with pagination and filtering.
     */
    public function getUserTasks(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        try {
            // Get query parameters
            $page = $request->get('page', 1);
            $perPage = min($request->get('per_page', 10), 50); // Max 50 per page
            $status = $request->get('status', 'all');
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // Build query
            $query = UserConversionTask::where('user_id', $user->id);

            // Apply status filter
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            // Apply sorting
            switch ($sortBy) {
                case 'assigned_at':
                    $query->orderBy('assigned_at', $sortOrder);
                    break;
                case 'status':
                    $query->orderBy('status', $sortOrder);
                    break;
                case 'created_at':
                default:
                    $query->orderBy('created_at', $sortOrder);
                    break;
            }

            // Get paginated results
            $userTasks = $query->with(['task' => function($query) {
                $query->select('id', 'title', 'description', 'task_type_id', 'reward_type_id', 'min_points', 'max_points');
            }])->paginate($perPage, ['*'], 'page', $page);

            // Get total counts for stats
            $totalTasks = UserConversionTask::where('user_id', $user->id)->count();
            $assignedTasks = UserConversionTask::where('user_id', $user->id)->where('status', 'assigned')->count();
            $inProgressTasks = UserConversionTask::where('user_id', $user->id)->where('status', 'in_progress')->count();
            $completedTasks = UserConversionTask::where('user_id', $user->id)->where('status', 'completed')->count();
            $failedTasks = UserConversionTask::where('user_id', $user->id)->where('status', 'failed')->count();

            Log::info('Fetched user tasks with pagination', [
                'user_id' => $user->id,
                'learn_rewards' => $user->learn_rewards,
                'page' => $page,
                'per_page' => $perPage,
                'status_filter' => $status,
                'sort_by' => $sortBy,
                'total_tasks' => $totalTasks,
                'current_page_count' => $userTasks->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'tasks' => $userTasks->items(),
                    'pagination' => [
                        'current_page' => $userTasks->currentPage(),
                        'last_page' => $userTasks->lastPage(),
                        'per_page' => $userTasks->perPage(),
                        'total' => $userTasks->total(),
                        'from' => $userTasks->firstItem(),
                        'to' => $userTasks->lastItem(),
                    ],
                    'stats' => [
                        'total_tasks' => $totalTasks,
                        'assigned_tasks' => $assignedTasks,
                        'in_progress_tasks' => $inProgressTasks,
                        'completed_tasks' => $completedTasks,
                        'failed_tasks' => $failedTasks,
                    ],
                    'learn_rewards' => $user->learn_rewards,
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching user tasks: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tasks: ' . $e->getMessage()
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
