<?php

namespace Vtlabs\Ecommerce\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Core\Http\Resources\UserResource;
use Vtlabs\Ecommerce\Models\Product;

class ProductRatingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'rating' => $this->pivot->rating,
            'review' => $this->pivot->review,
            'created_at' => $this->pivot->created_at,
            'user' => new UserResource($this),
            'product' => new ProductResource(Product::find($this->pivot->rateable_id))
        ];
    }
}