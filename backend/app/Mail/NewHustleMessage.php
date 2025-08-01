<?php

namespace App\Mail;

use App\Models\Hustle;
use App\Models\User;
use App\Models\HustleMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewHustleMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $hustle;
    public $recipient;
    public $messageContent;

    public function __construct(Hustle $hustle, User $recipient, HustleMessage $message)
    {
        $this->hustle = $hustle;
        $this->recipient = $recipient;
        $this->messageContent = $message->message;
    }

    public function build()
    {
        return $this->subject("New Message: {$this->hustle->title}")
                    ->view('emails.messages.new-message');
    }
} 