<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FavouriteJobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
        // return [
        //     'id'                    => $this->id,
        //     'sa_id'                 => $this->sa_id,
        //     'sp_id'                 => $this->sp_id,
        //     'job'                   => new JobResource($this->job)
        // ];
    }
}
