<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class OrderStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $status;
    protected $notes;

    public function __construct(Order $order, string $status, ?string $notes)
    {
        $this->order = $order;
        $this->status = $status;
        $this->notes = $notes;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->view('emails.orders.status-updated', [
                'order' => $this->order,
                'status' => $this->status,
                'notes' => $this->notes,
                'greeting' => "Hello {$notifiable->name}",
                'closing' => 'Thank you for shopping with us!'
            ]);
    }
} 