<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserCvDetailResource extends JsonResource
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
        return[
            'id'                => $this->id,
            'user_id'           => $this->user_id,
            'address_detail_id' => $this->address_detail_id,
            'title'             => $this->title,
            'languages_known'   => $this->languages_known,
            'key_skills'        => $this->key_skills,
            'preferred_job_env' => $this->preferred_job_env,
            'other_description' => $this->other_description,
            'is_published'      => $this->is_published,
            'published_at'      => $this->published_at,
            'cv_url'            => $this->cv_url,
            'generated_cv_file' => $this->generated_cv_file,
            'total_experience'  => $this->total_experience,
            'job_tags'          => $this->jobTags
        ];
    }
}
