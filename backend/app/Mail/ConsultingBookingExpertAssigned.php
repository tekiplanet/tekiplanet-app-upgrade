<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ConsultingBooking;

class ConsultingBookingExpertAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $isReassignment;

    public function __construct(ConsultingBooking $booking, bool $isReassignment = false)
    {
        $this->booking = $booking;
        $this->isReassignment = $isReassignment;
    }

    public function build()
    {
        return $this->view('emails.consulting.booking-expert-assigned')
            ->subject($this->isReassignment ? 'Expert Reassigned to Your Booking' : 'Expert Assigned to Your Booking');
    }
} 