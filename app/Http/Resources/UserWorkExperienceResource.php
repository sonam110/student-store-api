<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserWorkExperienceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id'                                => $this->id,
            'title'                             => $this->title,
            'employer_name'                     => $this->employer_name,
            'activities_and_responsibilities'   => $this->activities_and_responsibilities,
            'currently_working'                 => $this->currently_working,
            'from_date'                         => $this->from_date,
            'to_date'                           => $this->to_date,
            'is_from_sweden'                    => $this->is_from_sweden,
            'country'                           => $this->country,
            'state'                             => $this->state,
            'city'                              => $this->city
        ];
    }
}
