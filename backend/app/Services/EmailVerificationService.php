<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailVerification;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;

class EmailVerificationService
{
    public function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function createVerification(User $user): EmailVerification
    {
        // Invalidate any existing verification codes
        EmailVerification::where('user_id', $user->id)
            ->whereNull('verified_at')
            ->delete();

        // Create new verification
        return EmailVerification::create([
            'user_id' => $user->id,
            'code' => $this->generateCode(),
            'expires_at' => Carbon::now()->addMinutes(20)
        ]);
    }

    public function sendVerificationEmail(User $user, EmailVerification $verification)
    {
        Mail::to($user->email)->send(new VerifyEmail($user, $verification));
    }

    public function verify(User $user, string $code): bool
    {
        $verification = EmailVerification::where('user_id', $user->id)
            ->where('code', $code)
            ->whereNull('verified_at')
            ->first();

        if (!$verification || $verification->isExpired()) {
            return false;
        }

        // Mark email as verified
        $user->email_verified_at = now();
        $user->save();

        // Mark verification as complete
        $verification->verified_at = now();
        $verification->save();

        return true;
    }

    public function resend(User $user): EmailVerification
    {
        $verification = $this->createVerification($user);
        $this->sendVerificationEmail($user, $verification);
        return $verification;
    }
} 