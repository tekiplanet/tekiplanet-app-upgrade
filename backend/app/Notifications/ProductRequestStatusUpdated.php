<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ProductRequest;

class ProductRequestStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $productRequest;
    protected $oldStatus;

    public function __construct(ProductRequest $productRequest, string $oldStatus)
    {
        $this->productRequest = $productRequest;
        $this->oldStatus = $oldStatus;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Product Request Status Updated')
            ->view('emails.product-request-status-updated', [
                'productRequest' => $this->productRequest,
                'oldStatus' => $this->oldStatus,
                'user' => $notifiable
            ]);
    }
} 