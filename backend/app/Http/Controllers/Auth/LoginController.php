<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use App\Services\EmailVerificationService;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Validate credentials
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // Determine login type (email or username)
        $loginType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        // Prepare credentials for authentication
        $authCredentials = [
            $loginType => $credentials['login'],
            'password' => $credentials['password']
        ];

        // Attempt authentication
        if (!Auth::attempt($authCredentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        // Check if email is verified
        if (!$user->email_verified_at) {
            $token = $user->createToken('auth-token')->plainTextToken;
            return response()->json([
                'message' => 'Email verification required',
                'requires_verification' => true,
                'email' => $user->email,
                'token' => $token
            ], 403);
        }

        // Check if 2FA is enabled
        if ($user->two_factor_enabled) {
            // If no code provided, return that 2FA is required
            $code = $request->input('code');
            if (!$code) {
                return response()->json([
                    'requires_2fa' => true,
                    'message' => 'Two-factor authentication code required',
                    'user' => [
                        'email' => $user->email
                    ]
                ], 200);
            }

            // Verify 2FA code
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->two_factor_secret, $code);

            if (!$valid) {
                return response()->json([
                    'message' => 'Invalid two-factor authentication code'
                ], 422);
            }
        }
        
        // Generate a token for the user
        $token = $user->createToken('login_token')->plainTextToken;

        Log::info('Login successful', [
            'ip' => $request->ip(),
            'login_field' => $request->input('login'),
            'user_id' => $user->id,
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        return response()->json([
            'token' => $token,
            'user' => $user->makeVisible([
                'wallet_balance', 'dark_mode', 'two_factor_enabled', 
                'email_notifications', 'push_notifications', 
                'marketing_notifications', 'created_at', 'updated_at',
                'email_verified_at', 'timezone', 'bio', 'profile_visibility',
                'last_login_at', 'last_login_ip'
            ])->toArray()
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
