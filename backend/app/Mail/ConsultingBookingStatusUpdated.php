<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ConsultingBooking;

class ConsultingBookingStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $oldStatus;

    public function __construct(ConsultingBooking $booking, string $oldStatus)
    {
        $this->booking = $booking;
        $this->oldStatus = $oldStatus;
    }

    public function build()
    {
        $subject = match($this->booking->status) {
            'confirmed' => 'Booking Confirmed',
            'ongoing' => 'Consulting Session Started',
            'completed' => 'Consulting Session Completed',
            default => 'Booking Status Updated'
        };

        return $this->view('emails.consulting.booking-status-updated')
            ->subject($subject);
    }
} 