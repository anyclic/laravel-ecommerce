<?php

namespace Vtlabs\Ecommerce\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductSummaryResource extends JsonResource
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
            'updated_at' => $this->updated_at
        ];
    }
}