<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Str;

class FreelanceClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'rate' => $this->rate,
            'status' => ucfirst($this->status),
            'number_of_total_proposals' => $this->number_of_proposal ?: 0,
            'number_of_pending_proposals' => $this->number_of_pending ?: 0,
            'number_of_declined_proposals' => $this->number_of_declined ?: 0,
            'date_posted' => $this->created_at->toISOString(),
            'date_updated' => $this->updated_at
        ];
    }
}
