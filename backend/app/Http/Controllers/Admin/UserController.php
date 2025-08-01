<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Notifications\TransactionNotification;
use App\Services\NotificationService;
use App\Models\Notification;
use App\Notifications\CustomNotification;
use App\Notifications\AccountStatusNotification;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $query = User::query()
            ->with(['businessProfile', 'professional'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->when($request->account_type, function ($query, $type) {
                switch ($type) {
                    case 'business':
                        $query->whereHas('businessProfile');
                        break;
                    case 'professional':
                        $query->whereHas('professional');
                        break;
                    case 'student':
                        $query->whereDoesntHave('businessProfile')
                            ->whereDoesntHave('professional');
                        break;
                }
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            });

        $users = $query->latest()->paginate(10)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => [
                'search' => $request->search,
                'account_type' => $request->account_type,
                'status' => $request->status,
            ],
            'statusOptions' => User::getStatusOptions()
        ]);
    }

    public function show(Request $request, User $user)
    {
        // Load relationships
        $user->load(['businessProfile', 'professional']);
        
        // Get transactions with search
        $transactions = $user->transactions()
            ->when($request->search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%");
                });
            })
            ->when($request->type, function($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Get wallet statistics
        $stats = [
            'total_credits' => $user->transactions()->credits()->sum('amount'),
            'total_debits' => $user->transactions()->debits()->sum('amount'),
            'current_balance' => $user->wallet_balance,
        ];

        // Get currency settings
        $currency = [
            'code' => Setting::getSetting('default_currency', 'USD'),
            'symbol' => Setting::getSetting('currency_symbol', '$')
        ];

        return view('admin.users.show', compact('user', 'transactions', 'stats', 'currency'));
    }

    // Add method to handle new transactions
    public function createTransaction(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0',
                'type' => 'required|in:credit,debit',
                'description' => 'required|string|max:255',
                'category' => 'required|string|max:50',
                'payment_method' => 'required|string|max:50',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Get currency settings
            $currency = [
                'code' => Setting::getSetting('default_currency', 'USD'),
                'symbol' => Setting::getSetting('currency_symbol', '$')
            ];

            // Check balance for debit transactions
            if ($validated['type'] === 'debit' && $user->wallet_balance < $validated['amount']) {
                throw new \Exception('Insufficient balance for debit transaction.');
            }

            // Generate reference number
            $validated['reference_number'] = 'TXN-' . strtoupper(uniqid());
            $validated['status'] = 'completed';
            $validated['user_id'] = $user->id;

            // Create transaction
            $transaction = Transaction::create($validated);

            // Update user's wallet balance
            if ($validated['type'] === 'credit') {
                $user->increment('wallet_balance', $validated['amount']);
            } else {
                $user->decrement('wallet_balance', $validated['amount']);
            }

            // Create transaction notification
            $this->notificationService->send([
                'type' => Notification::TYPE_PAYMENT,
                'title' => ucfirst($transaction->type) . ' Transaction',
                'message' => "A {$transaction->type} transaction of {$currency['symbol']}{$transaction->amount} has been processed on your account.",
                'icon' => $transaction->type === 'credit' ? 'arrow-up' : 'arrow-down',
                'action_url' => '/dashboard/wallet',
                'extra_data' => [
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'type' => $transaction->type,
                    'reference' => $transaction->reference_number
                ]
            ], $user);

            // Send email notification
            $user->notify(new TransactionNotification($transaction));

            if ($user->deviceTokens()->exists()) {
                // The BaseNotification class will handle both in-app and push notifications
                $user->notify(new TransactionNotification($transaction));
            }

            return response()->json([
                'message' => 'Transaction created successfully',
                'transaction' => $transaction
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Failed to create transaction'
            ], 422);
        }
    }

    public function updateStatus(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:active,inactive'
            ]);

            $user->update($validated);

            // Send notification to user
            $this->notificationService->send([
                'type' => Notification::TYPE_PROFILE,
                'title' => 'Account Status Updated',
                'message' => "Your account has been marked as {$validated['status']}.",
                'icon' => 'user-circle',
                'action_url' => '/dashboard'
            ], $user);

            // Send email notification
            $user->notify(new AccountStatusNotification($validated['status']));

            return response()->json([
                'message' => 'User status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'username' => 'required|string|unique:users,username,' . $user->id,
                'phone' => 'nullable|string|max:20'
            ]);

            $user->update($validated);

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function sendNotification(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'send_email' => 'boolean'
            ]);

            // Send in-app notification
            $this->notificationService->send([
                'type' => Notification::TYPE_SYSTEM,
                'title' => $validated['title'],
                'message' => $validated['message'],
                'icon' => 'bell',
                'action_url' => '/dashboard/notifications'
            ], $user);

            // Send email if requested
            if ($request->send_email) {
                $user->notify(new CustomNotification(
                    $validated['title'], 
                    $validated['message'],
                    'system'
                ));
            }

            return response()->json([
                'message' => 'Notification sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy(User $user)
    {
        try {
            // Begin transaction
            DB::beginTransaction();

            // Delete related records
            if ($user->businessProfile) {
                $user->businessProfile->delete();
            }

            if ($user->professional) {
                $user->professional->delete();
            }

            // Delete notifications and transactions
            $user->userNotifications()->delete();
            $user->transactions()->delete();

            // Delete enrollments and course notices
            $user->enrollments()->delete();
            $user->userCourseNotices()->delete();
            
            // Delete the user
            $user->delete();

            // Commit transaction
            DB::commit();

            return response()->json([
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            \Log::error('User deletion failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to delete user'
            ], 422);
        }
    }

    public function notifyBulk(Request $request)
    {
        try {
            $validated = $request->validate([
                'selected_users' => 'required|string',
                'title' => 'required|string',
                'message' => 'required|string'
            ]);

            $userIds = json_decode($validated['selected_users'], true);
            $users = User::whereIn('id', $userIds)->get();
            
            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($users as $user) {
                try {
                    $this->notificationService->send([
                        'type' => 'admin_notification',
                        'title' => $validated['title'],
                        'message' => $validated['message'],
                        'icon' => 'bell',
                        'action_url' => null
                    ], $user);
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    // Only log detailed error for debugging
                    \Log::error('Notification failed for user ' . $user->id . ': ' . $e->getMessage());
                    
                    // Check if it's a Firebase token error
                    if (str_contains($e->getMessage(), 'device identified by')) {
                        // Remove invalid token
                        $user->deviceTokens()->delete();
                    }
                }
            }

            $message = "Notifications sent successfully to {$successCount} users.";
            if ($failedCount > 0) {
                $message .= " Failed to send to {$failedCount} users (invalid device tokens have been removed).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'details' => [
                    'success_count' => $successCount,
                    'failed_count' => $failedCount
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Bulk notification failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process notifications. Please try again.'
            ], 422);
        }
    }
} 