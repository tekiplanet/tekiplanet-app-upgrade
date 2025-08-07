<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RewardConversionService;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\UserConversionTask;

class RewardConversionController extends Controller
{
    protected $rewardConversionService;
    protected $currencyService;

    public function __construct(RewardConversionService $rewardConversionService, CurrencyService $currencyService)
    {
        $this->rewardConversionService = $rewardConversionService;
        $this->currencyService = $currencyService;
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
                $query->select('id', 'title', 'description', 'task_type_id', 'reward_type_id', 'min_points', 'max_points', 'product_id', 'coupon_id', 'course_id', 'cash_amount', 'discount_percent', 'service_name');
            }, 'task.type', 'task.rewardType', 'task.product', 'task.coupon', 'task.course'])->paginate($perPage, ['*'], 'page', $page);

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
     * Get actionable instructions and referral link for a user conversion task.
     */
    public function getTaskInstructions($userConversionTaskId): JsonResponse
    {
        $user = Auth::user();
        $userTask = \App\Models\UserConversionTask::where('id', $userConversionTaskId)
            ->where('user_id', $user->id)
            ->with('task')
            ->firstOrFail();

        $task = $userTask->task;
        $instructions = '';
        $referralLink = null;
        $progress = null;

        // Example: Only for referral registration tasks
        if ($task && strtolower($task->type->name) === 'refer to register') {
            $instructions = 'Share your referral link. When someone registers using your link, it will count towards your task.';
            $referralLink = $userTask->getReferralLink();
            $target = $task->referral_target ?? 1;
            $progress = [
                'needed' => $target,
                'completed' => $userTask->referral_count ?? 0,
            ];
        } else {
            $instructions = 'Complete the assigned task as described.';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'instructions' => $instructions,
                'referral_link' => $referralLink,
                'progress' => $progress,
                'task' => $task,
            ]
        ]);
    }

    /**
     * Get reward details for a completed user conversion task.
     */
    public function getTaskReward($userConversionTaskId): JsonResponse
    {
        $user = Auth::user();
        $userTask = \App\Models\UserConversionTask::where('id', $userConversionTaskId)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->with(['task.type', 'task.rewardType', 'task.product', 'task.coupon', 'task.course'])
            ->firstOrFail();

        $task = $userTask->task;
        $rewardDetails = [];

        // Debug logging
        Log::info('Task reward debug', [
            'task_id' => $task->id,
            'reward_type' => $task->rewardType ? $task->rewardType->name : 'null',
            'coupon_id' => $task->coupon_id,
            'coupon_loaded' => $task->coupon ? 'yes' : 'no',
            'coupon_data' => $task->coupon ? $task->coupon->toArray() : 'null'
        ]);

        // Build reward details based on reward type
        if ($task->rewardType) {
            $rewardType = strtolower($task->rewardType->name);
            
            switch ($rewardType) {
                case 'cash':
                    // Convert cash amount if user has a different currency
                    $cashAmount = $task->cash_amount;
                    $originalCurrency = 'NGN';
                    $userCurrency = $user->currency_code;
                    
                    if ($userCurrency && $userCurrency !== 'NGN') {
                        $cashAmount = $this->currencyService->convertAmount(
                            $task->cash_amount,
                            'NGN', // Cash amounts are stored in NGN
                            $userCurrency
                        );
                    }
                    
                    // Get currency symbol for display
                    $currencySymbol = '₦'; // Default to NGN
                    if ($userCurrency && $userCurrency !== 'NGN') {
                        try {
                            $currencySymbol = $this->currencyService->getCurrencySymbol($userCurrency);
                        } catch (\Exception $e) {
                            // Fallback to currency code if symbol not found
                            $currencySymbol = $userCurrency;
                        }
                    }
                    
                    $rewardDetails = [
                        'type' => 'cash',
                        'amount' => $cashAmount,
                        'original_amount' => $task->cash_amount,
                        'currency' => $userCurrency ?: 'NGN',
                        'original_currency' => 'NGN',
                        'description' => "You have been rewarded {$currencySymbol}{$cashAmount}"
                    ];
                    break;
                    
                case 'coupon':
                    if ($task->coupon) {
                        $rewardDetails = [
                            'type' => 'coupon',
                            'coupon' => $task->coupon,
                            'description' => "Coupon: {$task->coupon->code}"
                        ];
                    } else {
                        $rewardDetails = [
                            'type' => 'coupon',
                            'coupon' => null,
                            'description' => "Coupon reward (code not yet assigned)"
                        ];
                    }
                    break;
                    
                case 'course access':
                    // Convert course prices if user has a different currency
                    $course = $task->course;
                    if ($course && $user->currency_code && $user->currency_code !== 'NGN') {
                        // Convert course price
                        if (isset($course->price)) {
                            $course->price = $this->currencyService->convertAmount(
                                $course->price,
                                'NGN', // Course prices are stored in NGN
                                $user->currency_code
                            );
                        }
                        
                        // Convert enrollment fee if it exists
                        if (isset($course->enrollment_fee)) {
                            $course->enrollment_fee = $this->currencyService->convertAmount(
                                $course->enrollment_fee,
                                'NGN', // Enrollment fees are stored in NGN
                                $user->currency_code
                            );
                        }
                    }
                    
                    $rewardDetails = [
                        'type' => 'course_access',
                        'course' => $course,
                        'description' => $course ? "Free access to: {$course->title}" : "Course access"
                    ];
                    break;
                    
                case 'discount code':
                    $rewardDetails = [
                        'type' => 'discount_code',
                        'percentage' => $task->discount_percent,
                        'service' => $task->service_name,
                        'description' => "{$task->discount_percent}% discount on {$task->service_name}"
                    ];
                    break;
                    
                default:
                    $rewardDetails = [
                        'type' => 'unknown',
                        'description' => 'Reward details not available'
                    ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'task' => $task,
                'reward_details' => $rewardDetails,
                'completed_at' => $userTask->completed_at,
                'claimed' => $userTask->claimed,
                'claimed_at' => $userTask->claimed_at,
                'user_task' => $userTask
            ]
        ]);
    }

    /**
     * Claim course access reward for a completed user conversion task.
     */
    public function claimCourseAccess($userConversionTaskId): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $userTask = \App\Models\UserConversionTask::where('id', $userConversionTaskId)
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->with(['task.type', 'task.rewardType', 'task.course'])
                ->firstOrFail();

            $task = $userTask->task;
            
            // Verify this is a course access reward
            if (!$task->rewardType || strtolower($task->rewardType->name) !== 'course access') {
                throw new \Exception('This task does not have a course access reward.');
            }
            
            if (!$task->course) {
                throw new \Exception('No course assigned to this reward.');
            }

            // Check if the task reward has already been claimed
            if ($userTask->claimed) {
                return response()->json([
                    'success' => false,
                    'message' => 'This reward has already been claimed.',
                    'data' => [
                        'already_claimed' => true,
                        'claimed_at' => $userTask->claimed_at,
                        'course' => $task->course,
                        'course_management_url' => "/dashboard/academy/course/{$task->course->id}/manage"
                    ]
                ], 400);
            }

            // Check if user is already enrolled in this course
            $existingEnrollment = \App\Models\Enrollment::where('user_id', $user->id)
                ->where('course_id', $task->course->id)
                ->first();

            if ($existingEnrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already enrolled in this course.',
                    'data' => [
                        'already_enrolled' => true,
                        'enrollment' => $existingEnrollment,
                        'course' => $task->course,
                        'course_management_url' => "/dashboard/academy/course/{$task->course->id}/manage"
                    ]
                ], 400);
            }

            // Use the enrollment service to enroll user for free
            $enrollmentService = new \App\Services\EnrollmentService();
            $enrollment = $enrollmentService->enrollUserInCourseForReward($user, $task->course);

            // Mark the task as claimed
            $userTask->update([
                'claimed' => true,
                'claimed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Course access claimed successfully! You are now enrolled.',
                'data' => [
                    'enrollment' => $enrollment,
                    'course' => $task->course,
                    'course_management_url' => "/dashboard/academy/course/{$task->course->id}/manage"
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Course access claim failed', [
                'user_id' => $user->id,
                'user_conversion_task_id' => $userConversionTaskId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Claim cash reward for a completed user conversion task.
     */
    public function claimCashReward($userConversionTaskId): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $userTask = \App\Models\UserConversionTask::where('id', $userConversionTaskId)
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->with(['task.type', 'task.rewardType'])
                ->firstOrFail();

            $task = $userTask->task;
            
            // Verify this is a cash reward
            if (!$task->rewardType || strtolower($task->rewardType->name) !== 'cash') {
                throw new \Exception('This task does not have a cash reward.');
            }

            // Check if the task reward has already been claimed
            if ($userTask->claimed) {
                return response()->json([
                    'success' => false,
                    'message' => 'This reward has already been claimed.',
                    'data' => [
                        'already_claimed' => true,
                        'claimed_at' => $userTask->claimed_at
                    ]
                ], 400);
            }

            // Convert cash amount to user's currency for wallet addition
            $cashAmount = $task->cash_amount;
            if ($user->currency_code && $user->currency_code !== 'NGN') {
                $cashAmount = $this->currencyService->convertAmount(
                    $task->cash_amount,
                    'NGN', // Cash amounts are stored in NGN
                    $user->currency_code
                );
            }

            // Add to user's wallet balance
            $user->wallet_balance += $cashAmount;
            $user->save();

            // Create transaction record
            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'amount' => $cashAmount,
                'type' => 'credit',
                'description' => "Cash reward from task: {$task->title}",
                'category' => 'reward',
                'status' => 'completed',
                'payment_method' => 'reward',
                'reference_number' => 'REWARD-' . uniqid(),
                'notes' => [
                    'user_conversion_task_id' => $userTask->id,
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'reward_type' => 'cash',
                    'original_amount_ngn' => $task->cash_amount,
                    'converted_amount' => $cashAmount,
                    'user_currency' => $user->currency_code
                ]
            ]);

            // Mark the task as claimed
            $userTask->update([
                'claimed' => true,
                'claimed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cash reward claimed successfully! Amount added to your wallet.',
                'data' => [
                    'amount' => $cashAmount,
                    'currency' => $user->currency_code ?: 'NGN',
                    'wallet_balance' => $user->wallet_balance,
                    'task' => $task
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Cash reward claim failed', [
                'user_id' => $user->id,
                'user_conversion_task_id' => $userConversionTaskId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
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
