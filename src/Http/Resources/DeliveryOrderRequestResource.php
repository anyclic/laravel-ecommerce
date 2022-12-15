<?php

namespace Vtlabs\Ecommerce\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Ecommerce\Http\Resources\OrderResource;
use Vtlabs\Ecommerce\Http\Resources\DeliveryProfileResource;

class DeliveryOrderRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order' => new OrderResource($this->order),
            'delivery' => new DeliveryProfileResource($this->delivery),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}