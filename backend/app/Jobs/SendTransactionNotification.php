<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\NotificationService;

class SendTransactionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notificationData;
    protected $user;

    public function __construct($notificationData, $user)
    {
        $this->notificationData = $notificationData;
        $this->user = $user;
    }

    public function handle(NotificationService $notificationService)
    {
        $notificationService->send($this->notificationData, $this->user);
    }
} 