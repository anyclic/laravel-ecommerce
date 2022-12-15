<?php

namespace Vtlabs\Ecommerce\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    protected $table = 'ecommerce_order_addresses';

    protected $guarded = [];

    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float'
    ];

    public function order()
    {
        return $this->hasOne(config('vtlabs_ecommerce.models.order'));
    }
}
