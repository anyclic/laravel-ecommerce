<?php

namespace Vtlabs\Ecommerce\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddonChoiceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'product_addon_group_id' => $this->product_addon_group_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}