<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\VerifyEmail;
use App\Models\User;
use App\Models\EmailVerification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendVerificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    protected $user;
    protected $verification;

    public function __construct(User $user, EmailVerification $verification)
    {
        $this->user = $user;
        $this->verification = $verification;
    }

    public function handle()
    {
        try {
            Log::info('Sending verification email', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'verification_id' => $this->verification->id
            ]);

            Mail::to($this->user->email)->send(new VerifyEmail($this->user, $this->verification));

            Log::info('Verification email sent successfully', [
                'user_id' => $this->user->id,
                'email' => $this->user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send verification email', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Verification email job failed', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'error' => $exception->getMessage()
        ]);
    }
} 