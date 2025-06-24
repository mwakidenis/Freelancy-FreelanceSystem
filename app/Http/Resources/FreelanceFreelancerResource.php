<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\{User};
use Str;

class FreelanceFreelancerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = User::where('id',$this->client_id)->first();
        return [
            'freelance_project_details' => [
                'id' => $this->id,
                'title' => Str::title($this->title),
                'slug' => $this->slug,
                'category' => Str::title($this->category),
                'description' => $this->description,
                'rate' => $this->rate,
                'status' => Str::title($this->status),
                'number_of_total_proposals' => $this->number_of_proposal ?: 0,
                'number_of_pending_proposals' => $this->number_of_pending ?: 0,
                'number_of_declined_proposals' => $this->number_of_declined ?: 0,
                'date_posted' => $this->created_at->toISOString(),
                'date_updated' => $this->updated_at
            ],
            'client_details' => [   
                'client_name' => $user ? $user->name : '',
                'client_username' => $user ? $user->username : '',
                'number_of_total_projects' => $this->number_of_projects ?: 0,
                'date_registered' => $user ? $user->created_at->toISOString() : '',
            ]
        ];
    }
}
