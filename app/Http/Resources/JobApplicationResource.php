<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationResource extends JsonResource
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
            'id'                    => $this->id,
            'job_title'             => $this->job_title,
            'application_status'    => $this->application_status,
            'job_start_date'        => $this->job_start_date,
            'job_end_date'          => $this->job_end_date,
            'application_remark'    => $this->application_remark,
            'attachment_url'        => $this->attachment_url,
            'applied_by'            => new UserResource($this->user),
            'job'                   => new JobResource($this->job),
        ];
    }
}
