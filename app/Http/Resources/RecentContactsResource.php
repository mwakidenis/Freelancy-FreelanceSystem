<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\{User};

class RecentContactsResource extends JsonResource
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
        ];
    }
}
