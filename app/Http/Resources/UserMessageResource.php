<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\{User};

class UserMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sender = User::where('id',(int)($this->sender_id))->first();
        $recipient = User::where('id',(int)($this->recipient_id))->first();

        return [
            'sender' => $sender->username,
            'recipient' => $recipient->username,
            'message' => $this->message,
            'sent_at' => $this->created_at
        ];
    }
}
