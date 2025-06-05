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
use App\Models\{Message,User};

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

        $channels = [];
        $channels[] = new PrivateChannel("chat.{$ids[0]}.{$ids[1]}");
        $channels[] = new PrivateChannel("inbox.{$this->message->recipient_id}");

        if ($this->message->recipient_id !== $this->message->sender_id) {
            $channels[] = new PrivateChannel("inbox.{$this->message->sender_id}");
        }

        return $channels;
    }


    public function broadcastWith()
    {
        $sender = User::where('id',$this->message->sender_id)->first();
        $recipient = User::where('id',$this->message->recipient_id)->first();
        return [
            'sender' => $sender->username,
            'recipient' => $recipient->username,
            'message' => $this->message->message,
            'sent_at' => $this->message->created_at
        ];
    }
}
