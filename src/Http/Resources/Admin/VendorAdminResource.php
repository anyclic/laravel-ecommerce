<?php

namespace Vtlabs\Ecommerce\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Core\Http\Resources\Admin\UserAdminResource;
use Vtlabs\Category\Http\Resources\Admin\CategoryAdminResource;

class VendorAdminResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_translations' => $this->getTranslations('name'),
            'tagline' => $this->tagline,
            'tagline_translations' => $this->getTranslations('tagline'),
            'details' => $this->details,
            'details_translations' => $this->getTranslations('details'),
            'meta' => $this->meta,
            'mediaurls' => $this->getMediaUrlsAttribute(),
            'minimum_order' => $this->minimum_order,
            'delivery_fee' => $this->delivery_fee,
            'area' => $this->area,
            'address' => $this->address,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'is_verified' => $this->is_verified,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'categories' => CategoryAdminResource::collection($this->categories),
            'user' => new UserAdminResource($this->user)
        ];
    }
}