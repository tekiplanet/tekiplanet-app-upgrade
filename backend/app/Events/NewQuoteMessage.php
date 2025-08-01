<?php

namespace App\Events;

use App\Models\QuoteMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewQuoteMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(QuoteMessage $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        \Log::info('Broadcasting message now', [
            'channel' => 'quote.' . $this->message->quote_id,
            'message' => $this->message->message,
            'time' => now()
        ]);
        return new Channel('quote.' . $this->message->quote_id);
    }

    public function broadcastAs()
    {
        return 'new-message';
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message
        ];
    }
} 