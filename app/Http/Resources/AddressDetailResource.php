<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressDetailResource extends JsonResource
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
            'id'            => $this->id,
            "latitude"      => $this->latitude,
            "longitude"     => $this->longitude,
            "country"       => $this->country,
            "state"         => $this->state,
            "city"          => $this->city,
            "zip_code"      => $this->zip_code,
            "full_address"  => $this->full_address,
            "address_type"  => $this->address_type,
            "is_default"    => $this->is_default
        ];
    }
}
