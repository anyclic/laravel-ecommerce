<?php

namespace Vtlabs\Ecommerce\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Core\Http\Resources\UserResource;

class DeliveryProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'meta' => $this->meta,
            'is_verified' => $this->is_verified,
            'is_online' => $this->is_online,
            'assigned' => $this->assigned,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'user' => new UserResource($this->user)
        ];
    }
}