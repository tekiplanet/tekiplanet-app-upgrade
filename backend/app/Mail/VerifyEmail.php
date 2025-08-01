<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\EmailVerification;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public EmailVerification $verification
    ) {}

    public function build()
    {
        return $this->view('emails.verify-email')
            ->with([
                'user' => $this->user,
                'verificationCode' => $this->verification->code,
                'expiresIn' => round($this->verification->expires_at->diffInMinutes() / 60, 1)
            ]);
    }
} 