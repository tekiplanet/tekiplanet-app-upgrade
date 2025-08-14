<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Grit;
use App\Mail\GritApproved;
use App\Mail\GritRejected;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;

class SendGritNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    protected $grit;
    protected $action;
    protected $reason;

    public function __construct(Grit $grit, string $action, string $reason = null)
    {
        $this->grit = $grit;
        $this->action = $action;
        $this->reason = $reason;
    }

    public function handle(NotificationService $notificationService)
    {
        try {
            \Log::info('SendGritNotification Job Starting:', [
                'action' => $this->action,
                'grit_id' => $this->grit->id,
                'user_id' => $this->grit->user->id ?? 'no user'
            ]);

            // Load relationships if not already loaded
            if (!$this->grit->relationLoaded('user')) {
                $this->grit->load(['user', 'category']);
            }

            // Send email based on action
            if ($this->action === 'approved') {
                Mail::to($this->grit->user->email)
                    ->queue(new GritApproved($this->grit));
            } elseif ($this->action === 'rejected') {
                Mail::to($this->grit->user->email)
                    ->queue(new GritRejected($this->grit, $this->reason));
            }

            // Create notification data based on action type
            $notificationData = $this->getNotificationData();

            \Log::info('Sending notification:', [
                'type' => $notificationData['type'],
                'title' => $notificationData['title'],
                'message' => $notificationData['message']
            ]);

            // Send in-app notification
            $notificationService->send($notificationData, $this->grit->user);

            \Log::info('SendGritNotification Job Completed Successfully');

        } catch (\Exception $e) {
            \Log::error('SendGritNotification Job Failed:', [
                'error' => $e->getMessage(),
                'grit_id' => $this->grit->id,
                'action' => $this->action
            ]);
            throw $e;
        }
    }

    protected function getNotificationData(): array
    {
        if ($this->action === 'approved') {
            return [
                'type' => 'grit_approved',
                'title' => 'GRIT Approved',
                'message' => "Your GRIT '{$this->grit->title}' has been approved and is now visible to professionals.",
                'icon' => 'check-circle',
                'action_url' => '/dashboard/grits/mine',
                'extra_data' => [
                    'grit_id' => $this->grit->id,
                    'category_id' => $this->grit->category_id,
                    'status' => 'approved'
                ]
            ];
        } else {
            return [
                'type' => 'grit_rejected',
                'title' => 'GRIT Rejected',
                'message' => "Your GRIT '{$this->grit->title}' has been rejected." . 
                            ($this->reason ? " Reason: {$this->reason}" : ""),
                'icon' => 'x-circle',
                'action_url' => '/dashboard/grits/mine',
                'extra_data' => [
                    'grit_id' => $this->grit->id,
                    'category_id' => $this->grit->category_id,
                    'status' => 'rejected',
                    'reason' => $this->reason
                ]
            ];
        }
    }

    public function failed(\Throwable $exception)
    {
        \Log::error('SendGritNotification Job Failed Permanently:', [
            'grit_id' => $this->grit->id,
            'action' => $this->action,
            'error' => $exception->getMessage()
        ]);
    }
}
