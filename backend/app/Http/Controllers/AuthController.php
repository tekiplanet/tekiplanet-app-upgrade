<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use PragmaRX\Google2FA\Google2FA;
use App\Services\EmailVerificationService;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeVerifiedUser;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            // Validate account type
            'type' => [
                'required', 
                Rule::in(User::$accountTypeOptions)
            ],
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // Save account type
            'account_type' => $request->type,
            'first_name' => $request->first_name ?? null,
            'last_name' => $request->last_name ?? null,
        ]);

        // Create and send verification code
        $verificationService = app(EmailVerificationService::class);
        $verification = $verificationService->createVerification($user);
        $verificationService->sendVerificationEmail($user, $verification);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'requires_verification' => true
        ], 201);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $verificationService = app(EmailVerificationService::class);
        $success = $verificationService->verify($request->user(), $request->code);

        if (!$success) {
            return response()->json([
                'message' => 'Invalid or expired verification code'
            ], 422);
        }

        // Send welcome email after successful verification
        Mail::to($request->user()->email)
            ->queue(new WelcomeVerifiedUser($request->user()));

        return response()->json([
            'message' => 'Email verified successfully',
            'user' => $request->user()
        ]);
    }

    public function resendVerification(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email already verified'
            ], 400);
        }

        $verificationService = app(EmailVerificationService::class);
        $verification = $verificationService->resend($user);

        return response()->json([
            'message' => 'Verification email sent successfully'
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
            'code' => 'nullable|string|size:6'
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($loginField, $request->login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if 2FA is enabled and code is required
        if ($user->two_factor_enabled) {
            // If no code provided, return that 2FA is required
            if (!$request->code) {
                return response()->json([
                    'requires_2fa' => true,
                    'message' => 'Two-factor authentication code required'
                ], 200);
            }

            // Verify 2FA code
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

            if (!$valid) {
                throw ValidationException::withMessages([
                    'code' => ['The provided two-factor code is incorrect.'],
                ]);
            }
        }

        // Revoke all existing tokens for this user
        $user->tokens()->delete();

        // Create a new token
        $token = $user->createToken('login_token')->plainTextToken;

        // Return full user data including wallet_balance
        return response()->json([
            'user' => array_merge(
                $user->makeVisible(['wallet_balance', 'dark_mode', 'two_factor_enabled', 'email_notifications', 'push_notifications', 'marketing_notifications'])->toArray(),
                [
                    'preferences' => [
                        'dark_mode' => $user->dark_mode ?? false,
                        'theme' => $user->dark_mode ? 'dark' : 'light'
                    ]
                ]
            ),
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        $user = $request->user()->load(['professional', 'business_profile']);
        
        // Log::info('User data debug:', [
        //     'user_id' => $user->id,
        //     'professional' => $user->professional?->toArray(),
        //     'business_profile' => $user->business_profile?->toArray(),
        //     'has_professional' => $user->professional !== null,
        //     'has_business' => $user->business_profile !== null,
        //     'two_factor_enabled' => $user->two_factor_enabled
        // ]);

        // Return user data with professional profile
        return response()->json($user->makeVisible([
            'email_notifications',
            'push_notifications',
            'marketing_notifications',
            'profile_visibility',
            'dark_mode',
            'wallet_balance',
            'professional',
            'business_profile'
        ]));
    }
}
