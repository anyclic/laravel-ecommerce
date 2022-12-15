<?php

namespace Vtlabs\Ecommerce\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Category\Http\Resources\Admin\CategoryAdminResource;
use Vtlabs\Ecommerce\Http\Resources\Admin\AddonGroupAdminResource;
use Vtlabs\Ecommerce\Http\Resources\Admin\VendorProductAdminResource;

class ProductAdminResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_translations' => $this->getTranslations('title'),
            'detail' => $this->detail,
            'detail_translations' => $this->getTranslations('detail'),
            'meta' => $this->meta,
            'price' => $this->price,
            'owner' => $this->owner,
            'parent_id' => $this->parent_id,
            'attribute_term_id' => $this->attribute_term_id,
            'mediaurls' => $this->getMediaUrlsAttribute(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'addon_groups' => AddonGroupAdminResource::collection($this->addonGroups),
            'categories' => CategoryAdminResource::collection($this->categories),
            'vendor_products' => VendorProductAdminResource::collection($this->vendorProducts),
        ];
    }
}