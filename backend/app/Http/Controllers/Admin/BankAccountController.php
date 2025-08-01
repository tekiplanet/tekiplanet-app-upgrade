<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use App\Services\NotificationService;

class BankAccountController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $query = BankAccount::with('user')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('account_name', 'like', "%{$search}%")
                      ->orWhere('account_number', 'like', "%{$search}%")
                      ->orWhere('bank_name', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->verification_status, function ($query, $status) {
                $query->where('is_verified', $status === 'verified');
            });

        $bankAccounts = $query->latest()->paginate(15);

        return view('admin.bank-accounts.index', compact('bankAccounts'));
    }

    public function show(BankAccount $bankAccount)
    {
        $bankAccount->load(['user', 'transactions']);
        return view('admin.bank-accounts.show', compact('bankAccount'));
    }

    public function updateVerification(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'is_verified' => 'required|boolean',
            'verification_notes' => 'nullable|string'
        ]);

        try {
            $bankAccount->update($validated);

            // Send notification to user
            $this->notificationService->send([
                'type' => 'bank_account_verification',
                'title' => 'Bank Account Verification Update',
                'message' => $validated['is_verified'] 
                    ? 'Your bank account has been verified successfully.'
                    : 'Your bank account verification status has been updated.',
                'action_url' => "/bank-accounts/{$bankAccount->id}"
            ], $bankAccount->user);

            return response()->json([
                'success' => true,
                'message' => 'Bank account verification status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update verification status: ' . $e->getMessage()
            ], 500);
        }
    }
} 