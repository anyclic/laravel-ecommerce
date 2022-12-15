<?php

namespace Vtlabs\Ecommerce\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Ecommerce\Models\VendorProduct;

class OrderProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'total' => $this->total,
            'subtotal' => $this->subtotal,
            'order_id' => $this->order_id,
            'vendor_product_id' => $this->product_id,
            'vendor_product' => new VendorProductWithProductResource($this->vendorProduct),
            'addon_choices' => OrderAddonChoiceResource::collection($this->addonChoices)
        ];
    }
}