<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\NotificationService;
use App\Models\User;

class SendCourseNoticeNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notificationData;
    protected $user;

    public function __construct(array $notificationData, User $user)
    {
        $this->notificationData = $notificationData;
        $this->user = $user;
    }

    public function handle(NotificationService $notificationService)
    {
        $notificationService->send($this->notificationData, $this->user);
    }
} 