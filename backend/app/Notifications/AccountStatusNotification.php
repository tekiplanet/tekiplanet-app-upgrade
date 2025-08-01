<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $status;

    public function __construct($status)
    {
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Account Status Updated')
            ->view('components.mail.layout', [
                'slot' => new \Illuminate\Support\HtmlString(
                    view('emails.account-status', [
                        'user' => $notifiable,
                        'status' => $this->status,
                        'actionText' => 'View Dashboard',
                        'actionUrl' => url('/dashboard')
                    ])->render()
                ),
                'greeting' => 'Hello ' . $notifiable->first_name . '!',
                'closing' => 'Best regards, TekiPlanet Team'
            ]);
    }
} 