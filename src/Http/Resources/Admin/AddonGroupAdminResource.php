<?php

namespace Vtlabs\Ecommerce\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Ecommerce\Http\Resources\Admin\AddonChoiceAdminResource;

class AddonGroupAdminResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslations('title'),
            'title_translations' => $this->getTranslations('title'),
            'max_choices' => $this->max_choices,
            'min_choices' => $this->min_choices,
            'product_id' => $this->product_id,
            'addon_choices' => AddonChoiceAdminResource::collection($this->addonChoices),
        ];
    }
}