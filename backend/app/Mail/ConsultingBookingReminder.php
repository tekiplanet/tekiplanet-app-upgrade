<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ConsultingBooking;

class ConsultingBookingReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $timeUntil;
    public $note;

    public function __construct(ConsultingBooking $booking, string $timeUntil, ?string $note)
    {
        $this->booking = $booking;
        $this->timeUntil = $timeUntil;
        $this->note = $note;
    }

    public function build()
    {
        return $this->view('emails.consulting.booking-reminder')
            ->subject('Reminder: Upcoming Consulting Session');
    }
} 