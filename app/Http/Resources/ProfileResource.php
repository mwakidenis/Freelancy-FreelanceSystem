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

        $educational_attainment = $profile 
                                ? json_decode($profile->educational_attainment,true)
                                : null;
        if ($educational_attainment){
            $educational_attainment = collect($educational_attainment)->sortBy('year_graduated')->values();
        }

        $work_experience = $profile 
                        ? json_decode($profile->work_experience,true)
                        : null;
        if ($work_experience){
            $work_experience = collect($work_experience)->sortBy('year_start')->values();
        } 
 
        return array_merge([
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'user_type' => $this->role,
            'date_registered' => $this->created_at,
            'email_verified' => $this->email_verified_at,
            'brief_description' => $profile ? $profile->description : null,
            'educational_attainment' => $educational_attainment,
            'work_experience' => $work_experience,
            'skills' => $profile ? $profile->skills : null
        ], $additional);
    }
}
