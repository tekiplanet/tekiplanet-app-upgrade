<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Events\NewNotification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage as FirebaseMessage;

class NotificationService
{
    public function send($data, $users)
    {
        // Create the notification
        $notification = Notification::create([
            'type' => $data['type'],
            'title' => $data['title'],
            'message' => $data['message'],
            'icon' => $data['icon'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'data' => $data['extra_data'] ?? null,
        ]);

        // If users is a single user, convert to array
        $users = is_array($users) ? $users : [$users];

        // Attach notification to users and queue broadcasts
        foreach ($users as $user) {
            $notification->users()->attach($user->id, [
                'id' => Str::uuid(),
                'read' => false
            ]);

            // Queue the broadcast event
            dispatch(function () use ($notification, $user) {
                event(new NewNotification($notification, $user));
            })->onQueue('default');

            // Send FCM notification if user has device tokens
            if ($user->deviceTokens()->exists()) {
                $this->sendFcmNotification($data, $user);
            }
        }

        return $notification;
    }

    protected function sendFcmNotification($data, $user)
    {
        try {
            $deviceTokens = $user->deviceTokens()->pluck('token')->toArray();
            
            if (empty($deviceTokens)) {
                Log::info('No device tokens found for user', ['user_id' => $user->id]);
                return;
            }

            $messaging = app('firebase.messaging');
            
            $message = FirebaseMessage::withTarget('token', $deviceTokens[0])
                ->withNotification([
                    'title' => $data['title'],
                    'body' => $data['message'],
                    'image' => null
                ])
                ->withData($data['extra_data'] ?? []);

            $response = $messaging->send($message);
            
            Log::info('FCM notification sent successfully', [
                'user_id' => $user->id,
                'response' => $response
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('FCM notification failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            throw $e;
        }
    }
} 