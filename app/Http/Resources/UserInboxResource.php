<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\{User};

class UserInboxResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        $recipient_id = $user->id == (int)($this->user1) ? (int)($this->user2) : (int)($this->user1);
        $recipient = User::where('id',$recipient_id)->first();
        return [
            'username' => $recipient->username,
            'last_message' => $this->message,
            'last_updated_at' => $this->created_at
        ];
    }
}
