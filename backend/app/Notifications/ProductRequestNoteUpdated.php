<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ProductRequest;

class ProductRequestNoteUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $productRequest;

    public function __construct(ProductRequest $productRequest)
    {
        $this->productRequest = $productRequest;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Product Request Updated')
            ->view('emails.product-request-note-updated', [
                'productRequest' => $this->productRequest,
                'user' => $notifiable
            ]);
    }
} 