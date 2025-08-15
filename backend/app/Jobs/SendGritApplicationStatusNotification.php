<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\GritApplication;
use App\Mail\GritApplicationApproved;
use App\Mail\GritApplicationRejected;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendGritApplicationStatusNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    protected $application;
    protected $action;
    protected $reason;

    public function __construct(GritApplication $application, string $action, string $reason = null)
    {
        $this->application = $application;
        $this->action = $action;
        $this->reason = $reason;
    }

    public function handle(NotificationService $notificationService)
    {
        try {
            Log::info('SendGritApplicationStatusNotification Job Starting:', [
                'action' => $this->action,
                'application_id' => $this->application->id,
                'grit_id' => $this->application->grit_id,
                'professional_id' => $this->application->professional_id
            ]);

            // Load relationships if not already loaded
            if (!$this->application->relationLoaded('grit')) {
                $this->application->load(['grit.category', 'professional.user']);
            }

            // Send email based on action
            if ($this->action === 'approved') {
                Mail::to($this->application->professional->user->email)
                    ->queue(new GritApplicationApproved($this->application->grit, $this->application->professional));
            } elseif ($this->action === 'rejected') {
                Mail::to($this->application->professional->user->email)
                    ->queue(new GritApplicationRejected($this->application->grit, $this->application->professional, $this->reason));
            }

            // Create notification data based on action type
            $notificationData = $this->getNotificationData();

            Log::info('Sending notification:', [
                'type' => $notificationData['type'],
                'title' => $notificationData['title'],
                'message' => $notificationData['message']
            ]);

            // Send in-app notification
            $notificationService->send($notificationData, $this->application->professional->user);

            Log::info('SendGritApplicationStatusNotification Job Completed Successfully');

        } catch (\Exception $e) {
            Log::error('SendGritApplicationStatusNotification Job Failed:', [
                'error' => $e->getMessage(),
                'application_id' => $this->application->id,
                'action' => $this->action
            ]);
            throw $e;
        }
    }

    protected function getNotificationData(): array
    {
        if ($this->action === 'approved') {
            return [
                'type' => 'grit_application_approved',
                'title' => 'Application Approved',
                'message' => "Your application for '{$this->application->grit->title}' has been approved!",
                'icon' => 'check-circle',
                'action_url' => '/dashboard/grits/' . $this->application->grit_id,
                'extra_data' => [
                    'grit_id' => $this->application->grit_id,
                    'application_id' => $this->application->id,
                    'status' => 'approved'
                ]
            ];
        } else {
            return [
                'type' => 'grit_application_rejected',
                'title' => 'Application Update',
                'message' => "Your application for '{$this->application->grit->title}' was not selected." . 
                            ($this->reason ? " Reason: {$this->reason}" : ""),
                'icon' => 'x-circle',
                'action_url' => '/dashboard/grits',
                'extra_data' => [
                    'grit_id' => $this->application->grit_id,
                    'application_id' => $this->application->id,
                    'status' => 'rejected',
                    'reason' => $this->reason
                ]
            ];
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('SendGritApplicationStatusNotification Job Failed Permanently:', [
            'application_id' => $this->application->id,
            'action' => $this->action,
            'error' => $exception->getMessage()
        ]);
    }
}
