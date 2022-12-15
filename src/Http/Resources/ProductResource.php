<?php

namespace Vtlabs\Ecommerce\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Core\Models\User\User;
use Vtlabs\Ecommerce\Http\Resources\AddonGroupResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'detail' => $this->detail,
            'meta' => $this->meta,
            'price' => $this->price,
            'owner' => $this->owner,
            'parent_id' => $this->parent_id,
            'attribute_term_id' => $this->attribute_term_id,
            'mediaurls' => $this->getMediaUrlsAttribute(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'addon_groups' => AddonGroupResource::collection($this->addonGroups),
            'categories' => config('vtlabs_category.resources.category')::collection($this->categories),
            'vendor_products' => config('vtlabs_ecommerce.resources.vendor_product')::collection($this->vendorProducts),
            'ratings' => $this->averageRating(User::class),
            'ratings_count' => $this->raters(User::class)->count(),
            'favourite_count' => $this->favoritesCount,
            'is_favourite' => $this->isFavorited()
        ];
    }
}