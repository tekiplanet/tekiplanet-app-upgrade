<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeVerifiedUser;
use App\Services\EmailVerificationService;

class VerificationController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $user = Auth::user();
        $verificationService = app(EmailVerificationService::class);

        try {
            $verified = $verificationService->verify($user, $request->code);
            
            if ($verified) {
                // Send welcome email after verification using Mail facade
                Mail::to($user->email)
                    ->queue(new WelcomeVerifiedUser($user));
                
                return response()->json([
                    'message' => 'Email verified successfully',
                    'user' => $user->fresh()
                ]);
            }

            return response()->json([
                'message' => 'Invalid verification code'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
} 