<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $ipAddress;
    public $datetime;

    public function __construct($ipAddress)
    {
        $this->ipAddress = $ipAddress;
        $this->datetime = now()->format('F j, Y \a\t g:i a');
    }

    public function build()
    {
        return $this->view('emails.password-changed')
                    ->subject('Your Password Has Been Changed');
    }

    public function failed(\Throwable $exception)
    {
        \Log::error('Password changed notification email failed to send', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
} 