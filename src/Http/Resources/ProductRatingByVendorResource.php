<?php

namespace Vtlabs\Ecommerce\Http\Resources;

use Vtlabs\Core\Models\User\User;
use Vtlabs\Ecommerce\Models\Product;
use Vtlabs\Core\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductRatingByVendorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'review' => $this->review,
            'created_at' => $this->created_at,
            'user' => new UserResource(User::find($this->rater_id)),
            'product' => new ProductResource(Product::find($this->rateable_id))
        ];
    }
}