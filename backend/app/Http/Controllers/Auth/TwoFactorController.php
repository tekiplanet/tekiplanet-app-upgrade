<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function enable(Request $request)
    {
        $user = $request->user();

        // Check if 2FA is already enabled
        if ($user->two_factor_enabled) {
            return response()->json([
                'message' => 'Two-factor authentication is already enabled'
            ], 400);
        }

        // Generate the secret key
        $secret = $this->google2fa->generateSecretKey();

        // Generate recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = Str::random(10);
        }

        // Store the secret and recovery codes
        $user->two_factor_secret = $secret;
        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->save();

        // Generate the QR code URL
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return response()->json([
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
            'recovery_codes' => $recoveryCodes
        ]);
    }

    public function verify(Request $request)
    {
        // Add debug logging
        \Log::info('2FA Verification Request:', $request->all());
        
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6'
        ]);

        // Find user by email
        $user = \App\Models\User::where('email', $validated['email'])->first();
        
        \Log::info('User found:', ['user_id' => $user?->id, 'has_2fa' => $user?->two_factor_enabled]);
        
        if (!$user || !$user->two_factor_enabled) {
            return response()->json([
                'message' => 'Invalid verification attempt'
            ], 400);
        }

        // Verify 2FA code
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->two_factor_secret, $validated['code']);

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid two-factor authentication code'
            ], 422);
        }

        // Generate token
        $token = $user->createToken('auth-token')->plainTextToken;

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

    public function disable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = $request->user();

        if (!$user->two_factor_enabled) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled'
            ], 400);
        }

        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid authentication code'
            ], 400);
        }

        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        return response()->json([
            'message' => 'Two-factor authentication disabled successfully'
        ]);
    }

    public function validateCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = $request->user();
        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->code);

        return response()->json([
            'valid' => $valid
        ]);
    }

    public function validateRecoveryCode(Request $request)
    {
        $request->validate([
            'recovery_code' => 'required|string'
        ]);

        $user = $request->user();
        $recoveryCodes = $user->two_factor_recovery_codes ?? [];
        
        $valid = in_array($request->recovery_code, $recoveryCodes);

        if ($valid) {
            // Remove used recovery code
            $recoveryCodes = array_diff($recoveryCodes, [$request->recovery_code]);
            $user->two_factor_recovery_codes = array_values($recoveryCodes);
            $user->save();
        }

        return response()->json([
            'valid' => $valid
        ]);
    }

    public function generateRecoveryCodes(Request $request)
    {
        $user = $request->user();

        if (!$user->two_factor_enabled) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled'
            ], 400);
        }

        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = Str::random(10);
        }

        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->save();

        return response()->json([
            'recovery_codes' => $recoveryCodes
        ]);
    }

    public function getRecoveryCodes(Request $request)
    {
        $user = $request->user();

        if (!$user->two_factor_enabled) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled'
            ], 400);
        }

        return response()->json([
            'recovery_codes' => $user->two_factor_recovery_codes
        ]);
    }

    public function verifySetup(Request $request)
    {
        \Log::info('2FA Setup Verification Request:', [
            'code' => $request->code,
            'user' => $request->user(),
            'secret' => $request->user()->two_factor_secret,
            'endpoint' => 'verifySetup'
        ]);

        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = $request->user();
        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->code);

        \Log::info('2FA Setup Verification Result:', [
            'valid' => $valid,
            'code' => $request->code,
            'secret' => $user->two_factor_secret
        ]);

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid authentication code'
            ], 422);
        }

        $user->two_factor_enabled = true;
        $user->save();

        return response()->json([
            'message' => 'Two-factor authentication enabled successfully'
        ]);
    }
}
