<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\{Message};

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $ids = [$this->message->sender_id, $this->message->recipient_id];
        sort($ids);
        // Log::info('Broadcasting on channel: chat.' . $ids[0] . '.' . $ids[1]);
        return [
            new PrivateChannel("chat.{$ids[0]}.{$ids[1]}"),
        ];
    }


    public function broadcastWith()
    {
        // Log::info("Message object is:".$this->message);
        // Log::info("Message property is:".$this->message->message);
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'recipient_id' => $this->message->recipient_id,
            'message' => $this->message->message, // <-- Ensure there's no missing comma here
            'created_at' => $this->message->created_at // <-- Also check if this line has the correct syntax
        ];
    }
}
