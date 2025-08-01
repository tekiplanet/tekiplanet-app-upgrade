<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use App\Mail\TransactionStatusUpdated;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendTransactionEmail;
use App\Jobs\SendTransactionNotification;
use App\Jobs\SendTransactionReceipt;
use PDF;
use App\Mail\TransactionReceipt;
use App\Models\Setting;

class TransactionController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $query = Transaction::with(['user'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference_number', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            });

        $transactions = $query->latest()->paginate(15);
        
        // Add this for debugging
        \Log::info('Transactions with users:', [
            'sample_transaction' => $transactions->first(),
            'has_user' => $transactions->first()->user ? 'yes' : 'no',
            'user_details' => $transactions->first()->user
        ]);

        return view('admin.transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user']);
        return view('admin.transactions.show', compact('transaction'));
    }

    public function updateStatus(Request $request, Transaction $transaction)
    {
        // 1. Prevent updating completed, cancelled, or failed transactions
        if (in_array($transaction->status, ['completed', 'cancelled', 'failed'])) {
            $message = match ($transaction->status) {
                'completed' => 'Cannot update status of completed transactions',
                'cancelled' => 'Cannot update status of cancelled transactions',
                'failed' => 'Cannot update status of failed transactions',
            };

            return response()->json([
                'success' => false,
                'message' => $message
            ], 422);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,completed,failed,cancelled',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Prepare new note
            $newNote = [
                'status_update' => [
                    'from' => $transaction->status,
                    'to' => $validated['status'],
                    'date' => now()->format('Y-m-d H:i:s'),
                    'note' => $validated['notes'] ?? 'Status updated by admin'
                ]
            ];

            // Process based on transaction type and new status
            $user = $transaction->user;
            $amount = $transaction->amount;

            if ($transaction->type === 'debit') {
                if (in_array($validated['status'], ['cancelled', 'failed']) && $transaction->status === 'pending') {
                    // Credit back the amount to user's wallet
                    $user->increment('wallet_balance', $amount);
                    $newNote['wallet_update'] = "Credited back {$amount} to wallet due to {$validated['status']} status";
                }
            } elseif ($transaction->type === 'credit') {
                if ($validated['status'] === 'completed' && $transaction->status === 'pending') {
                    // Credit the amount to user's wallet
                    $user->increment('wallet_balance', $amount);
                    $newNote['wallet_update'] = "Credited {$amount} to wallet on completion";
                }
            }

            // Merge with existing notes after all notes are prepared
            $existingNotes = is_array($transaction->notes) ? $transaction->notes : [];
            $updatedNotes = array_merge($existingNotes, [$newNote]);  // Wrap $newNote in array to maintain history

            // Update transaction
            $transaction->update([
                'status' => $validated['status'],
                'notes' => $updatedNotes
            ]);

            // Prepare notification message based on status
            $notificationMessage = match ($validated['status']) {
                'failed' => "Your transaction #{$transaction->reference_number} has failed.",
                'cancelled' => "Your transaction #{$transaction->reference_number} has been cancelled.",
                'completed' => "Your transaction #{$transaction->reference_number} has been completed successfully.",
                default => "Your transaction #{$transaction->reference_number} status has been updated to " . ucfirst($validated['status'])
            };

            // Add wallet balance info if applicable
            if (isset($newNote['wallet_update'])) {
                $notificationMessage .= " " . $newNote['wallet_update'];
            }

            // Prepare notification data
            $notificationData = [
                'type' => 'transaction_status_updated',
                'title' => match ($validated['status']) {
                    'failed' => 'Transaction Failed',
                    'cancelled' => 'Transaction Cancelled',
                    'completed' => 'Transaction Completed',
                    default => 'Transaction Status Updated'
                },
                'message' => $notificationMessage,
                'action_url' => "/transactions/{$transaction->id}",
                'extra_data' => [
                    'transaction_id' => $transaction->id,
                    'old_status' => $transaction->status,
                    'new_status' => $validated['status'],
                    'wallet_updated' => isset($newNote['wallet_update'])
                ]
            ];

            // Queue notification and email
            dispatch(new SendTransactionNotification($notificationData, $user));
            dispatch(new SendTransactionEmail($transaction, $user));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction status updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Transaction status update failed:', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update transaction status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadReceipt(Transaction $transaction)
    {
        $settings = [
            'currency_symbol' => Setting::getSetting('currency_symbol', 'NGN'),
            'site_name' => Setting::getSetting('site_name', 'TekiPlanet'),
            'support_email' => Setting::getSetting('support_email', 'support@tekiplanet.com')
        ];

        $pdf = PDF::loadView('receipts.transaction-advanced', [
            'transaction' => $transaction,
            'settings' => $settings
        ]);

        return $pdf->stream("transaction-{$transaction->reference_number}.pdf");
    }

    public function sendReceipt(Transaction $transaction)
    {
        try {
            dispatch(new SendTransactionReceipt($transaction, $transaction->user));

            return response()->json([
                'success' => true,
                'message' => 'Receipt has been sent to user\'s email'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send receipt:', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send receipt: ' . $e->getMessage()
            ], 500);
        }
    }
} 