<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ConsultingBooking;

class ConsultingBookingCancelled extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(ConsultingBooking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        return $this->view('emails.consulting.booking-cancelled')
            ->subject('Consulting Booking Cancelled');
    }
} 