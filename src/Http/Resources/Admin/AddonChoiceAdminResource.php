<?php

namespace Vtlabs\Ecommerce\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class AddonChoiceAdminResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslations('title'),
            'title_translations' => $this->getTranslations('title'),
            'price' => $this->price,
            'product_addon_group_id' => $this->product_addon_group_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}