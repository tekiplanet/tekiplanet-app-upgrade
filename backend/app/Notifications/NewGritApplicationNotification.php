<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Admin;
use App\Models\Grit;
use App\Models\Professional;
use App\Enums\AdminRole;

class NewGritApplicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Grit $grit, private readonly Professional $professional)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $proUser = $this->professional->user;
        $proName = $proUser ? trim(($proUser->first_name ?? '') . ' ' . ($proUser->last_name ?? '')) : 'A professional';

        return (new MailMessage)
            ->subject('New GRIT Application: ' . $this->grit->title)
            ->greeting('Hello Admin')
            ->line($proName . ' has applied to the GRIT: ' . $this->grit->title)
            ->line('Category: ' . ($this->grit->category->name ?? ''))
            ->action('View GRIT', config('app.frontend_url') . '/#/dashboard/grits/' . $this->grit->id)
            ->line('This is an automated message.');
    }

    public function toArray($notifiable): array
    {
        return [
            'grit_id' => $this->grit->id,
            'title' => $this->grit->title,
            'professional_id' => $this->professional->id,
        ];
    }

    public static function notifyAdmins(Grit $grit, Professional $professional): void
    {
        Admin::query()
            ->whereIn('role', [AdminRole::SUPER_ADMIN, AdminRole::SALES])
            ->where('is_active', true)
            ->get()
            ->each(function (Admin $admin) use ($grit, $professional) {
                $admin->notify(new static($grit->loadMissing('category'), $professional));
            });
    }
}


