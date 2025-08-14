<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Grit;
use App\Models\Professional;
use App\Mail\NewGritAvailable;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;

class NotifyProfessionalsAboutNewGrit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    protected $grit;

    public function __construct(Grit $grit)
    {
        $this->grit = $grit;
    }

    public function handle(NotificationService $notificationService)
    {
        try {
            \Log::info('NotifyProfessionalsAboutNewGrit Job Starting:', [
                'grit_id' => $this->grit->id,
                'category_id' => $this->grit->category_id
            ]);

            // Load relationships if not already loaded
            if (!$this->grit->relationLoaded('category')) {
                $this->grit->load(['category']);
            }

            // Get all professionals in this category
            $professionals = Professional::where('category_id', $this->grit->category_id)
                ->with('user')
                ->get();

            \Log::info('Found professionals to notify:', [
                'grit_id' => $this->grit->id,
                'category_id' => $this->grit->category_id,
                'professionals_count' => $professionals->count()
            ]);

            // Prepare notification data
            $notificationData = [
                'type' => 'new_grit_available',
                'title' => 'New GRIT Available',
                'message' => "A new GRIT '{$this->grit->title}' has been posted in your category ({$this->grit->category->name}).",
                'icon' => 'briefcase',
                'action_url' => '/dashboard/grits/' . $this->grit->id,
                'extra_data' => [
                    'grit_id' => $this->grit->id,
                    'category_id' => $this->grit->category_id,
                    'budget' => $this->grit->owner_budget ?? $this->grit->budget,
                    'currency' => $this->grit->owner_currency ?? '₦'
                ]
            ];

            foreach ($professionals as $professional) {
                try {
                    // Send in-app notification
                    $notificationService->send($notificationData, $professional->user);

                    // Send email
                    Mail::to($professional->user->email)
                        ->queue(new NewGritAvailable($this->grit, $professional));

                    \Log::info('Notification sent to professional:', [
                        'professional_id' => $professional->id,
                        'user_id' => $professional->user->id,
                        'email' => $professional->user->email
                    ]);

                } catch (\Exception $e) {
                    \Log::error('Failed to send notification to professional:', [
                        'professional_id' => $professional->id,
                        'user_id' => $professional->user->id,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with other professionals even if one fails
                    continue;
                }
            }

            \Log::info('NotifyProfessionalsAboutNewGrit Job Completed Successfully', [
                'grit_id' => $this->grit->id,
                'professionals_notified' => $professionals->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('NotifyProfessionalsAboutNewGrit Job Failed:', [
                'error' => $e->getMessage(),
                'grit_id' => $this->grit->id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        \Log::error('NotifyProfessionalsAboutNewGrit Job Failed Permanently:', [
            'grit_id' => $this->grit->id,
            'error' => $exception->getMessage()
        ]);
    }
}
