<?php

namespace App\Notifications;

class TestPushNotification extends BaseNotification
{
    public function __construct()
    {
        $this->title = 'Test Notification';
        $this->message = 'This is a test push notification';
        $this->type = 'test';
        $this->url = '/dashboard';
    }
} 