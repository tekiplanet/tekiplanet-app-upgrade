<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Log;
use App\Mail\PasswordChangedMail;

class PasswordResetController extends Controller
{
    public function sendResetCode(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email'
            ]);

            // Generate 6-digit code
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            Log::info('Starting password reset process', [
                'email' => $request->email
            ]);

            // Invalidate any existing codes
            PasswordReset::where('email', $request->email)
                ->where('used', false)
                ->update(['used' => true]);

            // Create new reset record
            PasswordReset::create([
                'email' => $request->email,
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(30),
            ]);

            try {
                // Queue the email instead of sending synchronously
                Log::info('Queueing password reset email', [
                    'to' => $request->email,
                    'code' => $code
                ]);
                
                Mail::to($request->email)
                    ->queue(new PasswordResetMail($code));
                    
                Log::info('Password reset email queued successfully');
            } catch (\Exception $e) {
                Log::error('Mail queueing failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'email_config' => [
                        'driver' => config('mail.default'),
                        'host' => config('mail.mailers.smtp.host'),
                        'port' => config('mail.mailers.smtp.port'),
                        'from_address' => config('mail.from.address'),
                    ]
                ]);
                return response()->json([
                    'message' => 'Failed to send reset code. Please try again.'
                ], 500);
            }

            return response()->json([
                'message' => 'Password reset code sent successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Password reset process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to process password reset request'
            ], 500);
        }
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6'
        ]);

        $reset = PasswordReset::where('email', $request->email)
            ->where('code', $request->code)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$reset) {
            return response()->json([
                'message' => 'Invalid or expired code'
            ], 422);
        }

        return response()->json([
            'message' => 'Code verified successfully'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $reset = PasswordReset::where('email', $request->email)
            ->where('code', $request->code)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$reset) {
            return response()->json([
                'message' => 'Invalid or expired code'
            ], 422);
        }

        try {
            // Update password
            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            // Mark reset code as used
            $reset->used = true;
            $reset->save();

            // Queue password changed notification email
            Mail::to($request->email)
                ->queue(new PasswordChangedMail($request->ip()));

            Log::info('Password changed notification queued', [
                'email' => $request->email,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'message' => 'Password reset successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to reset password'
            ], 500);
        }
    }
} 