<?php

namespace Vtlabs\Ecommerce\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Ecommerce\Models\VendorProduct;

class OrderAddonChoiceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'addon_choice' => new AddonChoiceResource($this->addonChoice)
        ];
    }
}