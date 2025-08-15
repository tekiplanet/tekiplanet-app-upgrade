<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GritTypingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gritId;
    public $userId;
    public $senderType;
    public $isTyping;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct($gritId, $userId, $senderType, $isTyping)
    {
        $this->gritId = $gritId;
        $this->userId = $userId;
        $this->senderType = $senderType;
        $this->isTyping = $isTyping;
        
        // Load user data for the frontend
        $this->user = \App\Models\User::find($userId);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('grit.' . $this->gritId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'typing-event';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'grit_id' => $this->gritId,
            'user_id' => $this->userId,
            'sender_type' => $this->senderType,
            'is_typing' => $this->isTyping,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'username' => $this->user->username,
                'avatar' => $this->user->avatar,
            ] : null,
        ];
    }
}
