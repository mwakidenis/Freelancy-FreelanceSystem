<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\{User,Profile};

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = Profile::where('user_id',$this->id)->first();    
        $additional = match ($this->role) {
            'client' => ['number_of_projects' => $this->number_of_projects ?? 0],
            'freelancer' => ['number_of_freelances' => $this->number_of_freelances ?? 0],
            default => []
        };
    
        return array_merge([
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'user_type' => $this->role,
            'date_registered' => $this->created_at,
            'brief_description' => $profile ?? null,
            'educational_attainment' => $profile ?? null,
            'work_experience' => $profile ?? null,
            'skills' => $profile ?? null
        ], $additional);
    }
}
