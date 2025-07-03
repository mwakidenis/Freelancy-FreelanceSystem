<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\{User};
use Str;

class ProposalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $freelancer = User::where('id',$this->freelancer_id)->first();
        return [
            'id' => $this->id,
            'freelancer_name' => $freelancer->name,
            'freelancer_username' => $freelancer->username,
            'description' => $this->description,    
            'status' => in_array($this->status,['in progress','done']) ? 'Accepted' : Str::title($this->status),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
    ];
    }
}
