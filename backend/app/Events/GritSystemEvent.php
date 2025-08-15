<?php

namespace App\Events;

use App\Models\Grit;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GritSystemEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $event;
    public $message;
    public $metadata;

    public function __construct(string $event, string $message, array $metadata = [])
    {
        $this->event = $event;
        $this->message = $message;
        $this->metadata = $metadata;
    }

    public function broadcastOn()
    {
        return new Channel('grit.' . $this->metadata['grit_id'] ?? 'general');
    }

    public function broadcastAs()
    {
        return 'system-event';
    }

    public function broadcastWith()
    {
        return [
            'event' => $this->event,
            'message' => $this->message,
            'metadata' => $this->metadata,
            'timestamp' => now()->toISOString()
        ];
    }
}
