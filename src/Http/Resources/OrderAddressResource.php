<?php

namespace Vtlabs\Ecommerce\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderAddressResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'formatted_address' => $this->formatted_address,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'order_id' => $this->order_id
        ];
    }
}