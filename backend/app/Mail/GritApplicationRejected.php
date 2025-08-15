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

class GritApplicationRejected extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $grit;
    public $professional;
    public $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Grit $grit, Professional $professional, string $reason = null)
    {
        $this->grit = $grit;
        $this->professional = $professional;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Application Update - ' . $this->grit->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.applications.rejected',
            with: [
                'grit' => $this->grit,
                'professional' => $this->professional,
                'reason' => $this->reason,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
