<?php

namespace App\Events;

use App\Models\HustleMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewHustleMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(HustleMessage $message)
    {
        $this->message = [
            'id' => $message->id,
            'message' => $message->message,
            'sender_type' => $message->sender_type,
            'sender_name' => $message->user->full_name,
            'sender_avatar' => $message->user->avatar,
            'created_at' => $message->created_at->diffForHumans(),
            'is_admin' => $message->sender_type === 'admin',
            'hustle_id' => $message->hustle_id
        ];
    }

    public function broadcastOn()
    {
        return new PrivateChannel('hustle.'.$this->message['hustle_id']);
    }
} 