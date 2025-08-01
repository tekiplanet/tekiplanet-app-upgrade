<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $title;
    public $message;
    public $type;
    public $emailContent;

    public function __construct($title, $message, $type, $emailContent = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->emailContent = $emailContent ?? $message;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->title)
            ->view('components.mail.layout', [
                'slot' => $this->emailContent,
                'greeting' => 'Hello ' . $notifiable->name . '!',
                'closing' => 'Best regards, TekiPlanet Team'
            ]);
    }
} 