<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationTypeDetailResource extends JsonResource
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
            'registration_type'     => $this->registrationType,
            'id'                    => $this->id,
            'language'              => $this->language,
            'title'                 => $this->title,
            'description'           => $this->description,
            'status'                => $this->status,
        ];
    }
}
