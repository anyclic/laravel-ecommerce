<?php

namespace Vtlabs\Ecommerce\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Ecommerce\Http\Resources\Admin\VendorAdminResource;
use Vtlabs\Ecommerce\Http\Resources\Admin\ProductAdminResource;

class VendorProductAdminResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'sale_price_from' => $this->sale_price_from,
            'sale_price_to' => $this->sale_price_to,
            'stock_quantity' => $this->stock_quantity,
            'stock_low_threshold' => $this->stock_low_threshold,
            'product_id' => $this->product_id,
            'vendor_id' => $this->vendor_id,
            'vendor' => new VendorAdminResource($this->vendor)
        ];
    }
}