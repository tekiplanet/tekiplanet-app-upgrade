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
use Barryvdh\DomPDF\Facade\Pdf;

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
            ->with(['task.type', 'task.product', 'task.taskCourse'])
            ->firstOrFail();

        $task = $userTask->task;
        $instructions = '';
        $referralLink = null;
        $shareLink = null;
        $progress = null;
        $product = null;

        // Handle different task types
        if ($task && strtolower($task->type->name) === 'refer to register') {
            $instructions = 'Share your referral link. When someone registers using your link, it will count towards your task.';
            $referralLink = $userTask->getReferralLink();
            $target = $task->referral_target ?? 1;
            $progress = [
                'needed' => $target,
                'completed' => $userTask->referral_count ?? 0,
            ];
        } elseif ($task && strtolower($task->type->name) === 'share product') {
            if ($task->product) {
                $product = $task->product;
                $instructions = "Share the product link below. When someone purchases this product through your link, it will count towards your task.";
                
                // Generate share link using ProductShareService
                $productShareService = app(\App\Services\ProductShareService::class);
                $shareLink = $productShareService->generateShareLink($userTask, $product);
                
                $target = $task->share_target ?? 1;
                $progress = [
                    'needed' => $target,
                    'completed' => $userTask->share_count ?? 0,
                ];
            } else {
                $instructions = 'This task requires sharing a product, but no product has been assigned. Please contact support.';
            }
        } elseif ($task && strtolower($task->type->name) === 'refer to enroll course') {
            if ($task->taskCourse) {
                $course = $task->taskCourse;
                $instructions = "Share the course link below. When someone enrolls in this course through your link, it will count towards your task.";
                
                // Generate share link using CourseShareService
                $courseShareService = app(\App\Services\CourseShareService::class);
                $shareLink = $courseShareService->generateShareLink($userTask, $course);
                
                $target = $task->enrollment_target ?? 1;
                $progress = [
                    'needed' => $target,
                    'completed' => $userTask->enrollment_count ?? 0,
                ];
            } else {
                $instructions = 'This task requires sharing a course, but no course has been assigned. Please contact support.';
            }
        } else {
            $instructions = 'Complete the assigned task as described.';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'instructions' => $instructions,
                'referral_link' => $referralLink,
                'share_link' => $shareLink,
                'progress' => $progress,
                'product' => $product,
                'course' => $course ?? null,
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
            ->with(['task.type', 'task.rewardType', 'task.product', 'task.coupon', 'task.course', 'task.taskCourse'])
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

        // If this is a discount code reward and it's already claimed, include the discount slip
        $discountSlip = null;
        if ($userTask->claimed && $task->rewardType && strtolower($task->rewardType->name) === 'discount code') {
            $discountSlip = \App\Models\DiscountSlip::where('user_conversion_task_id', $userTask->id)->first();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'task' => $task,
                'reward_details' => $rewardDetails,
                'completed_at' => $userTask->completed_at,
                'claimed' => $userTask->claimed,
                'claimed_at' => $userTask->claimed_at,
                'user_task' => $userTask,
                'discount_slip' => $discountSlip
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
                ->with(['task.type', 'task.rewardType', 'task.course', 'task.taskCourse'])
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
     * Claim discount reward for a completed user conversion task.
     */
    public function claimDiscountReward($userConversionTaskId): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $userTask = \App\Models\UserConversionTask::where('id', $userConversionTaskId)
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->with(['task.type', 'task.rewardType'])
                ->firstOrFail();

            $task = $userTask->task;
            
            // Verify this is a discount code reward
            if (!$task->rewardType || strtolower($task->rewardType->name) !== 'discount code') {
                throw new \Exception('This task does not have a discount code reward.');
            }

            // Check if the task reward has already been claimed
            if ($userTask->claimed) {
                // Get the existing discount slip
                $discountSlip = \App\Models\DiscountSlip::where('user_conversion_task_id', $userTask->id)->first();
                
                return response()->json([
                    'success' => false,
                    'message' => 'This reward has already been claimed.',
                    'data' => [
                        'already_claimed' => true,
                        'claimed_at' => $userTask->claimed_at,
                        'discount_slip' => $discountSlip
                    ]
                ], 400);
            }

            // Validate required fields
            if (!$task->discount_percent || !$task->service_name) {
                throw new \Exception('Discount reward configuration is incomplete.');
            }

            // Create discount slip
            $discountSlip = \App\Models\DiscountSlip::createForTask(
                $userTask,
                $task->service_name,
                $task->discount_percent
            );

            // Mark the task as claimed
            $userTask->update([
                'claimed' => true,
                'claimed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Discount reward claimed successfully! Your discount slip has been generated.',
                'data' => [
                    'discount_slip' => $discountSlip,
                    'task' => $task
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Discount reward claim failed', [
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
     * Get discount slip details for a claimed discount reward.
     */
    public function getDiscountSlip($userConversionTaskId): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $userTask = \App\Models\UserConversionTask::where('id', $userConversionTaskId)
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->where('claimed', true)
                ->with(['task.type', 'task.rewardType'])
                ->firstOrFail();

            $task = $userTask->task;
            
            // Verify this is a discount code reward
            if (!$task->rewardType || strtolower($task->rewardType->name) !== 'discount code') {
                throw new \Exception('This task does not have a discount code reward.');
            }

            // Get the discount slip
            $discountSlip = \App\Models\DiscountSlip::where('user_conversion_task_id', $userTask->id)->first();
            
            if (!$discountSlip) {
                throw new \Exception('Discount slip not found for this task.');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'discount_slip' => $discountSlip,
                    'task' => $task,
                    'user_task' => $userTask
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Get discount slip failed', [
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
     * Download discount slip as PDF.
     */
    public function downloadDiscountSlip($userConversionTaskId): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $userTask = \App\Models\UserConversionTask::where('id', $userConversionTaskId)
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->where('claimed', true)
                ->with(['task.type', 'task.rewardType'])
                ->firstOrFail();

            $task = $userTask->task;
            
            // Verify this is a discount code reward
            if (!$task->rewardType || strtolower($task->rewardType->name) !== 'discount code') {
                throw new \Exception('This task does not have a discount code reward.');
            }

            // Get the discount slip
            $discountSlip = \App\Models\DiscountSlip::where('user_conversion_task_id', $userTask->id)->first();
            
            if (!$discountSlip) {
                throw new \Exception('Discount slip not found for this task.');
            }

            // Generate PDF content (this is a simplified version - you might want to use a proper PDF library)
            $pdfContent = $this->generateDiscountSlipPDF($discountSlip, $user, $task);

            return response()->json([
                'success' => true,
                'data' => [
                    'pdf_content' => base64_encode($pdfContent),
                    'filename' => "discount_slip_{$discountSlip->discount_code}.pdf",
                    'discount_slip' => $discountSlip
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Download discount slip failed', [
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
     * Generate PDF content for discount slip.
     */
    private function generateDiscountSlipPDF($discountSlip, $user, $task): string
    {
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Discount Slip</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 20px; 
                    color: #333;
                    line-height: 1.6;
                }
                .header { 
                    text-align: center; 
                    border-bottom: 2px solid #333; 
                    padding-bottom: 20px; 
                    margin-bottom: 30px; 
                }
                .header h1 {
                    color: #2563eb;
                    margin-bottom: 5px;
                }
                .discount-code { 
                    font-size: 28px; 
                    font-weight: bold; 
                    color: #2563eb; 
                    text-align: center; 
                    margin: 30px 0; 
                    padding: 20px;
                    border: 3px solid #2563eb;
                    border-radius: 10px;
                    background-color: #f8fafc;
                }
                .details { 
                    margin: 30px 0; 
                    padding: 20px;
                    background-color: #f8fafc;
                    border-radius: 8px;
                }
                .detail-row { 
                    margin: 15px 0; 
                    display: flex;
                    justify-content: space-between;
                }
                .label { 
                    font-weight: bold; 
                    color: #374151;
                }
                .value {
                    color: #1f2937;
                }
                .terms { 
                    margin-top: 30px; 
                    padding: 20px; 
                    background-color: #f3f4f6; 
                    border-radius: 8px; 
                    border-left: 4px solid #2563eb;
                }
                .terms h3 {
                    color: #2563eb;
                    margin-top: 0;
                }
                .footer { 
                    margin-top: 40px; 
                    text-align: center; 
                    font-size: 12px; 
                    color: #666; 
                    border-top: 1px solid #e5e7eb;
                    padding-top: 20px;
                }
                .logo {
                    font-size: 18px;
                    font-weight: bold;
                    color: #2563eb;
                }
            </style>
        </head>
        <body>
            <div class='header'>
                <div class='logo'>TekPlanet</div>
                <h1>Discount Slip</h1>
            </div>
            
            <div class='discount-code'>
                {$discountSlip->discount_code}
            </div>
            
            <div class='details'>
                <div class='detail-row'>
                    <span class='label'>Service:</span>
                    <span class='value'>{$discountSlip->service_name}</span>
                </div>
                <div class='detail-row'>
                    <span class='label'>Discount:</span>
                    <span class='value'>{$discountSlip->discount_percent}%</span>
                </div>
                <div class='detail-row'>
                    <span class='label'>Status:</span>
                    <span class='value' style='font-weight: bold; color: " . ($discountSlip->is_used ? '#dc2626' : '#059669') . ";'>
                        " . ($discountSlip->is_used ? 'USED' : 'ACTIVE') . "
                    </span>
                </div>
                <div class='detail-row'>
                    <span class='label'>Valid Until:</span>
                    <span class='value'>{$discountSlip->expires_at->format('F j, Y')}</span>
                </div>
                <div class='detail-row'>
                    <span class='label'>Issued To:</span>
                    <span class='value'>{$user->name}</span>
                </div>
                <div class='detail-row'>
                    <span class='label'>Issued On:</span>
                    <span class='value'>{$discountSlip->created_at->format('F j, Y')}</span>
                </div>
                " . ($discountSlip->is_used && $discountSlip->used_at ? "
                <div class='detail-row'>
                    <span class='label'>Used On:</span>
                    <span class='value'>{$discountSlip->used_at->format('F j, Y')}</span>
                </div>
                " : "") . "
            </div>
            
            <div class='terms'>
                <h3>Terms & Conditions</h3>
                <p>{$discountSlip->terms_conditions}</p>
                " . ($discountSlip->is_used ? "
                <p style='color: #dc2626; font-weight: bold; margin-top: 15px;'>
                    ⚠️ This discount slip has been used and is no longer valid.
                </p>
                " : "") . "
            </div>
            
            <div class='footer'>
                <p>This discount slip is valid for one-time use only.</p>
                <p>Generated on {$discountSlip->created_at->format('F j, Y \a\t g:i A')}</p>
                <p>TekPlanet Learning Platform - All rights reserved</p>
            </div>
        </body>
        </html>";

        // Use DomPDF Facade to generate PDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        
        // Return the PDF content as string
        return $pdf->output();
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
