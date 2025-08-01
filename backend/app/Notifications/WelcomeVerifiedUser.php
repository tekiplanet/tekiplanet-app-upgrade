<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WelcomeVerifiedUser extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3; // Number of times to attempt sending
    public $timeout = 60; // Seconds before timing out

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        Log::info('Preparing welcome email for user', [
            'user_id' => $notifiable->id,
            'email' => $notifiable->email
        ]);

        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name') . ' - Your Tech Journey Begins!')
            ->view('emails.welcome-verified', ['user' => $notifiable]);
    }

    /**
     * Handle a notification failure.
     */
    public function failed(\Exception $e)
    {
        Log::error('Welcome email notification failed', [
            'error' => $e->getMessage()
        ]);
    }
} 