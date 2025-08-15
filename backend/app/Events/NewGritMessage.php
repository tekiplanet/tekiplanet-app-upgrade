<?php

namespace App\Events;

use App\Models\GritMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewGritMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(GritMessage $message)
    {
        $this->message = [
            'id' => $message->id,
            'grit_id' => $message->grit_id,
            'user_id' => $message->user_id,
            'message' => $message->message,
            'sender_type' => $message->sender_type,
            'is_read' => $message->is_read,
            'created_at' => $message->created_at,
            'user' => $message->user ? [
                'id' => $message->user->id,
                'name' => $message->user->first_name . ' ' . $message->user->last_name,
                'first_name' => $message->user->first_name,
                'last_name' => $message->user->last_name,
                'username' => $message->user->username,
                'avatar' => $message->user->avatar,
            ] : null
        ];
    }

    public function broadcastOn()
    {
        return new Channel('grit.' . $this->message['grit_id']);
    }

    public function broadcastAs()
    {
        return 'new-message';
    }
}
