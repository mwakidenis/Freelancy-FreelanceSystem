<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\{Freelance,User};
use Str;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $freelance = Freelance::where('id',$this->freelance_id)->first();
        $client = User::where('id',$freelance->client_id)->first();
        $freelancer = User::where('id',$this->freelancer_id)->first();
        return [
            'freelance_project_details' => [
                'id' => $this->freelance_id,
                'client_name' => $client->name,
                'client_username' => $client->username,
                'title' => $freelance->title,
                'slug' => $freelance->slug,
                'description' => $freelance->description,
                'category' => $freelance->category,
                'rate' => $freelance->rate,
                'status' => $freelance->status,
                'created_at' => $freelance->created_at,
                'updated_at' => $freelance->updated_at,
            ],
            'proposal_details' => [
                'id' => $this->id,
                'freelancer_name' => $freelancer->name,
                'freelancer_username' => $freelancer->username,
                'description' => $this->description,    
                'status' => Str::title($this->status),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]
        ];
    }
}
