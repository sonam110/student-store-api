<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserPackageSubscriptionResource extends JsonResource
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
            'package'                           => $this->package,
            'package_valid_till'                => $this->package_valid_till,
            'subscription_status'               => $this->subscription_status,
            'payby'                             => $this->payby,
            'remark'                            => $this->remark
        ];
    }
}
