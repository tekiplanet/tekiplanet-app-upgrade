<?php

namespace App\Mail;

use App\Models\Hustle;
use App\Models\Professional;
use App\Models\HustlePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $hustle;
    public $professional;
    public $payment;

    public function __construct(Hustle $hustle, Professional $professional, HustlePayment $payment)
    {
        $this->hustle = $hustle;
        $this->professional = $professional;
        $this->payment = $payment;
    }

    public function build()
    {
        return $this->subject("New Payment Created: {$this->hustle->title}")
                    ->view('emails.payments.created');
    }
} 