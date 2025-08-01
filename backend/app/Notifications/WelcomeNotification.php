<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->view('emails.welcome', ['user' => $notifiable]);
    }
} 