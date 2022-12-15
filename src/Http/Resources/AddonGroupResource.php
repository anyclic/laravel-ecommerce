<?php

namespace Vtlabs\Ecommerce\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Ecommerce\Http\Resources\AddonChoiceResource;

class AddonGroupResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'max_choices' => $this->max_choices,
            'min_choices' => $this->min_choices,
            'product_id' => $this->product_id,
            'addon_choices' => AddonChoiceResource::collection($this->addonChoices),
        ];
    }
}