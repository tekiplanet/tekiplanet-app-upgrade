<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidNotification;

class BaseNotification extends Notification
{
    protected $title;
    protected $message;
    protected $type;
    protected $url;

    public function via($notifiable)
    {
        return ['database', 'fcm'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'icon' => 'bell',
            'action_url' => $this->url,
            'data' => [
                'extra_data' => [
                    'test' => true
                ]
            ]
        ];
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setData([
                'url' => $this->url ?? '',
                'type' => $this->type ?? 'default',
            ])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($this->title)
                ->setBody($this->message))
            ->setAndroid(
                AndroidConfig::create()
                    ->setNotification(AndroidNotification::create()
                        ->setColor('#0066FF')
                        ->setIcon('notification_icon'))
            );
    }
} 