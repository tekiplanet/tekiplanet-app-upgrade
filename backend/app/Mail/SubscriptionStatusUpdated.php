<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\WorkstationSubscription;

class SubscriptionStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $subscription;
    public $status;
    public $user;

    public function __construct(WorkstationSubscription $subscription, string $status)
    {
        $this->subscription = $subscription;
        $this->status = $status;
        $this->user = $subscription->user;
    }

    public function build()
    {
        return $this->subject('Workstation Subscription Status Updated')
                    ->view('emails.subscription-status-updated');
    }
} 