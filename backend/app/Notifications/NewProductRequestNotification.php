<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Admin;
use App\Enums\AdminRole;

class NewProductRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $productRequest;
    private $greeting;

    public function __construct($productRequest)
    {
        $this->productRequest = $productRequest;
        $this->greeting = "Hello Admin";
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Product Request Submitted')
            ->view('emails.new-product-request', [
                'greeting' => $this->greeting,
                'productRequest' => $this->productRequest
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'product_request_id' => $this->productRequest->id,
            'product_name' => $this->productRequest->product_name,
            'user_id' => $this->productRequest->user_id,
        ];
    }

    public static function notifyAdmins($productRequest)
    {
        Admin::query()
            ->whereIn('role', [AdminRole::SUPER_ADMIN, AdminRole::SALES])
            ->where('is_active', true)
            ->get()
            ->each(function ($admin) use ($productRequest) {
                $admin->notify(new static($productRequest));
            });
    }
} 