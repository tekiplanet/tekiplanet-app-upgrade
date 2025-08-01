<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yabacon\Paystack;

class WalletController extends Controller
{
    public function initiateBankTransfer(Request $request)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:100'
        ]);

        $user = auth()->user();

        // Create a pending bank transfer transaction
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => 'pending_credit',
            'amount' => $validatedData['amount'],
            'description' => "Bank Transfer Funding",
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
            'reference_number' => 'BT-' . Str::random(10)
        ]);

        return response()->json([
            'message' => 'Bank transfer initiated. Pending admin verification.',
            'transaction' => $transaction
        ], 200);
    }

    public function initiatePaystackPayment(Request $request)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:100'
        ]);

        $user = auth()->user();
        $paystackSecretKey = config('services.paystack.secret_key');

        // Validate Paystack secret key
        if (!$paystackSecretKey || !str_starts_with($paystackSecretKey, 'sk_')) {
            Log::error('Invalid Paystack Secret Key', [
                'key' => $paystackSecretKey ? 'Provided but invalid' : 'Not provided'
            ]);
            
            return response()->json([
                'message' => 'Payment gateway configuration error',
                'error' => 'Invalid Paystack configuration'
            ], 500);
        }

        try {
            // Initialize Paystack payment
            $paystack = new Paystack($paystackSecretKey);

            $reference = Str::random(10);
            
            // Log detailed payment initialization information
            Log::info('Paystack Payment Initialization', [
                'user_id' => $user->id,
                'email' => $user->email,
                'amount' => $validatedData['amount'],
                'callback_url' => config('app.frontend_url') . '/paystack-callback',
                'reference' => $reference
            ]);

            $transaction = $paystack->transaction->initialize([
                'amount' => $validatedData['amount'] * 100,
                'email' => $user->email,
                'callback_url' => config('app.url') . '/api/paystack-callback',
                'reference' => $reference,
                'metadata' => [
                    'user_id' => $user->id,
                    'transaction_type' => 'wallet_funding',
                    'is_mobile' => str_contains($request->header('User-Agent'), 'capacitor')
                ]
            ]);

            // Create a pending Paystack transaction
            $paystackTransaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'credit',  // Changed from 'pending_credit'
                'amount' => $validatedData['amount'],
                'description' => 'Paystack Payment',
                'status' => 'pending',
                'payment_method' => 'Paystack',
                'reference_number' => $transaction->data->reference
            ]);

            return response()->json([
                'authorization_url' => $transaction->data->authorization_url,
                'reference' => $transaction->data->reference
            ], 200);
        } catch (\Exception $e) {
            Log::error('Paystack Payment Initiation Failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'amount' => $validatedData['amount']
            ]);
            return response()->json([
                'message' => 'Payment initiation failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function verifyPaystackPayment(Request $request)
    {
        $reference = $request->input('reference');

        if (!$reference) {
            return response()->json([
                'status' => 'error',
                'message' => 'No reference provided'
            ], 400);
        }

        try {
            // First check if transaction is already verified and completed
            $existingTransaction = Transaction::where('reference_number', $reference)
                ->where('status', 'completed')
                ->first();

            if ($existingTransaction) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment already verified',
                    'amount' => $existingTransaction->amount
                ], 200);
            }

            // If not already verified, verify with Paystack
            $paystack = new Paystack(config('services.paystack.secret_key'));
            $transaction = $paystack->transaction->verify([
                'reference' => $reference
            ]);

            // Log the transaction details for debugging
            Log::info('Paystack Transaction Verification', [
                'reference' => $reference,
                'status' => $transaction->data->status,
                'amount' => $transaction->data->amount / 100 // Convert from kobo to naira
            ]);

            // Check transaction status
            if ($transaction->data->status === 'success') {
                // Find the pending transaction
                $pendingTransaction = Transaction::where('reference_number', $reference)
                    ->orWhere('reference_number', null)
                    ->where('status', 'pending')
                    ->first();

                if (!$pendingTransaction) {
                    // Create a new transaction if not found
                    $pendingTransaction = Transaction::create([
                        'user_id' => auth()->id(),
                        'amount' => $transaction->data->amount / 100,
                        'type' => 'credit',
                        'status' => 'pending',
                        'reference_number' => $reference,
                        'description' => 'Paystack Wallet Funding'
                    ]);
                }

                // Start database transaction
                DB::beginTransaction();

                try {
                    // Update transaction status
                    $pendingTransaction->update([
                        'status' => 'completed',
                        'type' => 'credit',
                        'reference_number' => $reference
                    ]);

                    // Update user wallet balance
                    $user = User::find($pendingTransaction->user_id);
                    $user->wallet_balance += $pendingTransaction->amount;
                    $user->save();

                    // Commit database transaction
                    DB::commit();

                    // Log successful transaction
                    Log::info('Paystack Payment Successful', [
                        'user_id' => $user->id,
                        'amount' => $pendingTransaction->amount
                    ]);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Payment verified and wallet funded',
                        'amount' => $pendingTransaction->amount
                    ], 200);

                } catch (\Exception $e) {
                    // Rollback in case of error
                    DB::rollBack();
                    Log::error('Paystack Verification Database Error', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to update wallet'
                    ], 500);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment verification failed'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Paystack Payment Verification Failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadBankTransferProof(Request $request)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:100',
            'paymentProof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120' // 5MB max
        ]);

        $user = auth()->user();

        try {
            // Store the payment proof
            $proofPath = $request->file('paymentProof')->store('bank_transfer_proofs', 'public');

            // Create a pending bank transfer transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'pending_credit',
                'amount' => $validatedData['amount'],
                'description' => 'Bank Transfer Funding',
                'status' => 'pending',
                'payment_method' => 'bank_transfer',
                'reference_number' => 'BT-' . Str::random(10),
                'notes' => $proofPath // Store the proof path in notes
            ]);

            return response()->json([
                'message' => 'Payment proof uploaded successfully. Pending admin verification.',
                'transaction' => $transaction
            ], 200);
        } catch (\Exception $e) {
            Log::error('Bank Transfer Proof Upload Failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to upload payment proof',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function bankTransferPayment(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:bank_transfer',
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120' // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            // Start a database transaction
            DB::beginTransaction();

            // Get the authenticated user
            $user = auth()->user();

            // Generate a unique transaction reference
            $reference = 'BT-' . Str::random(10);

            // Handle file upload with improved naming
            if ($request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                
                // Create a unique filename with user ID, timestamp, and original extension
                $timestamp = now()->format('YmdHis');
                $filename = sprintf(
                    '%s_%s_%s.%s', 
                    $user->id, 
                    $timestamp, 
                    Str::random(6), 
                    $file->getClientOriginalExtension()
                );

                // Store file in a dated subdirectory for better organization
                $path = $file->storeAs(
                    'payment_proofs/' . now()->format('Y/m'), 
                    $filename, 
                    'public'
                );
            } else {
                throw new \Exception('Payment proof is required');
            }

            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'type' => 'credit',
                'payment_method' => 'Bank Transfer',
                'status' => 'pending',
                'reference_number' => $reference, 
                'notes' => $path, // Store full path to the uploaded file
                'description' => 'Bank Transfer Funding'
            ]);

            // Commit the database transaction
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment proof uploaded successfully.',
                'transaction' => $transaction
            ], 200);

        } catch (\Exception $e) {
            // Rollback the transaction in case of any error
            DB::rollBack();

            Log::error('Bank Transfer Payment Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process bank transfer payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handlePaystackCallback(Request $request)
    {
        // Verify the transaction first
        $reference = $request->reference;
        
        try {
            // Initialize Paystack and verify the transaction
            $paystack = new Paystack(config('services.paystack.secret_key'));
            $transaction = $paystack->transaction->verify([
                'reference' => $reference
            ]);

            Log::info('Paystack Callback Received', [
                'reference' => $reference,
                'status' => $transaction->data->status,
                'amount' => $transaction->data->amount / 100
            ]);

            if ($transaction->data->status === 'success') {
                // Find and update the pending transaction
                $pendingTransaction = Transaction::where('reference_number', $reference)
                    ->where('status', 'pending')
                    ->first();

                if ($pendingTransaction) {
                    DB::transaction(function () use ($pendingTransaction, $transaction) {
                        // Update transaction status
                        $pendingTransaction->update([
                            'status' => 'completed',
                            'type' => 'credit'
                        ]);

                        // Update user wallet balance
                        $user = User::find($pendingTransaction->user_id);
                        $user->wallet_balance += $pendingTransaction->amount;
                        $user->save();
                    });
                }

                // Redirect based on platform
                if (str_contains($request->header('User-Agent'), 'capacitor')) {
                    $params = http_build_query([
                        'reference' => $reference,
                        'trxref' => $request->trxref,
                        'status' => 'success'
                    ]);
                    return redirect()->away("tekiplanet://app/paystack-callback?" . $params);
                }

                return redirect()->away(config('app.frontend_url') . "/paystack-callback?" . $params);
            }

            // Handle failed transaction
            throw new \Exception('Payment verification failed');

        } catch (\Exception $e) {
            Log::error('Paystack Callback Error: ' . $e->getMessage());
            
            $errorParams = http_build_query([
                'reference' => $reference,
                'status' => 'failed',
                'message' => $e->getMessage()
            ]);

            if (str_contains($request->header('User-Agent'), 'capacitor')) {
                return redirect()->away("tekiplanet://app/paystack-callback?" . $errorParams);
            }
            return redirect()->away(config('app.frontend_url') . "/paystack-callback?" . $errorParams);
        }
    }
}
