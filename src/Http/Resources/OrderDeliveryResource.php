<?php

namespace Vtlabs\Ecommerce\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Ecommerce\Http\Resources\DeliveryProfileResource;

class OrderDeliveryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'order_id' => $this->order_id,
            'delivery' => new DeliveryProfileResource($this->delivery)
        ];
    }
}