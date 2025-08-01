<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransactionStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;
    public $user;

    public function __construct($transaction, $user)
    {
        $this->transaction = $transaction;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Transaction Status Updated')
                    ->view('emails.transaction-status-updated');
    }
} 