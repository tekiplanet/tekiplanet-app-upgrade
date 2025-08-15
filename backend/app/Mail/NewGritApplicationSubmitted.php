<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Grit;
use App\Models\Professional;

class NewGritApplicationSubmitted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $grit;
    public $professional;

    public function __construct(Grit $grit, Professional $professional)
    {
        $this->grit = $grit;
        $this->professional = $professional;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Application for: ' . $this->grit->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.grits.new-application',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}


