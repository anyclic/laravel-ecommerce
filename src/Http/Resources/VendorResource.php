<?php

namespace Vtlabs\Ecommerce\Http\Resources;

use Vtlabs\Core\Models\User\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Category\Http\Resources\CategoryResource;

class VendorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tagline' => $this->tagline,
            'details' => $this->details,
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
            'categories' => CategoryResource::collection($this->categories),
            'product_categories' => CategoryResource::collection($this->productCategories),
            'user' => $this->user,
            'ratings' => $this->averageRating(User::class),
            'ratings_count' => $this->raters(User::class)->count(),
            'favourite_count' => $this->favoritesCount,
            'is_favourite' => $this->isFavorited(),
            'distance' => $this->distance
        ];
    }
}