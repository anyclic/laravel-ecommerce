<?php

namespace Vtlabs\Ecommerce\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Core\Http\Resources\UserResource;
use Vtlabs\Payment\Http\Resources\PaymentResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'notes' => $this->notes,
            'meta' => $this->meta,
            'subtotal' => $this->subtotal,
            'taxes' => $this->taxes,
            'delivery_fee' => $this->delivery_fee,
            'total' => $this->total,
            'discount' => $this->discount,
            'type' => $this->type,
            'scheduled_on' => $this->scheduled_on,
            'status' => $this->status,
            'vendor_id' => $this->vendor_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'products' => OrderProductResource::collection($this->products),
            'vendor' => new VendorResource($this->vendor),
            'user' => new UserResource($this->user),
            'address' => new OrderAddressResource($this->address),
            'delivery' => new OrderDeliveryResource($this->delivery),
            'payment' => new PaymentResource($this->payment),
        ];
    }
}