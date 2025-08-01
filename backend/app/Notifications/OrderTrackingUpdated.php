<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class OrderTrackingUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $status;
    protected $location;
    protected $description;

    public function __construct(Order $order, string $status, string $location, string $description)
    {
        $this->order = $order;
        $this->status = $status;
        $this->location = $location;
        $this->description = $description;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->view('emails.orders.tracking-updated', [
                'order' => $this->order,
                'status' => $this->status,
                'location' => $this->location,
                'description' => $this->description,
                'greeting' => "Hello {$notifiable->name}",
                'closing' => 'Thank you for shopping with us!'
            ]);
    }
} 