<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartDetailResource extends JsonResource
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
            'id'                        => $this->id,
            'user_id'                   => $this->user_id,
            "products_services_book_id" => $this->products_services_book_id,
            "sku"                       => $this->sku,
            "price"                     => $this->price,
            "discount"                  => $this->discount,
            "quantity"                  => $this->quantity,
            "item_status"               => $this->item_status,
            "note_to_seller"            => $this->note_to_seller,
        ];
    }
}
