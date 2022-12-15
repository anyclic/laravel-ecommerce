<?php

namespace Vtlabs\Ecommerce\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'meta' => $this->meta,
            'formatted_address' => $this->formatted_address,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'postcode' => $this->postcode,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'user_id' => $this->user_id
        ];
    }
}