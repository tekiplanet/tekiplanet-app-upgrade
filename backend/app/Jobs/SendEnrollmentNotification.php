<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\NotificationService;
use App\Models\User;

class SendEnrollmentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    protected $notificationData;
    protected $user;

    public function __construct(array $notificationData, User $user)
    {
        $this->notificationData = $notificationData;
        $this->user = $user;
    }

    public function handle(NotificationService $notificationService)
    {
        \Log::info('Processing notification job', [
            'user_id' => $this->user->id,
            'notification_type' => $this->notificationData['type']
        ]);

        $notificationService->send($this->notificationData, $this->user);
    }
} 