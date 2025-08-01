<?php

namespace App\Mail;

use App\Models\Hustle;
use App\Models\Professional;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $hustle;
    public $professional;

    public function __construct(Hustle $hustle, Professional $professional)
    {
        $this->hustle = $hustle;
        $this->professional = $professional;
    }

    public function build()
    {
        return $this->subject("Application Update: {$this->hustle->title}")
                    ->view('emails.applications.rejected');
    }
} 