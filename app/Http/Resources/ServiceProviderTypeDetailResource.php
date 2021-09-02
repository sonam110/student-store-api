<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceProviderTypeDetailResource extends JsonResource
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
            'service_provider_type' => $this->serviceProviderType,
            'id'                    => $this->id,
            'language'              => $this->language,
            'title'                 => $this->title,
            'description'           => $this->description,
            'status'                => $this->status,
        ];
    }
}
