<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Grit;
use App\Models\Professional;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewGritApplicationSubmitted;
use App\Notifications\NewGritApplicationNotification;

class SendGritApplicationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    public function __construct(private readonly Grit $grit, private readonly Professional $professional)
    {
    }

    public function handle(NotificationService $notificationService): void
    {
        try {
            Log::info('SendGritApplicationNotification starting', [
                'grit_id' => $this->grit->id,
                'professional_id' => $this->professional->id,
            ]);

            $grit = $this->grit->loadMissing(['user', 'category']);

            // If the GRIT has an owner user, notify the owner (email + in-app/push)
            if ($grit->user) {
                Mail::to($grit->user->email)
                    ->queue(new NewGritApplicationSubmitted($grit, $this->professional));

                $notificationService->send([
                    'type' => 'grit_application_received',
                    'title' => 'New GRIT Application',
                    'message' => 'You have a new application for ' . $grit->title,
                    'icon' => 'users',
                    'action_url' => '/dashboard/grits/' . $grit->id,
                    'extra_data' => [
                        'grit_id' => $grit->id,
                        'professional_id' => $this->professional->id,
                    ],
                ], $grit->user);
            } else {
                // Admin-created GRIT: notify admins (mail + database)
                NewGritApplicationNotification::notifyAdmins($grit, $this->professional);
            }

            Log::info('SendGritApplicationNotification completed');
        } catch (\Throwable $e) {
            Log::error('SendGritApplicationNotification failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}


